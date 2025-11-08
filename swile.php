<?php
require_once 'env.php';

// add logger
$logFile = __DIR__ . '/swile.log';
function log_msg($msg) {
    global $logFile;
    $time = (new DateTime())->format('Y-m-d H:i:s');
    // ensure message is a string
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    file_put_contents($logFile, "[$time] " . trim($msg) . PHP_EOL, FILE_APPEND);
}

// function to authenticate and get a token
function authenticate($baseURL, $user, $pass) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $baseURL . "/api/v1/auth/login",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_USERPWD => $user . ":" . $pass,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        log_msg("cURL Error #: " . $err);
        return null;
    }

    // try to decode JSON and extract token
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        log_msg("JSON decode error: " . json_last_error_msg() . " -- Raw response: " . $response);
        return null;
    }

    // common token locations
    if (is_array($data)) {
        if (isset($data['token'])) {
            // log token acquisition (optional)
            log_msg("Token found in response.");
            return $data['token'];
        }
        if (isset($data['access_token'])) {
            log_msg("Access token found in response.");
            return $data['access_token'];
        }
        if (isset($data['data']) && is_array($data['data'])) {
            if (isset($data['data']['token'])) {
                log_msg("Token found in data.token.");
                return $data['data']['token'];
            }
            if (isset($data['data']['access_token'])) {
                log_msg("Access token found in data.access_token.");
                return $data['data']['access_token'];
            }
        }
    }

    // token not found
    log_msg("Token not found in response. Response: " . $response);
    return null;
}

// store token in $token variable
$token = authenticate($baseURL, $user, $pass);

if ($token) {
    // log token if needed (be cautious with secrets)
    log_msg("Token obtained.");
} else {
    log_msg("No token obtained.");
    exit(1);
}

// set data 
$dtFriday = new DateTime('next Friday');
$dtFriday->setTime(19, 0, 0);
$nextFriday = $dtFriday->format('Y-m-d\TH:i:s');

$dtThursday = new DateTime('next Thursday');
$dtThursday->setTime(19, 0, 0);
$nextThursday = $dtThursday->format('Y-m-d\TH:i:s');

//OrderBody
$order = 
    '{
        "data": [
            {
            "document": "'. $document . '",
            "cardValues": [
                {
                "card": "v13",
                "value": "75"
                }
            ]
            }
        ],
        "externalId": "",
        "paymentMethod": "PIX",
        "benefitPayerDocument": "'. $bdocument . '",
        "campaignPayerDocument": "'. $bdocument . '",
        "creditDate": "'. $nextFriday . '",
        "dueDate": "' . $nextThursday . '",
        "benefitPaymentInfo": [
            {
            "document": "'. $bdocument . '",
            "invoiceText": "",
            "swileCreditToUse": 0
            }
        ],
        "campaignPaymentInfo": [
        {
            "document": "'. $bdocument . '",
            "invoiceText": "",
            "swileCreditToUse": ""
            }
        ]
    }';

// log minimal order info (avoid dumping sensitive data)
//log_msg("Prepared order for document: " . ($document ?? 'unknown') . " creditDate: " . $nextFriday);

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => $baseURL . "/api/v1/order/create/express",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "POST",
    // send JSON body
    CURLOPT_POSTFIELDS => $order,
    // use bearer token instead of basic auth
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$token}",
        "Content-Type: application/json",
        "Accept: application/json"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$http = curl_getinfo($curl, CURLINFO_HTTP_CODE);

curl_close($curl);

if ($err) {
    log_msg("cURL Error #: " . $err);
    exit(1);
}

// log response (store full response in log; keep stdout silent)
log_msg("HTTP $http Response: " . $response);

// exit successfully without printing to screen
exit(0);
?>
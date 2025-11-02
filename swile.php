<?php
require_once 'env.php';

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
        echo "cURL Error #: " . $err . "\n";
        return null;
    }

    // try to decode JSON and extract token
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "JSON decode error: " . json_last_error_msg() . "\n";
        // optionally print raw response for debugging
        // echo "Raw response: $response\n";
        return null;
    }

    // common token locations
    if (is_array($data)) {
        if (isset($data['token'])) {
            return $data['token'];
        }
        if (isset($data['access_token'])) {
            return $data['access_token'];
        }
        if (isset($data['data']) && is_array($data['data'])) {
            if (isset($data['data']['token'])) return $data['data']['token'];
            if (isset($data['data']['access_token'])) return $data['data']['access_token'];
        }
    }

    // token not found
    echo "Token not found in response.\n";
    return null;
}

// store token in $token variable
$token = authenticate($baseURL, $user, $pass);

if ($token) {
    echo "Token: " . $token . "\n";
} else {
    echo "No token obtained.\n";
}

?>
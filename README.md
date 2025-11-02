# Swile Credit Scheduler

Simple PHP script to authenticate against the Swile API and obtain a token. Intended to be used as part of a routine that credits users every Friday.

## Purpose
- Authenticate with Swile API and return a JWT token.
- Can be scheduled (cron) to run weekly and drive subsequent crediting steps.

## Prerequisites
- PHP 7.2+ with cURL extension enabled.

## Quick checks
- Check cURL enabled:
  - `php -m | grep -i curl`
  - or `php -r "echo extension_loaded('curl') ? 'enabled\n' : 'disabled\n';"`

## Run manually
From the project directory:
- `php swile.php`

## Scheduling (every Friday)
Add a crontab entry to run the script every Friday at 09:00 and log output:
```
0 9 * * 5 /usr/bin/php /home/chateau/github/swileApi/swile.php >> /var/log/swile_credit.log 2>&1
```
Adjust PHP path, time and log path as needed.

## Next steps
- Loading...
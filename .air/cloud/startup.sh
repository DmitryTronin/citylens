#!/usr/bin/env bash
set -u

app_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$app_root"

log_step() {
    printf '[citylens startup] %s\n' "$1"
}

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
    WARMUP=1
else
    WARMUP=
fi

if ! command -v php >/dev/null 2>&1 || ! command -v composer >/dev/null 2>&1; then
    log_step "Installing PHP 8.3 CLI, curl extension and Composer"
    sudo apt-get update -y
    sudo DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
        php8.3-cli php8.3-curl composer
fi

log_step "Installing PHP dependencies via Composer"
composer install --no-interaction --prefer-dist

if [ -z "${OPENWEATHER_API_KEY:-}" ]; then
    printf '[citylens startup] OPENWEATHER_API_KEY is not configured\n' >&2
    exit 1
fi

log_step "Writing runtime OpenWeatherMap configuration"
printf '%s\n' '<?php' "define('openweathermap_api_key', getenv('OPENWEATHER_API_KEY'));" > config.php

if ! pgrep -f "php -S 0.0.0.0:8000" >/dev/null 2>&1; then
    log_step "Starting PHP development server on port 8000"
    nohup php -S 0.0.0.0:8000 -t "$app_root" >/tmp/citylens-php.log 2>&1 &
    disown
else
    log_step "PHP development server already running"
fi

healthcheck() {
    local response status
    while true; do
        response="$(curl --silent --show-error --max-time 5 --write-out $'\n%{http_code}' http://127.0.0.1:8000/ 2>/tmp/citylens-healthcheck.log || true)"
        status="${response##*$'\n'}"
        if [ "$status" = 200 ] && [[ "$response" == *"CityLens Weather"* ]] && [[ "$response" != *"Error:"* ]]; then
            log_step "Healthcheck passed: app served live weather data"
            return 0
        fi
        printf '[citylens startup] Waiting for CityLens at port 8000 (HTTP %s)\n' "$status"
        sleep 2
    done
}

if [ -n "$WARMUP" ]; then
    healthcheck
fi

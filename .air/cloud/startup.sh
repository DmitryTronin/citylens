#!/usr/bin/env bash
set -u

app_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$app_root"

log_step() {
    printf '[citylens startup] %s\n' "$1"
}

log_step "Installing PHP dependencies"
composer install --no-interaction --prefer-dist

if [ -z "${OPENWEATHER_API_KEY:-}" ]; then
    printf '[citylens startup] OPENWEATHER_API_KEY is not configured\n' >&2
    exit 1
fi

log_step "Writing runtime OpenWeatherMap configuration"
printf '%s\n' '<?php' "define('openweathermap_api_key', getenv('OPENWEATHER_API_KEY'));" > config.php

log_step "Starting PHP development server"
nohup php -S 0.0.0.0:8000 -t "$app_root" >/tmp/citylens-php.log 2>&1 &

healthcheck() {
    local response
    local status=0
    while true; do
        response="$(curl --silent --show-error --write-out $'\n%{http_code}' http://127.0.0.1:8000/ 2>/tmp/citylens-healthcheck.log || true)"
        status="${response##*$'\n'}"
        if [ "$status" = 200 ] && [[ "$response" == *"CityLens Weather"* ]] && [[ "$response" != *"Error:"* ]]; then
            log_step "Healthcheck passed"
            return 0
        fi
        printf '[citylens startup] Waiting for CityLens at port 8000 (HTTP %s)\n' "$status"
        sleep 2
    done
}

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
    healthcheck
fi

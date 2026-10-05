#!/usr/bin/env bash
set -eu

ROOT_DIR=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
cd "$ROOT_DIR"

log() {
    printf '[citylens-startup] %s\n' "$1"
}

healthcheck() {
    local checks=0
    local page

    log "Waiting for CityLens to render live weather on port 8000."
    while :; do
        if page=$(curl --silent --show-error --fail \
            --header 'Host: localhost' \
            http://127.0.0.1:8000/ 2>/dev/null); then
            if printf '%s' "$page" | grep -q '<h1>CityLens Weather</h1>' \
                && printf '%s' "$page" | grep -q "class='weather-label'>Location" \
                && printf '%s' "$page" | grep -q "class='weather-label'>Temperature"; then
                log "CityLens rendered weather data successfully."
                return 0
            fi
        fi

        checks=$((checks + 1))
        if [ "$((checks % 3))" -eq 0 ]; then
            log "CityLens is still waiting for a successful weather response."
        fi
        sleep 5
    done
}

if [ -z "${OPENWEATHERMAP_API_KEY:-}" ]; then
    log "OPENWEATHERMAP_API_KEY is missing; add your personal OpenWeatherMap API key to the environment configuration."
    exit 1
fi

if ! command -v docker >/dev/null 2>&1; then
    log "Docker is required to run the PHP 8.2 Apache environment image."
    exit 1
fi

log "Preparing PHP 8.2 Apache image."
if docker image inspect php:8.2-apache >/dev/null 2>&1; then
    log "Using cached php:8.2-apache image."
else
    docker pull php:8.2-apache
fi

log "Writing the ignored runtime config.php file."
umask 077
key_base64=$(printf '%s' "$OPENWEATHERMAP_API_KEY" | base64 | tr -d '\n')
config_tmp=$(mktemp "$ROOT_DIR/config.php.XXXXXX")
trap 'rm -f "$config_tmp"' EXIT
printf '<?php\ndefine("openweathermap_api_key", base64_decode("%s"));\n' "$key_base64" > "$config_tmp"
chmod 644 "$config_tmp"
mv "$config_tmp" "$ROOT_DIR/config.php"
trap - EXIT

log "Starting CityLens on port 8000."
docker rm --force citylens-web >/dev/null 2>&1 || true
docker run --detach \
    --name citylens-web \
    --restart unless-stopped \
    --publish 8000:80 \
    --env HTTP_PROXY \
    --env HTTPS_PROXY \
    --env http_proxy \
    --env https_proxy \
    --volume "$ROOT_DIR:/var/www/html:ro" \
    php:8.2-apache

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
    healthcheck
fi

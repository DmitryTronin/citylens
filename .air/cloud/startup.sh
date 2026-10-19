#!/usr/bin/env bash
set -euo pipefail

if [ "${AIR_STARTUP_MODE:-}" = "warmup" ]; then
    WARMUP=1
else
    WARMUP=
fi

PHP_RUNTIME="$HOME/.local/bin/frankenphp"
PHP_VERSION="1.13.1"
PHP_DOWNLOAD_URL="https://github.com/php/frankenphp/releases/download/v${PHP_VERSION}/frankenphp-linux-x86_64-gnu"

mkdir -p "$HOME/.local/bin"
if [ ! -x "$PHP_RUNTIME" ]; then
    echo "Downloading FrankenPHP ${PHP_VERSION} (PHP runtime and web server)..."
    curl -fsSL -x "$HTTPS_PROXY" "$PHP_DOWNLOAD_URL" -o "$PHP_RUNTIME"
    chmod +x "$PHP_RUNTIME"
fi

# config.php is intentionally ignored by git. Read the key from the environment
# so the personal secret never gets written into the workspace or startup logs.
cat > config.php <<'PHP'
<?php
define('openweathermap_api_key', getenv('OPENWEATHERMAP_API_KEY') ?: '');
PHP

echo "Starting CityLens on 0.0.0.0:8000..."
nohup "$PHP_RUNTIME" php-server --root "$PWD" --listen 0.0.0.0:8000 \
    >/tmp/citylens-php-server.log 2>&1 </dev/null &

healthcheck() {
    local elapsed=0
    local page

    echo "Waiting for CityLens to serve live weather data..."
    while true; do
        page="$(curl -fsS -H 'Host: localhost' http://127.0.0.1:8000/ 2>/dev/null || true)"
        if [[ "$page" == *"CityLens Weather"* && "$page" == *"Location"* && "$page" == *"Temperature"* ]]; then
            echo "CityLens is serving its weather page successfully."
            return 0
        fi

        if (( elapsed % 10 == 0 )); then
            echo "CityLens is not ready yet; waiting for its PHP server and OpenWeatherMap response..."
            if [ -f /tmp/citylens-php-server.log ]; then
                tail -n 8 /tmp/citylens-php-server.log
            fi
        fi
        sleep 2
        elapsed=$((elapsed + 2))
    done
}

if [ -n "$WARMUP" ]; then
    healthcheck
fi

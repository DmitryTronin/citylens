#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/../.."
repo_root="$PWD"
state_dir="$HOME/.cache/citylens"
mkdir -p "$state_dir"

if ! command -v php >/dev/null || ! command -v composer >/dev/null ||
   ! command -v curl >/dev/null ||
   ! php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("curl") ? 0 : 1);'; then
    echo "Installing PHP CLI, cURL, and Composer..."
    sudo -n apt-get -o "Acquire::http::Proxy=${HTTP_PROXY:?}" \
        -o "Acquire::https::Proxy=${HTTPS_PROXY:?}" update
    sudo -n env DEBIAN_FRONTEND=noninteractive apt-get \
        -o "Acquire::http::Proxy=$HTTP_PROXY" \
        -o "Acquire::https::Proxy=$HTTPS_PROXY" \
        install -y php-cli php-curl composer curl
fi
php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("curl") ? 0 : 1);'

if [[ -z "${OPENWEATHERMAP_API_KEY:-}" ]]; then
    echo "OPENWEATHERMAP_API_KEY is required; fill the personal secret in Air." >&2
    exit 1
fi

# The ignored config reads the secret at runtime; no secret is written to disk.
if [[ ! -e config.php ]]; then
    cat > config.php <<'PHP'
<?php
define('openweathermap_api_key', getenv('OPENWEATHERMAP_API_KEY'));
PHP
fi

echo "Installing locked Composer dependencies..."
composer install --no-interaction --prefer-dist
composer check-platform-reqs

healthcheck() {
    echo "Waiting for CityLens on port 8001..."
    while true; do
        if ! kill -0 "$server_pid" 2>/dev/null; then
            echo "PHP server stopped; inspect $state_dir/server.log." >&2
            return 1
        fi
        if curl --proxy '' --fail --silent --show-error \
            -H 'Host: citylens-preview.example' \
            http://127.0.0.1:8001/ -o "$state_dir/health.html"; then
            if ! grep -q 'CityLens Weather' "$state_dir/health.html" ||
               ! grep -q "weather-label'>Temperature" "$state_dir/health.html" ||
               ! grep -q "weather-label'>Humidity" "$state_dir/health.html"; then
                echo "Waiting for live weather. Check the OpenWeatherMap key and API access."
                sleep 5
                continue
            fi
            curl --proxy '' --fail --silent --show-error \
                http://127.0.0.1:8001/skycons/skycons.js -o "$state_dir/skycons.js"
            grep -q Skycons "$state_dir/skycons.js"
            echo "CityLens is ready: live weather and Skycons served successfully."
            return 0
        fi
        echo "PHP server is not ready yet; waiting..."
        sleep 2
    done
}

# Restart our own server when the script is rerun; snapshots retain no processes.
if [[ -f "$state_dir/server.pid" ]]; then
    old_pid="$(cat "$state_dir/server.pid")"
    if [[ "$old_pid" =~ ^[0-9]+$ ]] &&
       [[ -r "/proc/$old_pid/cmdline" ]] &&
       tr '\0' ' ' < "/proc/$old_pid/cmdline" | grep -Fq "php -S 0.0.0.0:8001 -t $repo_root"; then
        kill "$old_pid"
    fi
fi
echo "Starting CityLens at 0.0.0.0:8001..."
nohup php -S 0.0.0.0:8001 -t "$repo_root" \
    >"$state_dir/server.log" 2>&1 </dev/null &
server_pid=$!
echo "$server_pid" > "$state_dir/server.pid"

if [[ "${AIR_STARTUP_MODE:-}" == warmup ]]; then
    healthcheck
fi

#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

say() {
    printf '[citylens-setup] %s\n' "$*"
}

install_dependencies() {
    if command -v php >/dev/null 2>&1 && php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("curl") ? 0 : 1);'; then
        say "PHP $(php -r 'echo PHP_VERSION;') with cURL is already available."
    else
        say "Installing PHP 8.2+ CLI and cURL."
        if [ "$(id -u)" -eq 0 ]; then
            apt-get update
            DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-curl
        else
            sudo apt-get update
            sudo env DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-curl
        fi
    fi

    if ! command -v composer >/dev/null 2>&1; then
        say "Installing Composer."
        if [ "$(id -u)" -eq 0 ]; then
            DEBIAN_FRONTEND=noninteractive apt-get install -y composer
        else
            sudo env DEBIAN_FRONTEND=noninteractive apt-get install -y composer
        fi
    fi

    say "Priming Composer dependencies."
    cd "$repo_root"
    composer install --no-interaction --prefer-dist
}

write_runtime_config() {
    say "Preparing ignored runtime configuration from OPENWEATHERMAP_API_KEY."
    cat > "$repo_root/config.php" <<'PHP'
<?php
if (!defined('openweathermap_api_key')) {
    define('openweathermap_api_key', (string) getenv('OPENWEATHERMAP_API_KEY'));
}
PHP
}

start_app() {
    if curl --noproxy '*' -fsS -H 'Host: citylens.local' http://127.0.0.1:8000/ -o /dev/null 2>/dev/null; then
        say "CityLens is already answering on port 8000."
        return
    fi

    say "Starting CityLens on 0.0.0.0:8000."
    nohup php -S 0.0.0.0:8000 -t "$repo_root" > /tmp/citylens-php-server.log 2>&1 < /dev/null &
}

healthcheck() {
    local attempt=0
    local page=/tmp/citylens-healthcheck.html
    say "Waiting for the CityLens page and its live weather request to succeed."
    while true; do
        attempt=$((attempt + 1))
        if curl --noproxy '*' -fsS -H 'Host: citylens.local' http://127.0.0.1:8000/ -o "$page" 2>/dev/null \
            && grep -q 'CityLens Weather' "$page" \
            && ! grep -Eq 'Error:</b>|No weather data available|Warning:|Fatal error:' "$page"; then
            say "Healthcheck passed: CityLens rendered its weather page successfully."
            return 0
        fi

        if [ $((attempt % 10)) -eq 0 ]; then
            say "Still waiting for CityLens readiness (attempt $attempt); recent server log:"
            tail -n 8 /tmp/citylens-php-server.log 2>/dev/null || true
        fi
        sleep 3
    done
}

install_dependencies
write_runtime_config
start_app

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
    healthcheck
fi

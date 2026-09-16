#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$0")/../.."

log() { printf '[startup] %s\n' "$*"; }

if ! command -v php >/dev/null 2>&1; then
  if command -v apt-get >/dev/null 2>&1 && [ "$(id -u)" -eq 0 ]; then
    log "Installing PHP runtime and cURL extension"
    apt-get update
    DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-curl
  else
    printf '[startup] PHP 8.2+ is required but php is unavailable and cannot be installed\n' >&2
    exit 1
  fi
fi

php_major="$(php -r 'echo PHP_MAJOR_VERSION;')"
php_minor="$(php -r 'echo PHP_MINOR_VERSION;')"
if [ "$php_major" -lt 8 ] || { [ "$php_major" -eq 8 ] && [ "$php_minor" -lt 2 ]; }; then
  printf '[startup] PHP 8.2+ is required; found %s\n' "$(php -r 'echo PHP_VERSION;')" >&2
  exit 1
fi

if ! php -m | grep -qx 'curl'; then
  printf '[startup] PHP cURL extension is required\n' >&2
  exit 1
fi

if [ -n "${OPENWEATHERMAP_API_KEY:-}" ]; then
  umask 077
  printf '<?php\ndefine("openweathermap_api_key", %s);\n' \
    "$(php -r 'echo var_export(getenv("OPENWEATHERMAP_API_KEY"), true);')" > config.php
else
  printf '[startup] OPENWEATHERMAP_API_KEY is not set\n' >&2
  exit 1
fi

if command -v composer >/dev/null 2>&1 && [ -f composer.json ]; then
  log "Priming Composer dependencies"
  composer install --no-interaction --prefer-dist --no-progress
fi

log "Starting PHP web server on port 8001"
nohup php -S 0.0.0.0:8001 >/tmp/citylens-php.log 2>&1 &

healthcheck() {
  log "Waiting for CityLens to serve the API-backed page"
  while :; do
    if response="$(curl -fsS http://127.0.0.1:8001/ 2>/dev/null)" \
      && printf '%s' "$response" | grep -q 'CityLens Weather' \
      && printf '%s' "$response" | grep -q 'weather-label'; then
      log "CityLens healthcheck passed"
      return 0
    fi
    log "CityLens is not ready yet; retrying"
    sleep 2
  done
}

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
  healthcheck
fi

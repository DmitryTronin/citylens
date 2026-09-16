#!/usr/bin/env bash
set -Eeuo pipefail

cd "$(dirname "$0")/../.."

log() { printf '[startup] %s\n' "$*"; }

if [ -n "${OPENWEATHERMAP_API_KEY:-}" ]; then
  umask 077
  if command -v php >/dev/null 2>&1; then
    printf '<?php\ndefine("openweathermap_api_key", %s);\n' \
      "$(php -r 'echo var_export(getenv("OPENWEATHERMAP_API_KEY"), true);')" > config.php
  else
    log "PHP is unavailable locally; pulling the cached PHP runtime image"
    docker pull php:8.2-cli
    docker run --rm -e OPENWEATHERMAP_API_KEY -v "$PWD:/app" -w /app php:8.2-cli \
      php -r 'file_put_contents("config.php", "<?php\\ndefine(\\"openweathermap_api_key\\", " . var_export(getenv("OPENWEATHERMAP_API_KEY"), true) . ");\\n");'
  fi
else
  printf '[startup] OPENWEATHERMAP_API_KEY is not set\n' >&2
  exit 1
fi

if command -v composer >/dev/null 2>&1 && [ -f composer.json ]; then
  log "Priming Composer dependencies"
  composer install --no-interaction --prefer-dist --no-progress
fi

log "Starting PHP web server on port 8001"
if command -v php >/dev/null 2>&1; then
  php -m | grep -qx 'curl' || { printf '[startup] PHP cURL extension is required\n' >&2; exit 1; }
  nohup php -S 0.0.0.0:8001 >/tmp/citylens-php.log 2>&1 &
else
  docker rm -f citylens-php >/dev/null 2>&1 || true
  nohup docker run --rm --name citylens-php -p 8001:8001 -v "$PWD:/app" -w /app php:8.2-cli \
    php -S 0.0.0.0:8001 >/tmp/citylens-php.log 2>&1 &
fi

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

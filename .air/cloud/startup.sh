#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PORT="${PORT:-8000}"
WARMUP=""
if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
  WARMUP=1
fi

log() {
  printf '[citylens] %s\n' "$*"
}

log "checking PHP runtime"
command -v php >/dev/null
php -r 'if (PHP_VERSION_ID < 80200) { fwrite(STDERR, "PHP 8.2 or newer is required\n"); exit(1); } if (!extension_loaded("curl")) { fwrite(STDERR, "PHP cURL extension is required\n"); exit(1); }'

if command -v composer >/dev/null 2>&1 && [ -f "$APP_DIR/composer.json" ]; then
  log "installing PHP dependencies"
  composer install --no-interaction --prefer-dist --no-progress --working-dir="$APP_DIR"
fi

if [ -z "${OPENWEATHERMAP_API_KEY:-}" ]; then
  log "OPENWEATHERMAP_API_KEY is not set"
  exit 1
fi

log "writing local application configuration"
umask 077
printf '%s\n' '<?php' "define('openweathermap_api_key', '$(printf '%s' "$OPENWEATHERMAP_API_KEY" | sed "s/'/'\\''/g")');" > "$APP_DIR/config.php"

log "starting PHP web server on port $PORT"
cd "$APP_DIR"
nohup php -S "0.0.0.0:$PORT" -t "$APP_DIR" >/tmp/citylens-php.log 2>&1 &

healthcheck() {
  log "waiting for CityLens HTTP response"
  while true; do
    response="$(curl -fsS "http://127.0.0.1:$PORT/index.php" 2>/dev/null || true)"
    if printf '%s' "$response" | grep -q 'CityLens\|weather\|Weather'; then
      log "CityLens HTTP healthcheck passed"
      return 0
    fi
    log "CityLens is not ready yet; waiting"
    sleep 2
  done
}

if [ -n "$WARMUP" ]; then
  healthcheck
fi

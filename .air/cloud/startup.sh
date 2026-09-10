#!/usr/bin/env bash
set -u

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PORT="${PORT:-8000}"
WARMUP=""
if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
  WARMUP=1
fi

log() {
  printf '[citylens] %s\n' "$*"
}

fail() {
  printf '[citylens] ERROR: %s\n' "$*" >&2
  exit 1
}

configure_shell() {
  local env_file="$HOME/.citylens-env"
  local profile="$HOME/.profile"
  local marker='# citylens startup environment'

  if [ -f "$HOME/.bash_profile" ]; then
    profile="$HOME/.bash_profile"
  elif [ -f "$HOME/.bash_login" ]; then
    profile="$HOME/.bash_login"
  fi

  {
    printf 'export CITYLENS_ROOT=%q\n' "$APP_DIR"
    printf 'export PORT=%q\n' "$PORT"
  } > "$env_file"

  touch "$profile" "$HOME/.bashrc"
  for shell_file in "$profile" "$HOME/.bashrc"; do
    if ! grep -Fq "$marker" "$shell_file"; then
      {
        printf '\n%s\n' "$marker"
        printf '[ -f %q ] && . %q\n' "$env_file" "$env_file"
      } >> "$shell_file"
    fi
  done
}

prepare_dependencies() {
  command -v docker >/dev/null 2>&1 || fail 'Docker is required but was not found'
  log 'Pulling PHP 8.2 Apache image'
  docker pull php:8.2-apache

  if [ -z "${OPENWEATHERMAP_API_KEY:-}" ]; then
    fail 'OPENWEATHERMAP_API_KEY is not configured'
  fi
  cat > "$APP_DIR/config.php" <<PHP_CONFIG
<?php
define('openweathermap_api_key', getenv('OPENWEATHERMAP_API_KEY'));
PHP_CONFIG
}

start_server() {
  log "Starting PHP server on 0.0.0.0:${PORT}"
  docker rm -f citylens-php >/dev/null 2>&1 || true
  docker run -d --name citylens-php -p "${PORT}:80" -v "$APP_DIR:/var/www/html" php:8.2-apache >/tmp/citylens-php.log
}

healthcheck() {
  log 'Waiting for CityLens to serve the application'
  while ! curl -fsS "http://127.0.0.1:${PORT}/" | grep -Fq 'CityLens Weather'; do
    sleep 2
    log 'CityLens is not ready yet'
  done
  log 'CityLens healthcheck passed'
}

log 'Preparing CityLens environment'
configure_shell
prepare_dependencies
start_server
if [ -n "${WARMUP:-}" ]; then
  healthcheck
fi
log 'Startup complete'

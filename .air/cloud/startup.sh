#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PHP_IMAGE="php:8.2-cli"
PORT="${PORT:-8000}"

log() { printf '[citylens] %s\n' "$*"; }

write_shell_environment() {
  local env_file="$HOME/.citylens-env"
  local profile
  cat > "$env_file" <<'EOF'
# CityLens development environment
export CITYLENS_ROOT="__CITYLENS_ROOT__"
export CITYLENS_PORT="__CITYLENS_PORT__"
EOF
  sed -i "s#__CITYLENS_ROOT__#${ROOT_DIR}#; s#__CITYLENS_PORT__#${PORT}#" "$env_file"
  chmod 600 "$env_file"

  profile=""
  for candidate in "$HOME/.bash_profile" "$HOME/.bash_login" "$HOME/.profile"; do
    if [ -f "$candidate" ]; then profile="$candidate"; break; fi
  done
  if [ -z "$profile" ]; then profile="$HOME/.profile"; touch "$profile"; fi
  local marker='# citylens startup environment'
  if ! grep -Fq "$marker" "$profile" 2>/dev/null; then
    printf '\n%s\n[ -f "$HOME/.citylens-env" ] && . "$HOME/.citylens-env"\n' "$marker" >> "$profile"
  fi
  if ! grep -Fq "$marker" "$HOME/.bashrc" 2>/dev/null; then
    printf '\n%s\n[ -f "$HOME/.citylens-env" ] && . "$HOME/.citylens-env"\n' "$marker" >> "$HOME/.bashrc"
  fi
}

prepare_config() {
  if [ -z "${OPENWEATHERMAP_API_KEY:-}" ]; then
    log 'OPENWEATHERMAP_API_KEY is not available; cannot create config.php'
    return 1
  fi
  umask 077
  printf '<?php\ndefine("openweathermap_api_key", %s);\n' \
    "$(printf '%s' "$OPENWEATHERMAP_API_KEY" | sed 's/\\/\\\\/g; s/'"'"'/\\'"'"'/g; s/^/'"'"'/; s/$/'"'"'/')" \
    > "$ROOT_DIR/config.php"
}

healthcheck() {
  log 'waiting for CityLens HTTP response on port 8000'
  while ! response="$(curl -fsS "http://127.0.0.1:${PORT}/" 2>/dev/null)"; do
    log 'CityLens is not ready yet'
    sleep 2
  done
  printf '%s' "$response" | grep -Fq 'CityLens Weather' || {
    log 'CityLens responded without the expected page title'
    return 1
  }
  printf '%s' "$response" | grep -Fq 'weather-card' || {
    log 'CityLens responded without the weather card'
    return 1
  }
  log 'CityLens healthcheck passed'
}

write_shell_environment
log 'pulling PHP runtime image'
docker pull "$PHP_IMAGE"
prepare_config
log 'starting CityLens on 0.0.0.0:${PORT}'
docker run --rm --name citylens-web --network host \
  -v "$ROOT_DIR:/app" -w /app "$PHP_IMAGE" \
  php -S "0.0.0.0:${PORT}" -t /app > /tmp/citylens-web.log 2>&1 &

if [ "${AIR_STARTUP_MODE:-}" = warmup ]; then
  healthcheck
fi

#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."

if ! command -v php >/dev/null 2>&1 || ! php -m | grep -qi '^curl$'; then
  sudo apt-get update -qq
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y -qq php-cli php-curl
fi

healthcheck() {
  local f
  for f in index.php src/*.php; do php -l "$f" >/dev/null; done
  php -m | grep -qi '^curl$'
  php -S 127.0.0.1:8099 >/tmp/php-health.log 2>&1 &
  local pid=$!
  until curl -fsS -o /dev/null http://127.0.0.1:8099/src/styles.css; do
    kill -0 "$pid" 2>/dev/null || { echo "php server died" >&2; return 1; }
    sleep 1
  done
  kill "$pid" 2>/dev/null || true
}

healthcheck

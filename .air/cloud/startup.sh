#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."

SUDO=""; [ "$(id -u)" -ne 0 ] && SUDO="sudo"

if ! command -v php >/dev/null 2>&1 || ! php -m | grep -qi '^curl$'; then
  $SUDO apt-get update -y
  $SUDO DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-curl
fi

if ! command -v composer >/dev/null 2>&1; then
  $SUDO DEBIAN_FRONTEND=noninteractive apt-get install -y composer unzip || true
fi
if command -v composer >/dev/null 2>&1; then
  composer install --no-interaction
fi

healthcheck() {
  php -r 'exit(version_compare(PHP_VERSION, "8.2", ">=") && extension_loaded("curl") ? 0 : 1);'
  for f in index.php src/*.php; do php -l "$f" >/dev/null; done
  local port=8099 pid
  php -S 127.0.0.1:$port >/tmp/php-server.log 2>&1 &
  pid=$!
  until curl -fs -o /dev/null "http://127.0.0.1:$port/skycons/index.html"; do
    kill -0 "$pid" 2>/dev/null || { cat /tmp/php-server.log; return 1; }
    sleep 1
  done
  kill "$pid"
}

healthcheck

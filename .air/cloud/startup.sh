#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."

if ! command -v php >/dev/null || ! php -m | grep -qi '^curl$'; then
  export DEBIAN_FRONTEND=noninteractive
  sudo apt-get update -qq
  sudo apt-get install -y -qq php-cli php-curl
fi

healthcheck() {
  php -r 'exit(PHP_VERSION_ID >= 80200 && extension_loaded("curl") ? 0 : 1);'
  local f
  for f in index.php src/*.php; do php -l "$f" >/dev/null; done
  # built-in server (as in .air/run.json) must serve requests
  php -S localhost:8001 >/tmp/php-server.log 2>&1 &
  local pid=$!
  until curl -sf -o /dev/null http://localhost:8001/favicon.ico; do
    kill -0 "$pid" 2>/dev/null || { cat /tmp/php-server.log; return 1; }
    sleep 1
  done
  kill "$pid"
}

healthcheck

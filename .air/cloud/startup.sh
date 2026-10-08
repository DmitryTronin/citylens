#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."

if ! command -v php >/dev/null 2>&1 || ! php -m | grep -qi '^curl$'; then
  sudo apt-get update -y
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y php-cli php-curl
fi

healthcheck() {
  php -r 'exit(extension_loaded("curl") && PHP_VERSION_ID >= 80200 ? 0 : 1);'
  for f in index.php src/*.php; do php -l "$f" >/dev/null; done
  php -S 127.0.0.1:8001 >/tmp/php-server.log 2>&1 &
  local pid=$!
  trap 'kill $pid 2>/dev/null || true' RETURN
  until curl -s -o /dev/null http://127.0.0.1:8001/favicon.ico; do
    kill -0 "$pid" 2>/dev/null || { echo "php server died" >&2; return 1; }
    sleep 1
  done
}

healthcheck

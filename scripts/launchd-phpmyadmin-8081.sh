#!/bin/zsh
set -euo pipefail

PHP_BIN="/opt/homebrew/opt/php@8.3/bin/php"
PHPMYADMIN_DIR="/opt/homebrew/share/phpmyadmin"

# launchd runs with a minimal environment; ensure common binaries are available.
export PATH="/opt/homebrew/bin:/opt/homebrew/sbin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin"

cd "$PHPMYADMIN_DIR"

exec "$PHP_BIN" -S 127.0.0.1:8081 -t "$PHPMYADMIN_DIR"


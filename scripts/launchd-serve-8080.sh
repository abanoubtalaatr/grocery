#!/bin/zsh
set -euo pipefail

PROJECT_DIR="/Users/abanoubtalaat/projects/grocery"
PHP_BIN="/opt/homebrew/opt/php@8.3/bin/php"

cd "$PROJECT_DIR"

# launchd runs with a minimal environment; ensure common binaries are available.
export PATH="/opt/homebrew/bin:/opt/homebrew/sbin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin"

exec "$PHP_BIN" artisan serve --host=127.0.0.1 --port=8080


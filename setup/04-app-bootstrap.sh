#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="${PROJECT_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
cd "$PROJECT_ROOT"

if [ ! -f composer.json ]; then
  echo "[04-app-bootstrap] composer.json not found; are you in the project root?" >&2
  exit 1
fi

composer install --no-interaction --prefer-dist

php artisan migrate --force || {
  echo "[04-app-bootstrap] Migration failed; check DB credentials and retry." >&2
  exit 1
}

if command -v npm >/dev/null 2>&1; then
  npm install
  npm run build
else
  echo "[04-app-bootstrap] npm not found; skipping frontend build." >&2
fi

echo "[04-app-bootstrap] Application bootstrap complete."
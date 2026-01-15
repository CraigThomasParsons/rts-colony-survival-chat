#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="${PROJECT_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
cd "$PROJECT_ROOT"

if [ ! -f .env ]; then
  cp .env.example .env
  echo "[03-configure-env] Copied .env.example to .env"
fi

: "${APP_URL:=http://localhost}"
: "${DB_HOST:=127.0.0.1}"
: "${DB_PORT:=3306}"
: "${DB_NAME:=colony}"
: "${DB_USER:=colony_user}"
: "${DB_PASS:=strongpassword}"

php artisan key:generate --force

# Use simple sed replacements; safe to re-run if lines already present.
sed -i \
  -e "s/^APP_ENV=.*/APP_ENV=local/" \
  -e "s/^APP_DEBUG=.*/APP_DEBUG=true/" \
  -e "s#^APP_URL=.*#APP_URL=${APP_URL}#" \
  -e "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" \
  -e "s/^DB_HOST=.*/DB_HOST=${DB_HOST}/" \
  -e "s/^DB_PORT=.*/DB_PORT=${DB_PORT}/" \
  -e "s/^DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" \
  -e "s/^DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" \
  -e "s/^DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" \
  -e "s/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=database/" \
  -e "s/^CACHE_DRIVER=.*/CACHE_DRIVER=redis/" \
  -e "s/^SESSION_DRIVER=.*/SESSION_DRIVER=redis/" \
  -e "s/^REDIS_HOST=.*/REDIS_HOST=127.0.0.1/" \
  -e "s/^REDIS_PORT=.*/REDIS_PORT=6379/" \
  .env

echo "[03-configure-env] .env updated for local Arch setup."
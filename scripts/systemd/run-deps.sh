#!/usr/bin/env bash
set -euo pipefail
cd /home/craigpar/Code/rts-colony-chat

# Composer dependencies
/usr/bin/docker compose exec -T app composer install --no-interaction --prefer-dist --ansi || true

# Node dependencies (front-end / Vite container)
/usr/bin/docker compose exec -T vite npm install --no-audit --no-fund || true

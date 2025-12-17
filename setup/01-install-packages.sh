#!/usr/bin/env bash
set -euo pipefail

# Install core system packages needed to run rts-colony-chat on Arch Linux.

if ! command -v pacman >/dev/null 2>&1; then
  echo "This script is intended for Arch Linux (pacman not found)." >&2
  exit 1
fi

sudo pacman -Syu --needed \
  nginx \
  php php-fpm php-gd php-pgsql php-intl php-xml php-curl php-zip php-sqlite \
  mariadb \
  redis \
  nodejs npm \
  git \
  unzip \
  composer

echo "[01-install-packages] Package installation complete."
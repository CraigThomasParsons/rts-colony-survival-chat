#!/usr/bin/env bash
set -euo pipefail

DB_NAME="${DB_NAME:-colony}"
DB_USER="${DB_USER:-colony_user}"
DB_PASS="${DB_PASS:-strongpassword}"

if ! command -v mariadb-install-db >/dev/null 2>&1; then
  echo "MariaDB tools not found. Did you run 01-install-packages.sh?" >&2
  exit 1
fi

if [ ! -d /var/lib/mysql/mysql ]; then
  echo "[02-setup-database] Initializing MariaDB data directory..."
  sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
fi

sudo systemctl enable --now mariadb

echo "[02-setup-database] You may be prompted to secure your installation."
read -rp "Run mysql_secure_installation now? [y/N] " ans
if [[ "${ans:-N}" =~ ^[Yy]$ ]]; then
  sudo mysql_secure_installation
fi

echo "[02-setup-database] Creating database and user if they do not exist..."
mysql -u root -p <<SQL
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

echo "[02-setup-database] Database '${DB_NAME}' and user '${DB_USER}' ready."
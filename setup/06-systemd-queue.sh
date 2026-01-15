#!/usr/bin/env bash
set -euo pipefail

USER_NAME="${USER_NAME:-$USER}"
PROJECT_ROOT="${PROJECT_ROOT:-/home/${USER_NAME}/Code/rts-colony-chat}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"

cat <<UNIT
[Unit]
Description=RTS Colony Chat Laravel Queue Worker
After=network.target mariadb.service redis.service

[Service]
User=${USER_NAME}
Group=${USER_NAME}
WorkingDirectory=${PROJECT_ROOT}
ExecStart=${PHP_BIN} artisan queue:work --sleep=3 --tries=3 --timeout=120
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
UNIT

echo "[06-systemd-queue] Above is a recommended rts-queue.service. Save it to /etc/systemd/system/rts-queue.service and enable it with systemctl."
#!/usr/bin/env bash
set -euo pipefail

USER_NAME="${USER_NAME:-$USER}"
PROJECT_ROOT="${PROJECT_ROOT:-/home/${USER_NAME}/Code/rts-colony-chat}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"

cat <<SERVICE
[Unit]
Description=RTS Colony Chat Laravel Scheduler
After=network.target

[Service]
User=${USER_NAME}
Group=${USER_NAME}
WorkingDirectory=${PROJECT_ROOT}
ExecStart=${PHP_BIN} artisan schedule:run
SERVICE

cat <<TIMER
[Unit]
Description=Run Laravel Scheduler every minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Unit=rts-scheduler.service

[Install]
WantedBy=timers.target
TIMER

echo "[07-systemd-scheduler] First block is rts-scheduler.service, second is rts-scheduler.timer. Save to /etc/systemd/system and enable the timer."
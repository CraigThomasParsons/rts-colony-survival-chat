#!/usr/bin/env bash
set -e

echo "🚀 RTS Colony – Dev Bootstrap"

PROJECT_DIR="$HOME/Code/rts-colony-chat"
SYSTEMD_USER_DIR="$HOME/.config/systemd/user"

echo "📁 Checking project directory..."
cd "$PROJECT_DIR"

echo "🧱 Fixing permissions..."
sudo chown -R http:http storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

echo "🧹 Clearing Laravel caches..."
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "🗄️ Running migrations..."
php artisan migrate --force

echo "⚙️ Reloading systemd user services..."
systemctl --user daemon-reload

echo "🔁 Restarting queue worker..."
systemctl --user restart rtschat-queue

echo "⏱️ Ensuring scheduler timer is running..."
systemctl --user enable --now rtschat-scheduler.timer

echo "✅ Dev environment ready."


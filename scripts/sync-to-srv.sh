#!/usr/bin/env bash
set -e

SRC="/home/craigpar/Code/rts-colony-chat/"
DST="/srv/rts-colony-chat/"

log() {
  echo "[rtschat-sync] $1"
}

log "Syncing files dev → srv..."

rsync -az --delete \
  --exclude='.git/' \
  --exclude='node_modules/' \
  --exclude='storage/logs/' \
  --exclude='bootstrap/cache/' \
  --exclude='.env' \
  --exclude='vendor/' \
  "$SRC" "$DST"

log "Fixing permissions..."
chown -R craigpar:http "$DST"
chmod -R 775 "$DST/storage" "$DST/bootstrap/cache"

cd "$DST"

# -------------------------
# Build steps
# -------------------------

if [ -f package.json ]; then
  log "Running frontend build..."
  npm run build || log "⚠️ frontend build failed (continuing)"
fi

# -------------------------
# Laravel steps
# -------------------------

log "Running migrations..."

if php artisan migrate --force; then
  log "Migrations successful."
else
  log "❌ Migrations FAILED — check DB or env"
  exit 1
fi


log "Clearing caches..."
php artisan optimize:clear || true

# -------------------------
# Restart background workers
# -------------------------

log "Restarting queue workers..."
systemctl --user restart rtschat-queue.service || log "⚠️ queue restart failed"

log "Scheduler timer remains active (no restart needed)"

log "Sync complete."

if ! systemctl is-active --quiet httpd; then
  log "Apache not running — starting it"
  sudo systemctl start httpd
fi

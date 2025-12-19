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
  --exclude='storage/framework/' \
  --exclude='storage/app/' \
  --exclude='bootstrap/cache/' \
  --exclude='.env' \
  --exclude='vendor/' \
  "$SRC" "$DST"

# Check for --quick flag
QUICK=false
for arg in "$@"; do
  if [ "$arg" == "--quick" ]; then
    QUICK=true
    shift
  fi
done

log "Fixing permissions..."
chown -R craigpar:http "$DST" || true
chmod -R 775 "$DST/storage" "$DST/bootstrap/cache" || true

cd "$DST"

# -------------------------
# Build steps
# -------------------------

if [ "$QUICK" = true ]; then
  log "Skipping frontend build (--quick)"
else
  if [ -f package.json ]; then
    log "Running frontend build..."
    npm run build || log "⚠️ frontend build failed (continuing)"
  fi
fi

# -------------------------
# Laravel steps
# -------------------------

if [ "$QUICK" = true ]; then
  log "Skipping migrations (--quick)"
else
  log "Running migrations..."
  if php artisan migrate --force; then
    log "Migrations successful."
  else
    log "❌ Migrations FAILED — check DB or env"
    exit 1
  fi
fi


log "Clearing caches..."
php artisan optimize:clear || true

# -------------------------
# Restart background workers
# -------------------------

if [ "$QUICK" = true ]; then
  if [ -f "/usr/bin/supervisorctl" ]; then
      # If using supervisor, checking/restarting might be fast or slow. 
      # Systemd user service is usually fast.
      :
  fi
  # We still restart plugins or workers if code changed?
  # Usually code changes require worker restart.
  log "Restarting queue workers..."
  systemctl --user restart rtschat-queue.service || log "⚠️ queue restart failed"
else 
  log "Restarting queue workers..."
  systemctl --user restart rtschat-queue.service || log "⚠️ queue restart failed"
fi

log "Scheduler timer remains active (no restart needed)"

log "Sync complete."

if ! systemctl is-active --quiet httpd; then
  log "Apache not running — starting it"
  sudo systemctl start httpd
fi

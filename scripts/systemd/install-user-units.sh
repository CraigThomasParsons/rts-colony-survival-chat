#!/usr/bin/env bash
set -euo pipefail

REPO_DIR="/home/craigpar/Code/rts-colony-chat"
UNIT_SRC_DIR="$REPO_DIR/scripts/systemd"
UNIT_DST_DIR="${XDG_CONFIG_HOME:-$HOME/.config}/systemd/user"

mkdir -p "$UNIT_DST_DIR"

for f in colony-build.service colony-build.path colony-migrate.service colony-migrate.path colony-deps.service colony-deps.path colony-sync.service; do
  install -m 0644 "$UNIT_SRC_DIR/$f" "$UNIT_DST_DIR/$f"
  echo "Installed $f -> $UNIT_DST_DIR/$f"
done

# Reload user units and enable the watchers
systemctl --user daemon-reload
systemctl --user enable --now colony-build.path colony-migrate.path colony-deps.path colony-sync.service

# Show status summaries
systemctl --user --no-pager status colony-build.path || true
systemctl --user --no-pager status colony-migrate.path || true
systemctl --user --no-pager status colony-deps.path || true
systemctl --user --no-pager status colony-sync.service || true

echo
echo "If you want these to run at boot without logging in:"
echo "  sudo loginctl enable-linger \"$USER\""
echo

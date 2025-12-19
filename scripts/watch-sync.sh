#!/bin/bash
PROJECT_PATH="/home/craigpar/Code/rts-colony-chat"
SYNC_SCRIPT="$PROJECT_PATH/scripts/sync-to-srv.sh"

echo "Watching for changes in $PROJECT_PATH..."
echo "Monitoring: app, resources, routes, config"
echo "Press Ctrl+C to stop."

while true; do
  # Wait for a change event in specific directories
  inotifywait -r -e close_write,delete,move,create \
    --exclude '\.git/|node_modules/|storage/|vendor/' \
    "$PROJECT_PATH/app" \
    "$PROJECT_PATH/resources" \
    "$PROJECT_PATH/routes" \
    "$PROJECT_PATH/config" \
    2>/dev/null

  echo "Change detected. Syncing..."
  # Small sleep to let file writes settle (e.g. IDEs doing multiple ops)
  sleep 1
  
  "$SYNC_SCRIPT" --quick
  
  echo "----------------------------------------"
  echo "Sync complete. Resume watching..."
done

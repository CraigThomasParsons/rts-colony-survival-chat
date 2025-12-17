# Gatling Rsync (Dev → Runtime Deploy Pipeline)

This project uses a systemd-based "rsync-as-a-service" pipeline to keep
a development working tree in sync with a runtime tree.

This replaces Docker, Vagrant, and manual deploy steps during pre-release
development.

---

## Overview

**Source of truth (development):**

~/Code/rts-colony-chat

**Runtime / Apache-served tree:**

/srv/rts-colony-chat


On file changes, the pipeline automatically:

1. Rsyncs dev → runtime
2. Fixes permissions
3. Builds frontend assets (Vite)
4. Runs Laravel migrations
5. Clears Laravel caches
6. Restarts queue workers

All without restarting Apache or PHP-FPM.

---

## Components

### 1. Sync Script

`scripts/sync-to-srv.sh`

Responsible for:
- rsync
- permissions
- build steps
- migrations
- worker restarts

---

### 2. systemd Service

`~/.config/systemd/user/rtschat-sync.service`

```ini
[Unit]
Description=RTS Colony Chat Gatling Rsync Deploy

[Service]
Type=oneshot
ExecStart=/home/craigpar/Code/rts-colony-chat/scripts/sync-to-srv.sh

3. systemd Path Watcher

~/.config/systemd/user/rtschat-sync.path
[Unit]
Description=Watch rts-colony-chat for changes

[Path]
PathModified=/home/craigpar/Code/rts-colony-chat/app
PathModified=/home/craigpar/Code/rts-colony-chat/resources
PathModified=/home/craigpar/Code/rts-colony-chat/routes
PathModified=/home/craigpar/Code/rts-colony-chat/public
PathModified=/home/craigpar/Code/rts-colony-chat/database

Unit=rtschat-sync.service

[Install]
WantedBy=default.target


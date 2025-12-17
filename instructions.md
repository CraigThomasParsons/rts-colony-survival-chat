# How to Test Map Generation (Queues) and phpMyAdmin

These steps walk you through starting the stack, logging in, creating a game, queueing all map-generation steps, watching progress, and verifying results in MySQL via phpMyAdmin.

## Prerequisites
- Linux with Docker and Docker Compose installed
- This repo checked out: `rts-colony-chat`
- Default ports free on host:
  - App: http://localhost:8083
  - phpMyAdmin: http://localhost:8084
  - MySQL host port: 3308
  - Redis host port: 6380
  - Vite host port: 5174

## 1) Start/Restart the stack

```zsh
# From the project root
docker compose up -d

# Optional: restart the queue worker to pick up any new commands
docker compose restart queue

# See running services
docker compose ps
```

## 2) Login to the app
- Open: http://localhost:8083
- Use one of the seeded users (dev/demo):
  - User: `craigpars0061@gmail.com` / Password: `********`
  - Admin: `admin@rts-colony.local` / Password: `********`

If login fails, ensure the app is healthy and try clearing caches:
```zsh
docker compose exec app php artisan optimize:clear
```

## 3) Create a new game
- Navigate to: http://localhost:8083/new-game (requires login)
- Fill the form:
  - Name: any string, e.g. "Test Map"
  - Width: 32–128 (e.g. 38 is common)
  - Height: 32–128 (e.g. 38 is common)
- Submit. You'll be redirected to the map generation page for that map.

## 4) Start map generation (queued chain)
- On the map generation page (route: `POST /game/{mapId}/mapgen`), optionally provide a seed and submit.
- The controller dispatches a job chain that runs sequentially:
  1. `map:1init` — height map + initial cells
  2. `map:2firststep-tiles` — tile processing
  3. `map:3tree-step1` — tree algorithm (20 iterations)
  4. `map:3tree-step2` — tree algorithm, hole punching + orphan purge
  5. `map:3tree-step3` — final tree refinement (2 iterations)
  6. `map:4water` — water processing
  7. `map:5mountain` — mountain ridges (default threshold 150)

- You’ll be redirected to the progress page: `GET /game/{mapId}/progress`
  - This streams `storage/logs/mapgen-{mapId}.log` to the browser via Server‑Sent Events (SSE).

## 5) Monitor progress
- UI: http://localhost:8083/game/{mapId}/progress (auto-refreshes via SSE)
- CLI tail (optional):
```zsh
docker compose exec app tail -f storage/logs/mapgen-<mapId>.log
```
- Queue worker logs (optional):
```zsh
docker compose logs -f queue
```

## 6) Verify results in phpMyAdmin
- Open: http://localhost:8084
- Server: `mysql`
- Username: `root`
 - Password: `********`
- Database: `colony`

Check tables like `map`, `cell`, `tile`:
```zsh
# Example quick check from CLI if preferred (you will be prompted for the password)
docker compose exec mysql mysql -uroot -p colony -e "SELECT COUNT(*) AS cell_count FROM cell WHERE map_id = <mapId>;"
```

## 7) Manual/CLI testing (optional)
Run individual artisan steps directly (useful for debugging):
```zsh
# Replace <mapId> with your map id
# List available commands
docker compose exec app php artisan list | grep "map:"

# Step 1: init
docker compose exec app php artisan map:1init <mapId>

# Step 2: tiles
docker compose exec app php artisan map:2firststep-tiles <mapId>

# Step 3 (trees)
docker compose exec app php artisan map:3tree-step1 <mapId>
docker compose exec app php artisan map:3tree-step2 <mapId>
docker compose exec app php artisan map:3tree-step3 <mapId>

# Step 4: water
docker compose exec app php artisan map:4water <mapId>

# Step 5: mountain (mountainLine optional; default 150)
docker compose exec app php artisan map:5mountain <mapId> 150
```

## 8) Troubleshooting
- Command not found / class not found:
```zsh
docker compose exec app php artisan optimize:clear
docker compose restart queue
```

- Queue stalled / failed jobs:
```zsh
# Show failed jobs
docker compose exec app php artisan queue:failed

# Retry all failed
docker compose exec app php artisan queue:retry all

# Clear failed list
docker compose exec app php artisan queue:flush
```

- No data in DB:
  - Confirm chain ran: tail the `mapgen-<mapId>.log`
  - Confirm MySQL is healthy: `docker compose ps mysql`
  - Ensure you are checking DB `colony` and the right `map_id`

- phpMyAdmin not reachable:
  - Ensure container is running: `docker compose ps phpmyadmin`
  - Conflicting port 8084? Change host port in `docker-compose.yml` and `docker compose up -d`

## 9) Clean up
```zsh
# Stop stack
docker compose down

# Optional: remove volumes (deletes DB data)
docker compose down -v
```

## Reference
- Queue worker lives in the `queue` service and runs `php artisan queue:work --tries=3 --timeout=90`.
- Map generation logs are written to `storage/logs/mapgen-{mapId}.log`.
- Default credentials and ports are defined in `docker-compose.yml` and `.env`.

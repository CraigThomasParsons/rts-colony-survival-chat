# PHP Xdebug Debugging in VS Code (Docker Compose)

This guide shows how to step through Laravel code inside your Docker containers using Xdebug and VS Code.

## 1. Prerequisites

- VS Code with the PHP Debug extension (Felix Becker or Xdebug-compatible fork)
- Running `docker compose` stack (services: `app`, `queue`, `scheduler`)
- Added Xdebug to Dockerfiles (already patched)

## 2. What We Added

- Installed Xdebug via `pecl install xdebug` in each PHP Dockerfile
- Copied `docker/xdebug.ini` into container at `/usr/local/etc/php/conf.d/xdebug.ini`
- Added environment vars and host mapping in `docker-compose.yml`:
  - `XDEBUG_MODE=debug,develop`
  - `extra_hosts: host.docker.internal:host-gateway` (Linux convenience)
- Created `.vscode/launch.json` with three listener configurations mapping `/var/www/html` -> your workspace.

## 3. Verify Xdebug Loaded

Inside the running container:
```bash
docker compose exec app php -v | grep -i xdebug
docker compose exec app php -m | grep -i xdebug
```
You should see `Xdebug` listed. If not, rebuild:
```bash
docker compose build --no-cache app queue scheduler
docker compose up -d
```

## 4. VS Code Launch Config

File: `.vscode/launch.json`
- Uses port `9003` (Xdebug v3 default)
- Path mapping: container `/var/www/html` → local `${workspaceFolder}`
- Choose configuration: “Listen for Xdebug (App)” (or Queue/Scheduler).

## 5. Typical Debug Flow

1. Set a breakpoint in a controller, job, command, or route file (e.g. `app/Http/Controllers/MapController.php`).
2. Launch VS Code debug “Listen for Xdebug (App)”.
3. Hit the Laravel route via browser or curl. Execution halts at breakpoint.
4. Inspect variables in VS Code debug panel.

## 6. Queue & Scheduler Debugging

- Start “Listen for Xdebug (Queue Worker)” or “Listen for Xdebug (Scheduler)”.
- Ensure long-running workers were started after Xdebug install; if not, restart containers.
- For jobs: dispatch a job (`php artisan tinker` or app action) and watch breakpoint in job handle().

## 7. Common Issues & Fixes

| Problem | Cause | Fix |
| ------- | ----- | --- |
| Breakpoints never hit | Port mismatch or Xdebug not loaded | Confirm port 9003 open; check `php -m`; restart container |
| VS Code says “No connection” | Container can't resolve host | Ensure `extra_hosts` host-gateway and `client_host=host.docker.internal` in `xdebug.ini` |
| Composer / vendor path mapping wrong | Different working directory | Verify `WORKDIR /var/www/html` and mapping in `launch.json` |
| Queue worker ignores breakpoints | Worker process started before Xdebug or cached code | Restart queue container after Xdebug install |
| Slow requests | Xdebug overhead | Use `XDEBUG_MODE=off` or limit to `debug` only (remove `develop`) |

## 8. Toggling Xdebug Quickly

Edit `docker-compose.yml`:
```yaml
XDEBUG_MODE: off
```
Then:
```bash
docker compose up -d --no-deps app
```
Or export dynamically inside the container:
```bash
docker compose exec app bash -c 'export XDEBUG_MODE=off && php artisan config:cache'
```
(Requires restart for FPM to fully drop tracing.)

## 9. Logging (Optional)

Add to `docker/xdebug.ini`:
```ini
xdebug.log=/tmp/xdebug.log
xdebug.log_level=7
```
Then tail:
```bash
docker compose exec app tail -f /tmp/xdebug.log
```

## 10. Advanced: Conditional Breakpoints

In VS Code, right-click a breakpoint → Edit Breakpoint → add an expression, e.g.:
```
$mapId === 42
```

## 11. Testing CLI Commands

Run an artisan command while listener active:
```bash
docker compose exec app php artisan some:command
```
Breakpoints in command class will trigger.

## 12. Cleanup / Disable

To permanently disable Xdebug for performance in production builds, wrap the install with an ARG:
```dockerfile
ARG ENABLE_XDEBUG=1
RUN if [ "$ENABLE_XDEBUG" = "1" ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi
```
Build without Xdebug:
```bash
docker compose build --build-arg ENABLE_XDEBUG=0 app
```

---
**You’re ready to debug.** Start the listener in VS Code, hit a route, and step through PHP logic.

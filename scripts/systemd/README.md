# Systemd user units for RTS Colony Chat

This directory contains systemd **user** units and a helper installer to:

- Rebuild and restart your Docker Compose stack when key files change
- Run Laravel migrations when files change in `database/migrations`

## Units

- `colony-build.service`: Runs `docker compose up -d --build` in the repo
- `colony-build.path`: Watches Dockerfiles and compose files (and can optionally include manifests)
- `colony-migrate.service`: Runs `php artisan migrate --force` inside the `app` container
- `colony-migrate.path`: Watches the `database/migrations` dir
- `colony-deps.service`: Executes composer install and npm install inside containers
- `colony-deps.path`: Watches dependency manifests (composer.json, composer.lock, package.json, lock files)

All units are user-scoped (live in `~/.config/systemd/user`) and do not require sudo to run.

If you use rootless Docker, set the socket for the services by uncommenting:

```
Environment=DOCKER_HOST=unix:///run/user/%U/docker.sock
```

## Install

```bash
# From repository root
bash scripts/systemd/install-user-units.sh
```

This copies the unit files to `~/.config/systemd/user/`, reloads user units, and enables the watchers.

## Start at boot without login (optional)

```bash
sudo loginctl enable-linger "$USER"
```

## Logs

```bash
journalctl --user -u colony-build.service -f
journalctl --user -u colony-migrate.service -f
journalctl --user -u colony-deps.service -f
```

## What are .path units?

Systemd `.path` units use inotify internally to watch filesystem changes. When a watched path changes, systemd starts the associated service specified by `Unit=`.

Key directives you used:

- `PathChanged=`: triggers when the *content* of the file changes (write/close). Use it for individual files.
- `PathModified=`: triggers on metadata changes (mtime) including writes; can be used for directories to catch new files.
- `Unit=`: the service that is started on an event.

Why separate build and dependency paths?

You might rebuild images only when Docker-related files change, while installing dependencies when manifests change. This avoids unnecessary image rebuilds on simple dependency bumps.

If you prefer a single trigger, you can merge the manifest `PathChanged` lines into `colony-build.path` and remove the deps units.

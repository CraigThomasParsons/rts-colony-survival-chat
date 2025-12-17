# Laravel Dusk — Setup & Quickstart for rts-colony-chat

This document explains how to set up Laravel Dusk in this repository, run the basic browser acceptance tests, and common troubleshooting notes. It assumes you want to run the Dusk test we scaffolded under `tests/Browser/CreateGameTest.php`.

> NOTE: This file contains instructions and example commands. It does not edit your `composer.json` or other files automatically — follow the steps below in your development environment.

---

## Overview

Laravel Dusk is a browser testing tool that runs real Chrome/Chromium instances to exercise UI flows. It's a good fit for acceptance tests such as "create a game → start mapgen → watch progress".

At a high level:
- Install Dusk as a dev dependency.
- Scaffold Dusk in the app (artisan command).
- Ensure test database & environment are configured.
- Run a queue worker or use `QUEUE_CONNECTION=sync` during tests (so background jobs run synchronously).

---

## Prerequisites

- PHP compatible with your Laravel version (you already have Laravel 11).
- Composer installed.
- Chrome or Chromium installed on the machine running tests.
- On CI: either a headless Chromium in container or `selenium/standalone-chrome` service.

Local development: install `google-chrome` or `chromium` available to the user running the tests.

---

## 1) Install Laravel Dusk (dev)

Run (in project root):

```bash
composer require --dev laravel/dusk
```

Then scaffold Dusk files:

```bash
php artisan dusk:install
```

What `dusk:install` does:
- Adds `tests/DuskTestCase.php` (scaffold).
- Creates helper bootstrapping files.
- Adds a `dusk` binary entrypoint to `composer.json` scripts (if not present).
- (Optionally) can add example tests.

If you get an autoload/class-not-found error, run:

```bash
composer dump-autoload
```

---

## 2) Dusk test scaffolding we added

The repo contains:
- `tests/DuskTestCase.php` — a Dusk base test class (scaffolded).
- `tests/Browser/CreateGameTest.php` — a skeleton acceptance test for "create game → start mapgen → progress".

Verify those files exist and are autoloadable. If you adapted namespaces or test paths, ensure `composer dump-autoload` was run.

---

## 3) Prepare test environment and DB

Dusk runs tests in a separate browser session and usually uses your application configured for testing.

Recommended local steps:

- Create a `.env.dusk.local` or `.env.testing` with appropriate test settings:
  - `APP_URL=http://127.0.0.1:8000`
  - `DB_CONNECTION` / `DB_DATABASE` set to your test DB (or use sqlite :memory: for speed)
  - `QUEUE_CONNECTION=sync` (recommended for acceptance tests so queued jobs run inline)
  - Any other services you need (mail, cache) — use lightweight local versions when possible

Example `.env.dusk.local` fragment:

```
APP_ENV=testing
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
QUEUE_CONNECTION=sync
```

If using an actual database, create & migrate it before running Dusk:

```bash
php artisan migrate --env=testing
# or for sqlite file:
php artisan migrate --database=sqlite --env=testing
```

If your tests rely on factories, ensure factories exist and the test seeding flow is prepared.

---

## 4) ChromeDriver / browser driver

Dusk manages ChromeDriver automatically in most setups. The `php artisan dusk:install` command also adds `chrome-driver` convenience scripts.

If you need to refresh the ChromeDriver (matching installed Chrome version), run:

```bash
php artisan dusk:chrome-driver --detect
# or
php artisan dusk:chrome-driver  # follow prompts / docs
```

On Linux you may need to install additional packages required by headless Chromium (fonts, kernel libs). If tests fail to start the browser, inspect the worker output.

---

## 5) Running Dusk locally

Option A — simple (recommended for initial tests)

Start your local app server in one terminal (if not using `php artisan serve` via Dusk):

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

In another terminal, run:

```bash
php artisan dusk
```

Option B — run Dusk headless without separate serve (Dusk can start the application automatically depending on config). If you get "Could not start ChromeDriver", ensure Chrome is installed and accessible.

Notes:
- If tests use queues/background jobs, prefer `QUEUE_CONNECTION=sync` for deterministic runs.
- If a test times out due to long background tasks, increase timeouts in the test or run a worker in parallel.

---

## 6) CI recommendations (GitHub Actions example)

A minimal GitHub Actions job outline (conceptual):

- Start a job with `ubuntu-latest`.
- Install PHP, composer, node (if needed).
- Install Chrome (or use `google-chrome-stable`), or use Selenium container.
- Run migrations and start `php artisan serve` (or run in background).
- Run `php artisan dusk` headless.

Example snippet (conceptual, adapt to your runner):

```yaml
jobs:
  dusk:
    runs-on: ubuntu-latest
    services:
      # Optionally run selenium if you prefer that approach
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.3
      - name: Install Chrome
        run: sudo apt-get update && sudo apt-get install -y google-chrome-stable
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      - name: Prepare env
        run: cp .env.example .env && php artisan key:generate
      - name: Migrate
        run: php artisan migrate --force
      - name: Run Dusk
        run: php artisan dusk --no-ansi --no-interaction
```

Refer to Laravel Dusk docs for full recommended CI configuration.

---

## 7) Test-specific notes for this project

- The acceptance flow we scaffolded expects authentication — the test uses `User::factory()->create()` and `loginAs($user)`. Ensure your User factory exists and `users` table migrations are available.
- The test also triggers map generation and progress pages. For reliable tests:
  - Use `QUEUE_CONNECTION=sync` so `RunMapGenerationStep` runs inline during the test (jobs will append output to `storage/logs/mapgen-<id>.log`).
  - Run any required migrations before tests.
  - If you prefer to test UI-only behavior and not run the full map generation, stub or mock job dispatching, or assert UI redirects only.

---

## 8) Troubleshooting

- "Class not found" for `DuskTestCase` or test classes:
  - Run `composer dump-autoload`.
  - Check namespaces and file locations (Dusk base must be under `tests/DuskTestCase.php`).

- Dusk fails to start ChromeDriver:
  - Make sure Chrome/Chromium is installed and the driver version matches.
  - Run `php artisan dusk:chrome-driver --detect`.
  - Check system dependencies required by Headless Chrome on Linux (fonts, libX11, etc).

- Tests time out or background jobs never finish:
  - Use `QUEUE_CONNECTION=sync` during tests to run queued jobs inline.
  - Alternatively, run `php artisan queue:work` in a parallel terminal while running Dusk.

- SSE/progress stream shows no output:
  - Ensure `storage/logs/mapgen-<id>.log` gets written by the queued job (open the file from the worker terminal).
  - If the worker writes logs but SSE still receives nothing, check response buffering on your webserver or Dusk network logs.

- Permissions:
  - Ensure `storage/` and `bootstrap/cache` are writable by the user running the tests/worker.

---

## 9) Next steps / enhancements

- Add a `php artisan dusk --filter CreateGameTest` convenience script if you want to run a single test quickly.
- Add a small `tests/Browser/Pages` Page object for `NewGame` if your UI grows more complex.
- If your acceptance suite grows, run workers in CI or use containerized Selenium for full parity.

---

If you want, I can:
- Add a composer `scripts` entry for `dusk` or `dusk:ci`.
- Create a GitHub Actions workflow file example pre-configured for this repo.
- Adjust `tests/DuskTestCase.php` to customize ChromeDriver options (headless flags, window size).

Tell me which of those you'd like next and I’ll prepare the files/instructions.
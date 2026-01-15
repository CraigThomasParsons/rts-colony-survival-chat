# Local Testing Without Docker (SQLite)

This project is built and deployed through Docker Compose, but you can still run the PHP test suite locally without MySQL by using the lightweight SQLite configuration added in December 2025.

## When to use this
- You want to run `php artisan test` outside Docker.
- You don't have MySQL, or `pdo_mysql` isn't available in your PHP build.
- You only need the application layer tests (feature/unit) to execute; queue/mapgen pipelines still expect Docker.

## How it works
- `phpunit.xml` now forces the testing environment to use the SQLite driver (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) and enables foreign keys.
- The bootstrap script checks `APP_STORAGE`; when set, Laravel writes all logs/cache into `storage/testing` so there are no permission errors when running locally.
- `config/database.php` falls back to SQLite automatically if MySQL drivers are missing or `DB_FORCE_SQLITE=true`.

## Quick start
```zsh
# Ensure dependencies exist
composer install

# Optional: create writable storage/testing directory
mkdir -p storage/testing/{app,framework/cache,logs}

# Run tests entirely on SQLite
APP_STORAGE="$(pwd)/storage/testing" php artisan test
```

## Notes
- Feature tests that rely on Livewire/Volt still run because factories seed into the in-memory database automatically.
- Queue/Artisan pipeline tests that require Laravel Horizon or external services should continue to run inside Docker (`docker compose exec app php artisan test`).
- For full QA (map generation, workers, MySQL data), run the existing `Run Codex QA` VS Code task or `bash .codex/run-tests.sh`, both of which use Docker Compose.

## Troubleshooting
- **`Undefined method useStoragePath`**: ensure you pulled the latest `bootstrap/app.php` which adds the `APP_STORAGE` override via `tap()`.
- **`There is no existing directory at "storage/logs"`**: confirm `APP_STORAGE` points to a writable path and the directory exists.
- **Still hitting MySQL**: make sure no `.env.testing` overrides are present; `phpunit.xml` now exports `DB_FORCE_SQLITE=true` for the duration of the run.

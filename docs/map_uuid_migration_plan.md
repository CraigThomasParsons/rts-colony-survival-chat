# Map UUID Migration Plan

This document captures the full scope and phased approach for switching the legacy `map` table (and all dependents) from an auto-incrementing integer primary key to UUIDs.

## Goals

- Guarantee every `map` row uses a UUID primary key and that all relationships, routes, and jobs accept that UUID.
- Preserve existing data (maps + generated assets) by backfilling UUIDs and re-keying child tables (`cell`, `tile`, `game_map`, etc.).
- Minimize downtime and provide a safe rollback story.
- Keep artisan commands, controllers, Livewire components, and SSE progress feeds working without manual map-id conversions.

## Current Schema Inventory

| Table | Column(s) | Notes |
| --- | --- | --- |
| `map` | `id` (INT PK), FK-like columns: `mapstatuses_id`, `state`, `next_step`, `is_generating` | Target table to convert to UUID PK.
| `cell` | `id` INT AUTO_INCREMENT PK, `map_id` INT (indexed), plus composite unique (`coordinateX`, `coordinateY`, `map_id`) | Cells keep integer auto-increment PKs; reference map via FK.
| `tile` | `id` INT AUTO_INCREMENT PK, `map_id` INT, `cell_id` INT, indexes on (`cell_id`,`map_id`) | Tiles keep integer auto-increment PKs; reference map and cell via FKs.
| `game_map` | `map_id` INT FK to `map.id` (cascade delete) | Pivot between games and maps.
| `resource_nodes` (newer RTS pipeline) | `id` INT AUTO_INCREMENT PK; `map_id` UNSIGNED BIGINT referencing `maps` table | Separate system; its own PK stays integer auto-increment; only `map_id` will reference a UUID map.
| SQL seed files (`sqlseed/map_one.sql`) | Hard-coded numeric `map_id` values | Must be regenerated or updated to string UUID literals.

Application touch points discovered via `grep`:

- Routes (`routes/web.php`) expose `{mapId}` parameters across editor, progress, preview, and save endpoints.
- Controllers (`GameController`, `MapController`) and jobs (`FinalizeMapGeneration`) load maps via integer IDs.
- Artisan commands (e.g., `map:1init`, `map:3tree-step*`, `map:4water`, `map:5mountain`) accept `mapId` arguments and query by integer.
- Helpers/services (`MapRepository`, `MapHelper`, `MapLoader`, `MapGenerator`, work givers, BotManager, etc.) issue raw DB queries filtering by `map_id`.
- Frontend assets (`resources/views/game/progress.blade.php`, `public/js/progress.js`) show/stream logs keyed by numeric IDs.
- Docs/scripts (`instructions.md`, `docs/architecture.md`) reference numeric IDs in command snippets and log filenames.

## Proposed Migration Strategy

1. **Introduce UUID on maps (dual-write phase)**
   - Add `uuid` column to `map` as `uuid()->unique()` while keeping existing integer PK temporarily.
   - Backfill existing `map` rows with generated UUIDs (`Str::uuid()`), enforce `NOT NULL`.
   - Add `map_uuid` columns to dependent tables (`cell`, `tile`, `game_map`, etc.) with indexes. Their primary keys remain integer AUTO_INCREMENT.
   - Update Eloquent `Map` model to emit UUIDs on `creating` (using `Str::uuid()`); plan to promote `uuid` to the primary key in a later step.

2. **Cut application logic to UUID maps only**
   - Update controllers, commands, jobs, and helpers to resolve maps by the `uuid` column (temporarily via `$map = Map::where('uuid', $mapId)->firstOrFail()` until PK promotion).
   - Adjust relationships and pivots to reference `map_uuid` while leaving child table PKs unchanged (integer AUTO_INCREMENT).
   - Update factories/seeders so `Map` sets `uuid` automatically.
   - Ensure console commands treat `{mapId}` as UUID strings and validate when appropriate.
   - Update log filenames and SSE routes to expect UUID strings for maps; child resource identifiers remain integers.

3. **Promote map UUID to PK; keep child PKs**
    - Add a migration to:
       - Update FK constraints so `cell.map_uuid`, `tile.map_uuid`, `game_map.map_uuid` reference `map.uuid` (ensure unique + indexed).
       - Promote `map.uuid` to the primary key (rename to `id` with `primary()`), deprecating the legacy integer `map.id`.
       - In child tables, rename `map_uuid` → `map_id` (UUID type) for consistency. Do NOT change `cell.id` or `tile.id`—they remain integer AUTO_INCREMENT.
       - Drop legacy integer `map_id` columns from child tables after verifying data integrity.

4. **Update application bindings**
   - Modify route-model binding to accept UUID strings for `Map`.
   - Replace integer casts/type hints for map IDs with string-based equivalents.
   - For raw DB helpers (e.g., `MapRepository`), filter by `map_id` as UUID; continue using integers for `cell.id`, `tile.id` where applicable.
   - Regenerate SQL seed data to include UUID map values; leave child IDs numeric.

5. **Clean up + enforce**
   - Remove dual-write columns and legacy helpers once all code paths rely on map UUIDs.
   - Add database constraints (FKs + indexes) and Laravel validation to prevent regressions.
   - Update docs to clarify: only `map` uses UUID PK; `cell` and `tile` keep integer AUTO_INCREMENT PKs.

## Testing / Verification

- Run full `.codex/run-tests.sh` suite and targeted artisan commands (`map:1init`, `map:2firststep-tiles`, etc.) using UUID-backed maps.
- Smoke test UI flows: create new map via `/game`, kick off generation, and view progress/preview to ensure SSE + log tails work with UUIDs.
- Verify cascading deletes: deleting a map removes cells/tiles/game_map rows via UUID FKs.
- Backfill validation: migrate existing DB snapshot and compare row counts (cells/tiles per map) before vs after.

## Rollback Plan

- During dual-write phase, keep integer IDs and original `map_id` columns intact so we can revert by switching application lookups back to integers.
- Before dropping legacy columns, capture a MySQL backup. Rolling back past the PK switch requires restoring that snapshot.
- Versioned migrations ensure `php artisan migrate:rollback` re-adds integer PK + drops UUID columns if needed.

## Open Questions / Follow-ups

- Does the newer `maps`/`tiles` schema (2025_01_01_000003) need to stay numeric or should it mirror this UUID plan?
- Are there external systems (analytics, telemetry, exports) that still expect numeric map IDs? If so, we may need a derived `short_id` field for human-friendly displays.
- Should log filenames keep numeric suffixes or move to `mapgen-{uuid}.log`? Confirm with ops/log rotation scripts before cutting over.

Once these answers are locked, we can proceed to implement the migrations and code changes outlined above.

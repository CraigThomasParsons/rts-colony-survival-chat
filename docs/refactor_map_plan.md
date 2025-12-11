# MapController Eloquent Refactor Plan

## 1. Goals
- Remove `MapRecord`, `MapHelper`, and `MapMemory` dependencies from `app/Http/Controllers/MapController.php`.
- Ensure every controller step reads/writes state via `App\Models\Map` fields (`state`, `mapstatuses_id`, `next_step`, `is_generating`).
- Keep existing tile/cell helper workflows functional until their replacements are ready.
- Unblock the height-map pipeline so `php artisan map:1init {mapId}` (and downstream steps) operate purely on Eloquent data.

## 2. Current Pain Points
- `runFirstStep` instantiates helper `MapRecord` and calls `setState`, which assumes helper data arrays rather than Eloquent attributes.
- Steps 2–5 repeatedly fetch helper map models (`MapHelper`, `MapMemory`) just to change state fields or perform status transitions.
- Controller status strings don’t always map to the canonical `MapStatus` rows, and `mapstatuses_id` is not guaranteed to stay in sync with `state`.
- `MapRepository::findFirst` now returns an Eloquent model, but controller logic still treats it as a helper, risking method/property mismatches.

## 3. Refactor Blueprint

### 3.1 Shared Patterns to Apply Everywhere
1. **Map Fetching**: use `Map::findOrFail($mapId)` (or check for null to create) instead of helper wrappers.
2. **State Updates**:
   - Set `$map->state = MapStatus::CONSTANT;`
   - Sync `$map->mapstatuses_id = MapStatus::firstWhere('name', MapStatus::CONSTANT)?->id;`
   - Persist via `$map->save();`
3. **Next Step**: use `$map->next_step` (snake_case matches DB columns) for UI hints.
4. **Generation Flag**: set `$map->is_generating = true/false` around long-running steps for editor polling.
5. **Redirects/Responses**: always redirect via named routes where available to keep consistent navigation.

### 3.2 Method-by-Method Targets
| Method | Helper Usage Today | Eloquent Replacement |
| --- | --- | --- |
| `runFirstStep` | Creates `MapRecord`, calls `setState`, pipes through `MapMemory`. | Create/find `Map` model directly; set `state/mapstatuses_id/is_generating`; pass bare `$map` data to generator (consider new adapter later). Ensure `next_step = 'step2'` when finished. |
| `runSecondStep` | Calls `$map->setState`, uses `MapHelper` for hole punching. | Convert `setState` call to direct field assignments; guard hole punch logic until helper port is ready (wrap behind feature flag or dedicated service). |
| `runThirdStep` | Uses `MapHelper` and helper states (`setState`, `next_step`). | Replace `setState` with direct assignments; update `next_step` to `'treeStepSecond'`; ensure map saves after `state` changes before heavy processing. |
| `runTreeStepTwo` | Same helper pattern for state and map loader. | Set `state` to `TREE_2ND_COMPLETED` via Eloquent; continue using helper for now but treat `$mapLoader` purely as processing engine, not source of truth. |
| `runTreeStepThree` | `setState` + helper loader. | Direct Eloquent assignments for `state`; evaluate whether `MapRepository::findAllCells` should feed `MapHelper` or whether we can pass raw arrays. |
| `runMapLoad` | References `$map->nextStep` (camelCase). | Switch UI hints to use `$map->next_step` string, falling back to computed `status()` mapping if null. |
| `runFourthStep` | Uses `MapMemory` to wrap map record. | Provide minimal data (`$map->toArray()`) to `WaterProcessing` or refactor processor to read from DB layer only; ensure map state transitions (e.g., `state = MapStatus::TREE_GEN_COMPLETED`). |
| `runLastStep` | Pure helper state; setState missing. | Update to set `state` to mountain-specific status when available (or add placeholder constant). |
| `status` | Already reads `Map::find`. | Ensure mapping uses canonical constants and that each controller method updates `state`/`mapstatuses_id` consistently so polling works. |

### 3.3 Additional Supporting Work
- **MapRepository**: leave helper-based cell/tile loaders intact temporarily; controllers can keep calling them for data shaping even while map state is Eloquent-driven.
- **Processing Classes** (`TreeProcessing`, `WaterProcessing`, `MountainProcessing`): verify constructors accept raw arrays or Eloquent models. Where they require helper wrappers, keep the adapters contained within the processing command layer, not the controller.
- **Validation Hooks**: add guard clauses if `Map::find($mapId)` returns null (return 404 or redirect with message).

## 4. Implementation Steps
1. **Controller Rewrite** (single PR):
   - Import `Illuminate\Support\Facades\DB` only if needed; prefer Eloquent relationships.
   - Replace every `setState` call with `updateState($map, MapStatus::CONST)` helper function (inline private method) to avoid repetition.
   - Normalize `next_step` vs. `nextStep` naming.
   - Remove unused helper imports at top of controller.
2. **Map Model Helper**: optional utility method `Map::updateStatus(MapStatus::NAME)` if controller repetition remains high.
3. **Route/Blade Audit**: ensure views expect `next_step` snake_case and `state` strings coming from Eloquent.
4. **Database Alignment**: confirm migration adding `state`/`next_step` has been run; include instructions in README snippet if others need to migrate.

## 5. Testing Strategy
- `php artisan migrate` to ensure `state/next_step` columns exist.
- `php artisan map:1init {mapId}` to verify Step 1 completes with new controller logic.
- Trigger subsequent steps via `/Map/step2/...` and `/Map/treeStep2/...` to catch any lingering helper dependencies.
- Run existing Codex QA task (`bash .codex/run-tests.sh`) before pushing.

## 6. Risks & Mitigations
- **Processors needing helper adapters**: keep current `MapHelper` constructions inside processing-specific service classes until we refactor them; do not delete helpers yet.
- **Status drift**: add centralized helper to set both `state` and `mapstatuses_id` together to prevent mismatches.
- **Long-running steps**: ensure `$map->refresh()` between stages if parallel jobs mutate the same row.

## 7. Follow-Up Tasks
- Port `TreeProcessing`/`WaterProcessing` classes to operate on Eloquent collections directly, allowing removal of `MapHelper` entirely.
- Add feature tests covering each step endpoint (happy path + missing map).
- Update documentation/README with the new field workflow and commands to run the pipeline end-to-end.

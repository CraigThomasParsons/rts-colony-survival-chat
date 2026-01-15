# Architecture Overview

This document outlines the high-level architecture of the Web-Based RTS Colony Simulation Game built with **Laravel + Livewire**. It is designed to help maintain a clean, scalable, and extensible codebase.

---

## 1. System Architecture Summary
The system is composed of:
- A Laravel backend responsible for state management, data storage, and game logic.
- Livewire components for real-time UI interactions.
- A tick-based simulation loop.
- Eloquent models representing game entities.
- Blade templates for rendering the game map and UI.

---

## 2. Core Layers
### **2.1 Backend (Laravel)**
Handles:
- Map generation
- Worker AI logic
- Task scheduling
- Resource management
- Building system
- State persistence

**Key Components:**
- `App\Models`: Entity models
- `App\Services`: Game logic and systems
- `App\Http\Livewire`: UI controllers
- `database/migrations`: Schema

---

### **2.2 Frontend (Livewire + Blade)**
Responsible for:
- Rendering grid layout
- Worker positions and actions
- Interactive components (buttons, overlays)
- State updates via Livewire events

Frontend is intentionally thin to keep logic in the backend.

---

## 3. Game Systems
### **3.1 Map System**
- Procedural generation using `MapGenerator` service
- Tiles stored as rows in database
- Terrain types and resource clustering
- Entities placed on tiles

### **3.2 Worker System**
- Worker model tracks: position, state, fatigue, inventory
- Pathfinding/transit logic
- Task queues
- AI behaviors encapsulated in `WorkerService`

### **3.3 Resource System**
- `ResourceNode` model
- Workers harvest resources, store in inventory
- Delivery to stockpile
- Node depletion logic

### **3.4 Building System**
- Buildings occupy one or more tiles
- Costs: stone, wood, gold
- Construction tasks managed by workers

### **3.5 Game Loop**
- Tick engine triggers worker updates
- Livewire listens for state changes
- UI updates incrementally

---

## 4. Data Model Overview
### **Primary Tables**
- `maps`
- `tiles`
- `resource_nodes`
- `workers`
- `inventories`
- `tasks`
- `buildings`

### **Relations**
- Map → Tiles (1:M)
- Tile → ResourceNode (1:1 or null)
- Worker → Inventory (1:1)
- Tile → Building (1:1 or shared via pivot)

---

## 5. Flow of a Game Tick
1. Worker checks task queue
2. Pathfinding determines step
3. Worker moves one tile
4. If on resource node → harvest
5. Add item to inventory
6. If inventory full → path to stockpile
7. Deliver resources
8. Update worker fatigue
9. Save state
10. Livewire updates view

---

## 6. Event Flow Example: Harvesting
1. Player designates a resource node or worker auto-selects
2. Worker enters `Harvesting` state
3. Worker moves to node
4. Harvest amount added to inventory
5. Node quantity reduced

---

## 7. Scalability Considerations
- Tick batching for multiplayer
- WebSockets for faster UI updates
- Caching heavy map queries
- Data partitioning large maps
- Worker AI throttling

---

## 8. Future Extensions
- Combat systems
- Faction AI
- Diplomacy
- Weather systems
- Economy automation

---

## 9. Integration Notes for Codex
When modifying game logic:
- Keep calculations in services, not Livewire
- Maintain separation: UI (Livewire) vs Logic (Services)
- Return unified diff patches
- Reference `docs/chatgpt_full_history.md` for background

---

## 10. Foundational Build Blueprint
The sections below translate the architecture into a concrete Laravel + Livewire implementation for a persistent Warcraft‑style colony sim. Each subsection can be tackled independently while still plugging into the shared simulation loop and MySQL state.

### 10.1 Game Architecture
- **Domain services** (`App\Services\*`) orchestrate systems: `MapGenerator`, `WorkerService`, `TaskScheduler`, `ResourceService`, `BuildingService`, and `SimulationEngine`.
- **Models** capture persistent entities: `Map`, `Tile`, `ResourceNode`, `Worker`, `Inventory`, `Stockpile`, `Building`, `Task`, and `PlayerSession`.
- **Events** encapsulate published state changes (`WorkerMoved`, `ResourcesDeposited`) so Livewire or future WebSocket listeners can react without duplicating logic.
- **Pipelines** keep heavy logic out of controllers by leaning on actions/jobs (e.g., `ProcessGameTick` job calls `SimulationEngine::tick()`).

### 10.2 Laravel / Livewire Setup
1. **Migrations** for all entities plus foreign keys enforcing the relations defined earlier.
2. **Seeders** that provision a starter map and a small worker roster so the game boots into a playable state.
3. **Livewire components**:
   - `GameBoard` renders the tile grid, workers, and buildings.
   - `WorkerPanel` shows selected worker stats/inventory.
   - `HudStats` surfaces resource totals and tick rate.
4. **Routes**: a single `/colony` route pointing to a Blade view that mounts the Livewire components.
5. **Service container bindings** to inject the simulation services anywhere (`app()->singleton(SimulationEngine::class, ...)`).

Basic Livewire render method example:
```php
class GameBoard extends Component
{
    public $mapId;

    public function mount() { $this->mapId = Map::current()->id; }

    public function getTilesProperty()
    {
        return Tile::with('resourceNode', 'building', 'workers')
            ->where('map_id', $this->mapId)
            ->orderBy('y')->orderBy('x')
            ->get();
    }
}
```

### 10.3 Real-Time Sync
- **Livewire polling**: initially use `wire:poll.500ms` to refresh key components; pave the way to swap to WebSockets later.
- **Broadcasting**: wrap critical mutations in events fired via `broadcast()` so multiple browser tabs stay consistent.
- **Optimistic UI**: front-end predicts movement while awaiting server confirmation to keep responsiveness.
- **Caching**: store frequently accessed map tiles in Redis to cut down on per-poll MySQL reads (`Cache::remember("map:$id", 1, fn () => Tile::... )`).

### 10.4 Game Loops
- `app/Console/Kernel.php` schedules `ProcessGameTick` every second (or faster) using Laravel’s scheduler.
- `ProcessGameTick` dispatches a job that:
  1. Locks the map row (`forUpdate()`) to prevent concurrent ticks.
  2. Calls `SimulationEngine::tick(Map $map)`.
  3. Broadcasts summary events (resources updated, worker moved).
- The engine coordinates services in order: fatigue, tasks, movement, harvesting, delivery, construction, world events.

Sample pseudo-code:
```php
class SimulationEngine
{
    public function tick(Map $map): void
    {
        DB::transaction(function () use ($map) {
            $workers = Worker::where('map_id', $map->id)->get();
            foreach ($workers as $worker) {
                $this->taskScheduler->ensureTask($worker);
                $this->workerService->advance($worker);
            }
            $this->resourceService->processRegrowth($map);
        });
    }
}
```

### 10.5 Map & Unit Systems
- **Map Generation** (see `docs/map_generation_spec.md`): artisan command `php artisan map:generate --size=64` invokes `MapGenerator`.
- **Tile storage**: `tiles` table houses terrain, references to resource nodes/buildings, and walkability flags.
- **Units**: `workers` table tracks coordinates, state, fatigue, inventories, and assigned task IDs.
- **Pathfinding**: start with grid stepping stored as JSON paths on each worker; upgrade to A* within `PathfindingService`.
- **Commands**: players trigger worker actions via Livewire events (`wire:click="assign('harvest', $tileId)"`) which push tasks into the `tasks` table.

### 10.6 Co-op Multiplayer (Single Persistent Colony)
- **Player sessions**: `player_sessions` table ties users to the single shared map plus preferences (camera position, UI filters).
- **Permissions**: simple policy where every authenticated user can issue commands but actions are rate-limited per session to avoid spam.
- **Shared economy**: all resource totals live on the `maps` or `stockpiles` tables, keeping the experience cooperative instead of competitive.
- **Conflict resolution**: `TaskScheduler` locks a resource node once a worker claims it, ensuring two users cannot issue conflicting orders.
- **Persistence**: every mutation flows through Eloquent models, so refreshing the page simply reloads the saved map state—no transient matches or resets.

These blueprints supply the “foundational code” layout ChatGPT previously described, grounded in the existing architecture guidance and ready for incremental implementation.

End of file.

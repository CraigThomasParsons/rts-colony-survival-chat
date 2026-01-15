## Game Placeholder Assets

The front-end includes a minimal Phaser setup (see `resources/js/app.js`) that loads placeholder assets via `resources/js/game/assets.js`.

Expected paths:

```
public/images/game/tiles/grass.png            (fallback: grass.jpg)
public/images/game/units/worker_sheet.png     (spritesheet, horizontal row; fallback: worker_sheet.jpg or single worker.png)
```

Worker animation:

1. Provide a spritesheet named `worker_sheet.png` with frames laid out horizontally.
2. Each frame should be 64x64. Default expected frame count: 4.
3. Adjust `workerFrames` when starting the game:
  ```js
  window.startFeudalFrontiersGame({ workerFrames: 6, idleFrameRate: 8 });
  ```

### Worker Spritesheet Layout (Directional Rows)

Provide a `worker_sheet.png` containing horizontal frames for each animation row. Default expectation:

```
Rows (top → bottom):
0: idle        (frame indices 0 .. workerFrames-1)
1: walk north  (frame indices workerFrames .. 2*workerFrames-1)
2: walk south  (frame indices 2*workerFrames .. 3*workerFrames-1)

Example with workerFrames=8 and 64x64 frames (sheet size: 512x192):

Row 0 (idle):       [0][1][2][3][4][5][6][7]
Row 1 (walk north): [8][9][10][11][12][13][14][15]
Row 2 (walk south): [16][17][18][19][20][21][22][23]
```

Frame index formula: `globalFrameIndex = rowIndex * workerFrames + columnIndex`

Recommended:
- Frame size: 64x64
- Frames per row: 6–8 (idle can use fewer; duplicates acceptable for timing)
- Row count (initial): 3 (idle, north, south)
- Expand later: add rows for east/west or diagonal movement (update `rows` config and add new animations).

Start the game with custom configuration:

```js
window.startFeudalFrontiersGame({
  workerFrames: 8,
  idleFrameRate: 8,
  rows: { idle: 0, north: 1, south: 2 },
  camera: { zoom: 1, minZoom: 0.5, maxZoom: 2, drag: true }
});
```

If you later change the sheet ordering, simply adjust the `rows` mapping (e.g. `{ idle: 2, north: 0, south: 1 }`).

Testing quickly:
1. Drop `worker_sheet.png` into `public/images/game/units/`.
2. Open the app page containing the `#phaser-game` mount.
3. In browser devtools console run the snippet above.
4. Move the mouse upward/downward over the canvas to see north/south animations; otherwise idle plays.
5. Use scroll wheel to zoom, drag to pan.

Troubleshooting:
- Animation not playing: verify texture key `worker` exists in `scene.textures.list`.
- Wrong frames: confirm sheet width = `frameWidth * workerFrames` and height = `frameHeight * numberOfRows`.
- Fallback triggered (static frame): ensure filename is exactly `worker_sheet.png` (case sensitive) and not blocked by caching.

If you only have JPGs, the loader will attempt PNG first, then dynamically retry JPG. If the sheet is missing it will fall back to a single `worker.png` frame.

1. Replace the `.jpg` with a proper `.png` (same filename pattern).
2. Remove the temporary `.txt` placeholder markers if present.
3. Rebuild front-end assets (Vite or Mix) if required.

Adding new assets:

1. Drop files under `public/images/game/<category>/<name>.png`.
2. Add an entry to `resources/js/game/assets.js` with `key`, `pathPng`, and optional `pathJpg`.
3. Reference in a Phaser scene with `this.add.image(x, y, key)`.

The loader will log a warning if neither PNG nor JPG are found.

### How to use
1. Ensure the `app/Helpers` directory is present (it was merged into the project).  
2. Run the commands in order to build the map into your DB / map model:  

```bash
php artisan map:1init
php artisan map:2firststep-tiles
php artisan map:3mountain
php artisan map:4water
```

3. Review `resources/views/mapgen/` for a minimal UI to preview progress (optional).

### 🎵 Bandcamp Library Downloader

This repository also includes a Bandcamp music library downloader for ArchLinux users. See [BANDCAMP_README.md](BANDCAMP_README.md) for details.

**Quick start:**
```bash
./install_bandcamp.sh
python3 bandcamp_downloader.py -u YOUR_USERNAME -c cookies.json
```

### Notes on integration
- The mapgen uses its own MapDatabase and Cell models under `app/Helpers/MapDatabase/`. If you wish to map these directly to your game's `tiles` and `resource_nodes` tables, I can add an importer that converts generated Cells into Eloquent `Tile` and `ResourceNode` rows. Tell me if you'd like me to implement that importer (recommended).

- The map generator supports deterministic seeds. Pass seed options via the console commands (check command options in `app/Console/Commands/`).

## Co-Op Procedurally generated RTS Colony survival

That is the idea anyways

Includes:

- A* pathfinding
- GameEngine tick-loop integration
- Procedurally Generated Terrain

🗺️ Gameplay Overview 🧑‍🌾 Colonists

Each colonist has:

- Stats

- Mood

- Needs

- Skills

- Current job state

- Pathfinding agent

🔁 Tick Loop

- The entire simulation ticks at 250 ms intervals:

- Update colonists

- Process jobs

- Evaluate states

- Move units

- Harvest/build

- Sync to clients

🌾 Resources

- Trees
- Stone
- Fields (wheat, barley, vegetables)
- Forageables

🏗️ Buildings

- Stockpiles
- Houses
- Workshops
- Farms
- Storage huts
- Walls and defenses

🧭 A* Pathfinding

- Custom binary min-heap (~40% faster than SplPriorityQueue)
- Terrain weights (mud, grass, roads)
- Diagonal movement
- Early exit optimization

🤝 Contributing

- Contributions are welcome!
- Fork the repo
- Create a feature branch
- Submit a PR
- Include test coverage where appropriate
- Follow the [coding conventions](docs/coding-conventions.md) (readability over brevity)
- Run `composer cs:check` before pushing and `composer cs:fix` to auto-format.
- Apply SOLID (especially dependency inversion) by injecting collaborators instead of instantiating them inline—see conventions doc for details.

⚙️ Systems

- Save/load multiple worlds
- Deeper colonist AI (psych traits, work priorities)
- Auto-designated work zones
- Blueprint system for buildings

🌐 Multiplayer Enhancements

- Player factions
- Territory
- Shared trade economy

# Colony RTS Project README

## Overview
A multiplayer, co-operative **RTS-style colony simulation game** built with **Laravel**, **Livewire**, and **MySQL**. Inspired by early Warcraft-style mechanics, the project focuses on resource gathering, worker AI, map generation, and persistent world simulation.

The goal: a lightweight, browser-based RTS foundation with real-time updates, simulation ticks, and a data-driven backend suitable for expansion into a full RTS/colony builder.

---
# Name ideas
 - Colonizing Scum
 - Frontier Scoundrels
 - Feudal Frontiers
 - Dominion Foundry
 - Bannered Colonies
 - Lordship Settlers
 - Barony Builders
 - Crown & Colony

## Key Features
### 🎮 Core Gameplay
- Real-time-ish RTS colony simulation  
- Co-op multiplayer architecture (shared world state)  
- Worker units with:  
  - Task assignment  
  - Fatigue & retries  
  - Multi-worker coordination  
  - Pathing logic  
  - Individual inventories  
- Persistent world saved in MySQL  
- Upgradable buildings & resource pipelines  

---

## 🗺️ Procedural Map Generation
- Grid-based world (X × Y tiles)  
- Seed-based terrain generation  
- Tree distribution and surface resources  
- Underground layer support (see below)  
- Resource nodes stored in DB via Eloquent  

---

## 🌑 Underground Layer Architecture
A full two-layer world system adds depth, strategy, and realism.

### Surface Layer
- Contains forests, lakes, grasslands, farms, houses, stockpiles  
- Workers gather **wood**, construct buildings, and path normally  
- **Mineshaft buildings** allow access to the lower layer  

### Underground Layer
- Only accessible through mineshafts  
- Contains:  
  - Gold veins  
  - Stone deposits  
  - Caverns & tunnels  
- Independent tile grid (same dimensions or custom size)  
- Different noise & generation rules  

### Mineshaft Building
- Connects a **surface tile** to an **underground tile**  
- Workers transition layers when entering/exiting  
- Functions as a chokepoint for stone/gold extraction  

### Underground Generation
- Begins as solid rock  
- Caverns carved using cellular automata or noise  
- Veins placed in clusters using BFS/DFS flood-fill logic  
- Entry chamber created under each surface mineshaft  

### Worker Layer Switching
Workers track:

### Docker instructions

# 1. Build containers
docker compose build

# 2. Start everything
docker compose up -d

# 3. Install composer deps
docker compose exec app composer install

# 4. Run migrations
docker compose exec app php artisan migrate

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

📅 Planned Features (Roadmap) 🌱 Gameplay

- Temperature system & seasons
- Hunting & wildlife
- Illness + medicine
- Bandit raids
- Diplomacy/reputation system

⚙️ Systems

- Save/load multiple worlds
- Deeper colonist AI (psych traits, work priorities RimWorld-style)
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

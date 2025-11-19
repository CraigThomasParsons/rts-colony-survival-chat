# RTS Colony – Permanent Project Context

PROJECT: Medieval / RTS Colony Survival Game  
TECH STACK:
- Backend: PHP 8+ (Laravel Framework)  
- Frontend: Livewire  
- Database: MySQL  
- Optional: Redis (future real-time sync), WebSockets  
- Tooling: GitHub Connector (ChatGPT reads repo + produces git patches)  

CORE GAME FEATURES:
- Procedural map generation (noise-based, scatter resource clusters)
- Resource nodes: wood, stone, gold
- Persistent Tiles + Map + ResourceNode models  
- Workers:
    - Inventory system
    - Harvesting cycles (carry capacity, travel → harvest → return → dropoff)
    - Pathfinding (A*, weighted terrain, diagonal optional)
    - Fatigue, state machine, job priorities (future)
- Buildings:
    - Construction progress, required resources, worker participation  
- World State:
    - Fully persisted in MySQL
    - Simulation runs via Laravel service/tick loop
- Multiplayer (eventual):
    - Shared persistent world
    - Cooperative colony play  
- Future expansions:
    - Underground map layers
    - Road auto-routing
    - Rust TUI client
    - Selenium / AI players for testing

AI DEVELOPMENT RULES (IMPORTANT):
1. Always produce unified git patches (.diff) for modifications unless user asks for full files.
2. When creating a new file, always output the entire file.
3. Follow Laravel best practices:
    - Models in app/Models
    - Services in app/Services
    - Livewire components in app/Http/Livewire
    - Console Commands in app/Console/Commands
4. Maintain compatibility with existing project structure at all times.
5. Provide clean code with:
    - PHPDoc blocks
    - Readable functions
    - Clear naming conventions
6. Before major refactors or architecture changes, ask clarifying questions.
7. Assume the world state must always remain persistent and deterministic.

GIT WORKFLOW RULES:
- ChatGPT generates feature-specific patches.
- User applies patches using:
    git apply patch.diff
- Feature branches follow:
    feat/*
    fix/*
    refactor/*
    docs/*

GOAL:
Maintain a consistent development environment where ChatGPT acts as an AI co-developer for the RTS Colony Survival game.

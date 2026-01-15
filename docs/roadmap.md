# Project Roadmap

This roadmap outlines the planned progression of development for the RTS Colony Simulation Game. It is separated into **short-term**, **mid-term**, and **long-term** milestones to guide development and Codex integration.

---

## 1. Short-Term Goals (Immediate Development)
These tasks focus on strengthening the foundational systems.

### **1.1 Worker System Enhancements**
- Add fatigue system (energy drain, resting behavior)
- Add worker selection UI
- Improve pathfinding (basic avoidance, queueing)
- Implement per-worker inventory
- Add worker progress indicators

### **1.2 Resource System Improvements**
- Improve resource node clustering
- Add depletion logic for nodes
- Add stockpile mechanics

### **1.3 UI/UX Polishing**
- Tile hover tooltips
- Display worker info on click
- Add resource counters to HUD

### **1.4 Map and Database Stability**
- Ensure map loads perfectly across sessions
- Improve map generation speed

---

## 2. Mid-Term Goals (Core Features)
These systems elevate the game from prototype to playable alpha.

### **2.1 Building System**
- Building placement via UI
- Construction tasks handled by workers
- Building costs (stone/wood/gold)
- Buildings as supply depots, storage, or functional structures

### **2.2 Enhanced Worker AI**
- Global task manager for multi-worker coordination
- Priority queues (e.g., harvesting > building > delivering)
- Idle behavior and signals

### **2.3 Improved Pathfinding**
- A* or Jump Point Search
- Avoiding collisions with static obstacles

### **2.4 Game Loop Optimization**
- Batch updates
- Make tick engine scalable for multiple players

### **2.5 Persistence & Multiplayer Foundations**
- User accounts / sessions
- Shared map instance
- Multi-user Livewire system handling concurrency

---

## 3. Long-Term Goals (Advanced Features)
Deep gameplay systems, content expansion, and quality-of-life improvements.

### **3.1 Advanced AI Systems**
- Faction AI
- Enemy raids
- Wildlife harvesting/interaction

### **3.2 Environment & World Systems**
- Weather effects
- Seasons system

### **3.3 Economy & Automation**
- Production chains
- Automated worker roles
- Trade routes

### **3.4 UI/UX Overhaul**
- Advanced game UI
- Mini-map
- Tracking population metrics
- Debug overlays

### **3.5 Performance & Scaling**
- Procedural mega-maps
- Client-side rendering improvements
- WebSocket realtime sync instead of Livewire

---

## 4. Release Stages

### **Prototype**
- Map generation
- Workers gathering and moving
- Basic UI

### **Alpha**
- Buildings
- Advanced worker AI
- Stable persistence

### **Beta**
- Multiplayer
- Advanced world systems

### **1.0 Release**
- Polished UI
- Large maps
- Full gameplay loop

---

## 5. Codex Instructions for Roadmap
When implementing roadmap features:
- Prioritize short-term tasks first
- Use `docs/architecture.md` for system reference
- Output unified diff patches
- Ensure Laravel/Livewire separation of concerns
- Keep AI logic inside services

End of file.


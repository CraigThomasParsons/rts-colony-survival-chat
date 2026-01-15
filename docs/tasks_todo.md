# Project TODO Tasks

This file lists actionable development tasks for the RTS Colony Simulation Game. Tasks are grouped by system and priority. Use this file with Codex to implement features incrementally.

---

## 1. High Priority Tasks (Do These First)
### **1.1 Worker System**
- [ ] Implement worker fatigue system
- [ ] Add rest behavior when fatigue is low
- [ ] Add simple pathfinding improvements
- [ ] Create worker selection UI element
- [ ] Show worker info panel on selection
- [ ] Implement per-worker inventory handling

### **1.2 Resource System**
- [ ] Finalize resource node clustering algorithm
- [ ] Add node depletion + removal on 0 resources
- [ ] Add stockpile entity and deposit logic

### **1.3 Map/State Persistence**
- [ ] Ensure map loads consistently from DB
- [ ] Improve tile querying performance

---

## 2. Medium Priority Tasks
### **2.1 Building System**
- [ ] Create building model + migration
- [ ] UI for placing buildings
- [ ] Construction tasks for workers
- [ ] Resource cost verification

### **2.2 Worker AI & Behavior**
- [ ] Implement global task scheduler
- [ ] Add priority system
- [ ] Add fallback tasks (idle, roam)
- [ ] Add failure/retry mechanisms

### **2.3 Game Tick Improvements**
- [ ] Add tick throttling
- [ ] Convert heavy operations to batched updates

---

## 3. Low Priority / Long-Term Tasks
### **3.1 Advanced Pathfinding**
- [ ] Replace naive pathfinding with A*
- [ ] Add obstacle awareness

### **3.2 UI / UX Enhancements**
- [ ] Visual worker movement animation
- [ ] Tooltip hover displays
- [ ] Minimap view
- [ ] Resource counters on HUD

### **3.3 Multiplayer Foundations**
- [ ] Add user sessions
- [ ] Sync actions between users
- [ ] Shared map instance

---

## 4. Maintenance & Cleanup
- [ ] Refactor Livewire components to keep logic in Services
- [ ] Add unit tests for worker logic
- [ ] Add integration tests for map generation
- [ ] Add seeders for test game state

---

## 5. Codex Instructions
When implementing TODO tasks:
- Always output unified diff patch format
- Reference `docs/architecture.md` and `docs/chatgpt_full_history.md`
- Keep logic inside services when possible
- Validate data changes against existing models
- Keep Livewire lean (UI logic only)

End of file.


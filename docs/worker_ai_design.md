# Worker AI Design Document

This file describes the behavior architecture and logic flow for worker units in the RTS Colony Simulation Game. It is intended to guide implementation and provide Codex with clear behavioral rules.

---

## 1. Worker Overview
Workers are autonomous agents responsible for:
- Moving around the grid
- Gathering resources
- Constructing buildings (future)
- Delivering materials to stockpiles
- Resting when fatigued

Workers operate on a **Finite State Machine (FSM)** model with defined states and transitions.

---

## 2. Worker States
### **2.1 Idle**
Default state when a worker has no task.
- Look for tasks
- Move to nearest resource node
- If none: wander or stay idle

### **2.2 Moving**
Worker is traveling toward a target (resource, building, stockpile).
- One-tile movement per tick
- Basic pathfinding to avoid obstacles

### **2.3 Harvesting**
Worker is extracting resources.
- Add resources to inventory
- Reduce node quantity
- If node depleted: auto-select next node
- If inventory full: transition to `Delivering`

### **2.4 Delivering**
Worker returns resources to a stockpile.
- Path to nearest stockpile
- Drop resources
- Reset inventory

### **2.5 Resting**
Triggered by fatigue.
- Worker remains stationary
- Fatigue regenerates per tick
- When above threshold → return to Idle

---

## 3. Behavior Logic
### **3.1 Task Search Algorithm (Idle State)**
1. Find nearest resource node
2. Check if node is not depleted
3. Check if another worker is not already assigned (optional)
4. Set state to `Moving` with node position

---

### **3.2 Movement Logic (Moving State)**
- Compute path toward target
- Move one tile per tick
- If blocked: try alternative tile
- Upon arrival: change state to task-specific state

---

### **3.3 Harvesting Logic (Harvesting State)**
```
harvest_amount = min(node.quantity, worker.harvest_rate)
worker.inventory.add(resource_type, harvest_amount)
node.quantity -= harvest_amount
```

Trigger conditions:
- If node.quantity == 0 → node removal and Idle state
- If inventory.full → Delivering state

---

### **3.4 Delivery Logic (Delivering State)**
- Navigate to stockpile
- Transfer inventory to global storage
- Clear inventory
- Return to Idle

---

### **3.5 Fatigue System**
Workers have:
```
fatigue_max
fatigue_level
fatigue_threshold (start resting)
recharge_rate
```

Rules:
- Every action increases fatigue
- When fatigue_level < fatigue_threshold → Resting
- During rest, regenerate fatigue

---

## 4. Worker Data Model
### **4.1 Database Columns**
```
id
x
y
state
fatigue_level
inventory_id
task_target_x
task_target_y
current_task_type
```

### **4.2 Inventory Model**
```
id
worker_id
wood
stone
gold
capacity
```

---

## 5. Pathfinding System
### **5.1 Current Version (Simple)**
- Step toward target tile
- Avoid blocked tiles

### **5.2 Improved Version (Future)**
- A* implementation in service
- Path caching
- Obstacle avoidance

---

## 6. Multi-Worker Coordination
### **6.1 Task Assignment Rules**
- Prevent overcrowding on nodes
- First-come-first-serve system
- Optional: assign workers based on distance

### **6.2 Avoidance Behavior**
- Don’t step onto a tile another worker occupies
- Slight detour movement

---

## 7. Worker AI Loop (Per Tick)
```
switch(state):
  case Idle:
    find_task()
  case Moving:
    move_one_step()
  case Harvesting:
    harvest_tick()
  case Delivering:
    deliver_tick()
  case Resting:
    restore_fatigue()
```

---

## 8. Codex Implementation Notes
- Encapsulate all AI behavior inside `WorkerService`
- Worker states are enum-like constants
- Use dedicated methods:
  - `performIdle()`
  - `performMove()`
  - `performHarvest()`
  - `performDeliver()`
  - `performRest()`
- Avoid putting any AI logic inside Livewire Components

End of file.


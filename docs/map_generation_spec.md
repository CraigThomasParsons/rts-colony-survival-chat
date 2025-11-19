# Map Generation Specification

This document describes the full specification for the procedural map generator used in your RTS Colony Simulation Game. It details terrain creation, resource distribution, clustering algorithms, and database storage format.

---

## 1. Overview
The map system generates a grid-based world containing:
- Terrain tiles
- Resource nodes
- Spawn areas
- Optional obstacles

Maps are fully persisted in the database, enabling session reloads and multi-user support.

---

## 2. Map Structure
A map consists of:
- **Width (X)** and **Height (Y)** dimensions
- **Tiles** stored individually
- Optional metadata (seed, biome, difficulty)

### **2.1 Tile Attributes**
```
id
map_id
x
y
terrain_type (grass, water, dirt, stone, forest)
has_resource (bool)
resource_node_id (nullable)
walkable (bool)
```

### **2.2 Resource Nodes**
```
id
tile_id
resource_type (wood, stone, gold)
quantity
cluster_id (nullable)
```

---

## 3. Generation Pipeline
Map generation uses multiple passes to progressively refine detail.

### **3.1 Step 1: Initialize Grid**
- Create `Map` model
- Create X * Y `Tile` entries
- Default terrain: grass

### **3.2 Step 2: Terrain Noise**
Apply noise or rule-based methods:
- Perlin/Simplex noise (optional)
- Manual region carving
- Rivers, lakes, mountains (future)

### **3.3 Step 3: Resource Allocation**
Resource nodes placed in dense clusters.

Resources:
- **Wood**: forest clusters
- **Stone**: rock clusters near mountains
- **Gold**: rare clusters

---

## 4. Resource Clustering Algorithm
Resource nodes are placed in clusters to create natural-looking deposits.

### **4.1 Algorithm**
1. Choose random cluster centers (3–7 per resource type)
2. For each cluster center:
   - Choose cluster size (5–20 nodes)
   - Spread nodes using radial or random-walk placement
   - Enforce tile bounds
3. Prevent overlapping different resource types

### **4.2 Sample Code Logic**
```
foreach cluster_center:
  for i in 1..cluster_size:
    dx = random(-radius, radius)
    dy = random(-radius, radius)
    tile = getTile(center.x + dx, center.y + dy)
    if tile valid and not occupied:
        create resource node
```

---

## 5. Saving to Database
### **5.1 Models Created**
- `Map`
- `Tile`
- `ResourceNode`

### **5.2 Data Integrity Rules**
- Each tile may only have **one** resource node
- Resource nodes must refer to a **valid tile**
- Tile walkability may depend on resource type

---

## 6. Loading & Rendering
### **6.1 Loading Process**
1. Load map
2. Fetch all tiles
3. Fetch resource nodes
4. Reconstruct grid in memory
5. Feed Livewire front-end

### **6.2 Rendering on Frontend**
- Tiles rendered as a grid of divs
- Colors or icons for terrain
- Overlays for resource nodes
- Workers and buildings placed on top layers

---

## 7. Future Improvements
### **7.1 Biomes**
- Desert, Snow, Jungle maps
- Modify resource probabilities

### **7.2 Obstacles**
- Trees, rocks, cliffs as separate obstacles
- Affect pathfinding

### **7.3 Advanced Noise**
- Multi-octave Perlin noise
- Climate layers (temperature, humidity)

### **7.4 Large Maps**
- Lazy loading for massive grids
- Database chunk streaming

---

## 8. Codex Integration Notes
When Codex modifies the map system:
- Keep map logic in `MapGenerator` service
- Avoid heavy computations in Livewire components
- Keep database writes batched where possible
- Use unified diff format for code updates
- Reference `docs/architecture.md` and `docs/chatgpt_full_history.md` for context

End of file.


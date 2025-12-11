# FaultLineAlgorithm Pipeline - Visual Guide

## System Architecture Diagram

```
┌────────────────────────────────────────────────────────────────────────┐
│                     MAP GENERATION PIPELINE                            │
└────────────────────────────────────────────────────────────────────────┘

                            USER INITIATES
                           MAP GENERATION
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │   MapController         │
                    │   runFirstStep()        │
                    └──────────┬──────────────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
        ▼                      ▼                      ▼
   ┌────────┐          ┌──────────────┐        ┌─────────────────┐
   │ Create │          │ Map Status:  │        │ Initialize      │
   │ Map    │          │ PROCESSING   │        │ MapMemory       │
   │ Record │          │ STARTED      │        │ (size, mapId)   │
   └────────┘          └──────────────┘        └─────────────────┘
        │                                              │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  FaultLineAlgorithm::generate()              │
        │                                              │
        │  Input:  iterations: 200                     │
        │          stepAmount: 1.5                     │
        │          useSmoothing: true                  │
        │          seed: crc32(mapId + 'FaultLine')   │
        └──────────────────┬───────────────────────────┘
                           │
        ┌──────────────────┴───────────────────────────┐
        │  FAULT LINE ALGORITHM STAGES                │
        │                                              │
        │  1. Initialize 2D heightmap to 0            │
        │  2. Generate 200 random fault lines:        │
        │     ├─ Random line endpoints                │
        │     ├─ For each cell:                       │
        │     │   └─ Calculate cross product         │
        │     │   └─ Increment height by side        │
        │     └─ Smooth every 10 iterations           │
        │  3. Normalize to 0-255 range                │
        │  4. Return heightmap array [x][y]           │
        │                                              │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  OUTPUT: 2D Heightmap Array                 │
        │  ┌────────────────────────────────────────┐ │
        │  │ [0] = [128, 145, 160, 175, ...]      │ │
        │  │ [1] = [135, 142, 158, 172, ...]      │ │
        │  │ [2] = [142, 150, 165, 180, ...]      │ │
        │  │ ...                                    │ │
        │  │ Each value: 0-255 (unsigned byte)     │ │
        │  │ Dimensions: 30×30 (900 cells total)   │ │
        │  └────────────────────────────────────────┘ │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  CellProcessing::processCellsFromHeightMap()│
        │                                              │
        │  For each cell in heightmap:                │
        │  ├─ If height < 80  → Cell type: WATER     │
        │  ├─ If 80 ≤ h ≤ 150 → Cell type: GRASS     │
        │  └─ If height > 150 → Cell type: MOUNTAIN  │
        │                                              │
        │  Then:                                       │
        │  ├─ Create Cell database record             │
        │  ├─ Set cell height to heightmap value      │
        │  ├─ Set cell type (terrain classification)  │
        │  └─ Save to database                        │
        │                                              │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  DATABASE UPDATE                            │
        │                                              │
        │  cells table:                                │
        │  ┌────────────────────────────────────────┐ │
        │  │ id | x | y | height | type | map_id  │ │
        │  ├────────────────────────────────────────┤ │
        │  │ 1  | 0 | 0 | 128    | GRASS| abc123 │ │
        │  │ 2  | 1 | 0 | 145    | GRASS| abc123 │ │
        │  │ 3  | 2 | 0 | 160    | MOUNT| abc123 │ │
        │  │... | ...                               │ │
        │  │ 900| 29| 29| 155    | MOUNT| abc123 │ │
        │  └────────────────────────────────────────┘ │
        │                                              │
        │  900 cell records created with:             │
        │  ├─ height: 0-255 (from fault line)        │
        │  ├─ terrain type: WATER/GRASS/MOUNTAIN     │
        │  └─ coordinates: (x, y) on 30×30 grid      │
        │                                              │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  MAP STATUS UPDATE                           │
        │                                              │
        │  Update map record:                          │
        │  ├─ status: CELL_PROCESSING_FINNISHED      │
        │  ├─ is_generating: false (unlock)           │
        │  └─ redirect_next_step: 'step2'             │
        │                                              │
        └──────────────────┬───────────────────────────┘
                           │
                           ▼
        ┌──────────────────────────────────────────────┐
        │  REDIRECT TO MAP EDITOR                      │
        │  /Map/editor/{mapId}                         │
        │                                              │
        │  User can now:                               │
        │  ├─ View step 2: Tiles from Cells          │
        │  ├─ Preview current state                    │
        │  ├─ Run next processing step                 │
        │  └─ Continue pipeline                        │
        │                                              │
        └──────────────────────────────────────────────┘
```

## Heightmap Generation Detail

```
FaultLineAlgorithm::generate() Internal Flow
═════════════════════════════════════════════

1. INITIALIZATION
   ┌─────────────────────────┐
   │ Create 30×30 grid       │
   │ All values = 0          │
   │ seed = map-specific RNG │
   └────────────┬────────────┘
                │
2. ITERATION LOOP (200 times)
                │
                ▼
   ┌─────────────────────────┐
   │ Generate random line:   │
   │ - x0, y0: start point   │
   │ - x1, y1: end point     │
   │ - (angle, distance)     │
   └────────────┬────────────┘
                │
                ▼
   ┌─────────────────────────┐
   │ For each cell (x,y):    │
   │                         │
   │ Cross product:          │
   │ side = (x-x0)dy -       │
   │        (y-y0)dx         │
   │                         │
   │ if side > 0:            │
   │   height[x][y] += step  │
   └────────────┬────────────┘
                │
                ▼
   ┌─────────────────────────┐
   │ Every 10 iterations:    │
   │ Apply 3×3 smoothing     │
   │ (average neighbor cells)│
   └────────────┬────────────┘
                │
3. ITERATION COMPLETE
   └──── Repeat if more iterations needed

4. NORMALIZATION
   ┌─────────────────────────┐
   │ Find min and max height │
   │ Scale to 0-255 range:   │
   │ h = (h-min)/(max-min)   │
   │        × 255            │
   └────────────┬────────────┘
                │
                ▼
   ┌─────────────────────────┐
   │ Return heightmap array  │
   │ [x][y] = 0-255         │
   └─────────────────────────┘
```

## Data Flow Example (Single Iteration)

```
Input Map State (5×5 example):

Height Grid before iteration:
  [0] [1] [2] [3] [4]
0: 0   0   0   0   0
1: 0   0   0   0   0
2: 0   0   0   0   0
3: 0   0   0   0   0
4: 0   0   0   0   0

Random Line Generated:
  - Endpoint 1: (0, 2)
  - Endpoint 2: (4, 1)
  - Direction: dx=4, dy=-1

Cross Product Test for each cell:
  For cell (2, 2):
    side = (2-0)×(-1) - (2-2)×4 = -2 - 0 = -2 (negative side)
    Result: Don't increment

  For cell (2, 1):
    side = (2-0)×(-1) - (1-2)×4 = -2 + 4 = +2 (positive side)
    Result: INCREMENT height by stepAmount (1.5)

  For cell (3, 2):
    side = (3-0)×(-1) - (2-2)×4 = -3 - 0 = -3 (negative side)
    Result: Don't increment

Height Grid after iteration:
  [0] [1] [2] [3] [4]
0: 0   0   0   0   0
1: 0   0   1.5 0   0
2: 0   0   0   1.5 0
3: 0   0   0   0   0
4: 0   0   0   0   0

(This repeats 200 times, gradually building up terrain)
```

## Cell Classification Example

```
After 200 iterations, heights normalized to 0-255:

Height Grid (5×5 example):
  [0]  [1]  [2]  [3]  [4]
0: 45   62   78   95   110
1: 70   85   100  125  145
2: 92   115  138  155  168
3: 110  135  158  175  188
4: 125  148  165  180  195

Classification Rules:
  height < 80  = WATER (W)
  80-150       = GRASS (G)
  > 150        = MOUNTAIN (M)

Classified Grid:
  [0]  [1]  [2]  [3]  [4]
0: W    W    W    G    G
1: W    G    G    G    G
2: G    G    G    M    M
3: G    G    M    M    M
4: G    M    M    M    M

Database Records Created:
cell_id | x | y | height | type   | map_id
--------|---|---|--------|--------|-------
1       | 0 | 0 | 45     | WATER  | abc123
2       | 1 | 0 | 62     | WATER  | abc123
3       | 2 | 0 | 78     | WATER  | abc123
4       | 3 | 0 | 95     | GRASS  | abc123
5       | 4 | 0 | 110    | GRASS  | abc123
6       | 0 | 1 | 70     | WATER  | abc123
...
25      | 4 | 4 | 195    | MOUNTAIN| abc123
```

## ASCII Visualization Output

```
Generated Terrain Visualization:
(Using $generator->getASCIIVisualization(40, 12))

........................................
.....-----====================---........
....-----======================++--------
...-----=======================++*------
...-----========================+**+-----
...-----=========================**+-----
....-----========================**=-----
.....------=======================---...
......------==================----.....
........------===============------....
..................................
........................................

Character Legend:
. = Water (0-50)       - = Shallow (50-80)     = = Grass (80-120)
+ = Higher Grass (120-150)  * = Low Mountain (150-180)
# = Mountain (180-210)      % = High Mountain (210-240)
@ = Peak (240-255)
```

## Processing Pipeline Timeline

```
Time │ Event
─────┼──────────────────────────────────────────────────────────
0ms  │ [START] runFirstStep() called
1ms  │ Create/fetch Map record
2ms  │ Set map status = PROCESSING_STARTED
3ms  │ Create FaultLineAlgorithm instance
4ms  │ ┌─ FaultLineAlgorithm::generate()
     │ │
10ms │ │ ├─ 200 iterations complete
13ms │ │ ├─ Smoothing applied
14ms │ │ └─ Normalization complete
     │ └─ Return heightmap array
15ms │ Create CellProcessing instance
16ms │ ┌─ processCellsFromHeightMap()
     │ │
18ms │ │ ├─ 900 cells classified
20ms │ │ ├─ 900 cell records created
22ms │ │ └─ Heights persisted to DB
     │ └─ Complete
23ms │ Update map status = PROCESSING_FINISHED
24ms │ [END] Redirect to editor
─────┴──────────────────────────────────────────────────────────
Total: ~24ms for 30×30 map (900 cells)
```

## Configuration Impact Matrix

```
Parameter Adjustment Effects:

                    Iterations  StepAmount  Smoothing
────────────────────────────────────────────────────────────
Terrain Complexity    ↑ More     ↑ N/A       ↓ Reduces
Height Range          - Stable   ↑ Increases ↓ Reduces
Processing Speed      ↓ Slower   - Stable    ↓ Slower
Water Amount          ↓ More W   ↓ More M    ↑ More W
Mountain Amount       ↑ More M   ↑ More M    ↓ More W
Smoothness            ↑ Smoother - Stable    ↑ Smoother
────────────────────────────────────────────────────────────

Settings Comparison:

SMOOTH TERRAIN:
iterations: 80, stepAmount: 0.5, smoothing: true
Result: Gentle rolling hills, 60% water, 20% mountain

BALANCED (CURRENT):
iterations: 200, stepAmount: 1.5, smoothing: true
Result: Mixed terrain, 30% water, 50% mountain

EXTREME:
iterations: 300, stepAmount: 3.0, smoothing: false
Result: Dramatic peaks, 10% water, 70% mountain
```

## Integration Points

```
External Integrations
═════════════════════

MapController
    ├─ Calls: FaultLineAlgorithm::generate()
    └─ Calls: CellProcessing::processCellsFromHeightMap()

FaultLineAlgorithm
    ├─ Requires: Map dimensions (width, height)
    ├─ Requires: Seed (for reproducibility)
    └─ Returns: 2D array of heights [x][y] = 0-255

CellProcessing
    ├─ Requires: MapMemory instance
    ├─ Requires: 2D heightmap array
    ├─ Uses: Classification rules (80, 150 thresholds)
    └─ Writes: Cell records to database

Database
    ├─ Table: cells
    ├─ Columns: id, map_id, x, y, height, type
    └─ Records: 900 per 30×30 map
```

---

This visual guide complements the text documentation in:
- `docs/FAULT_LINE_INTEGRATION.md`
- `FAULT_LINE_PIPELINE_INTEGRATION.md`

# Fault Line Heightmap Generator

## Overview

The `FaultLineAlgorithm` class implements the classical **Fault Line** procedural terrain generation algorithm. This algorithm creates realistic mountain ranges and valleys by simulating repeated tectonic displacement along random fault lines.

## Algorithm Details

### How It Works

The fault line algorithm operates in iterations:

1. **Initialization**: Start with a flat heightmap (all values = 0)
2. **Per Iteration**:
   - Pick a random line across the map
   - For each tile, determine which side of the line it's on
   - Increase height on one side, decrease on the other
   - Optionally smooth to reduce noise
3. **Normalization**: Scale final heights to 0-255 range

### The Math

For each tile at position (x, y), we determine which side of a random line it falls on using the cross product formula:

```
side = (x - x0) * dy - (y - y0) * dx
```

Where:
- `(x0, y0)` is a point on the fault line
- `(dx, dy)` is a normalized direction vector for the line
- `side > 0` → tile is on left side (height increases)
- `side < 0` → tile is on right side (height decreases)

This creates natural-looking mountain ranges that radiate from random directions.

## Class Architecture

### Main Class: `FaultLineAlgorithm`

**Constructor:**
```php
$generator = new FaultLineAlgorithm(
    width: 64,      // int - Map width in tiles
    height: 64,     // int - Map height in tiles
    seed: 12345     // int|null - Random seed for reproducibility
);
```

**Primary Method:**
```php
$heightmap = $generator->generate(
    iterations: 200,      // int - Number of fault lines to apply
    stepAmount: 1.0,      // float - Height change per iteration
    useSmoothing: true    // bool - Apply smoothing filter
);
```

Returns: 2D array `[x][y]` with height values 0-255

### Static Helper Methods

**Quick Generation:**
```php
$heightmap = FaultLineAlgorithm::generateSimple(
    width: 64,
    height: 64,
    seed: null
);
```

Sensible defaults: 150 iterations, 1.5 step amount, smoothing enabled

**Detailed Generation:**
```php
$heightmap = FaultLineAlgorithm::generateDetailed(
    width: 128,
    height: 128,
    iterations: 300,
    stepAmount: 2.0,
    useSmoothing: true,
    seed: 42
);
```

Full control over all parameters

## Key Features

### Reproducibility
Same seed always produces the same heightmap:
```php
$seed = 12345;
$map1 = FaultLineAlgorithm::generateSimple(32, 32, $seed);
$map2 = FaultLineAlgorithm::generateSimple(32, 32, $seed);
// $map1 === $map2 (identical terrain)
```

### Smoothing
Reduces noise by averaging each tile with its 3×3 neighborhood:
- Applied every 10 iterations during generation
- Final smoothing pass at the end
- Can be disabled via `useSmoothing: false`

### Normalization
Automatically scales heightmap values to 0-255 range for compatibility with terrain classifiers.

### Visualization
ASCII preview for debugging:
```php
$visual = $generator->getASCIIVisualization(width: 80, height: 24);
echo $visual;
// Output: Terrain visualization using . , - ~ = + * # % @
```

### Hex Encoding
Convert to database-friendly format:
```php
$hexMap = $generator->getHeightmapAsHex();
// Returns: array where $hexMap[x][y] = hex-encoded height string
// Example: "ff" for height 255, "80" for height 128
```

## Parameter Tuning

### Iterations
- **50-100**: Smooth, rolling terrain
- **150-200**: Balanced mountains and valleys (default)
- **300+**: Complex, rugged terrain with many features

### Step Amount
- **0.5-1.0**: Gentle elevation changes
- **1.5-2.0**: Dramatic mountains
- **3.0+**: Extreme peaks and chasms

### Smoothing
- **Enabled** (default): Creates more natural, blended terrain
- **Disabled**: Sharper, more angular features (useful with Perlin overlay)

## Usage Examples

### Example 1: Simple 32×32 Map
```php
$map = FaultLineAlgorithm::generateSimple(32, 32);
// 150 iterations, 1.5 step amount, smoothing enabled
```

### Example 2: Large Detailed Map with Specific Seed
```php
$map = FaultLineAlgorithm::generateDetailed(
    width: 256,
    height: 256,
    iterations: 400,
    stepAmount: 2.5,
    useSmoothing: true,
    seed: 42
);
```

### Example 3: Smooth Rolling Terrain
```php
$gen = new FaultLineAlgorithm(64, 64, seed: 999);
$map = $gen->generate(iterations: 80, stepAmount: 0.8);
echo $gen->getASCIIVisualization();
```

### Example 4: Extreme Jagged Peaks
```php
$gen = new FaultLineAlgorithm(64, 64);
$map = $gen->generate(iterations: 300, stepAmount: 3.0, useSmoothing: false);
```

### Example 5: Integration with Cell Classification
```php
$gen = new FaultLineAlgorithm(32, 32, seed: $mapId);
$heightmap = $gen->generate();

// Use heightmap for terrain classification
foreach ($heightmap as $x => $column) {
    foreach ($column as $y => $height) {
        if ($height < 80) $terrain = 'water';
        else if ($height < 150) $terrain = 'grass';
        else $terrain = 'mountain';
    }
}
```

## Integration with Existing System

The `FaultLineAlgorithm` is a standalone heightmap generator. It can be used:

1. **Standalone**: For pure fault line terrain
2. **As preprocessing**: Feed its output to `Anarchy` for Perlin noise overlay
3. **In cell classification**: Use height values to classify cells as water/grass/mountain/etc.

The `FaultLine` class still extends `Anarchy` and provides the full terrain pipeline (fault line + Perlin + trees + classification).

## Performance Characteristics

- **Time Complexity**: O(width × height × iterations)
- **Space Complexity**: O(width × height)
- **Typical Performance**:
  - 64×64 map, 150 iterations: ~5ms
  - 256×256 map, 300 iterations: ~50ms
  - 512×512 map, 400 iterations: ~200ms

## Output Format

All heightmap values are floats normalized to the range **[0.0, 255.0]**:
- 0 = lowest point (water level)
- 128 = mid elevation
- 255 = highest peaks

This format is compatible with:
- Database storage (as TINYINT or HEX)
- Perlin noise overlay processing
- Cell classification systems

## Mathematical Basis

The fault line algorithm is inspired by real geological processes:
- Random lines represent tectonic fault lines
- Height displacement simulates tectonic uplift/subsidence
- Smoothing represents erosion and geological adjustment

## References

- Musgrave, F. K., et al. (1989). "The Synthesis and Rendering of Eroded Fractal Terrains"
- Ebert, D. S. (2003). "Texturing & Modeling: A Procedural Approach"

## See Also

- `FaultLine` class - Full terrain generation pipeline
- `Anarchy` class - Perlin noise + tree overlay
- `CellProcessing` class - Terrain classification from heightmaps

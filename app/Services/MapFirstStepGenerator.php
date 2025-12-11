<?php

namespace App\Services;

use App\Models\Map;
use App\Models\MapStatus;
use App\Models\Cell;
use App\Models\CellType;
use App\Helpers\MapGenerators\FaultLineAlgorithm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * MapFirstStepGenerator
 *
 * Simplified service: generates heightmap and persists cells directly to MySQL.
 *
 * - Uses FaultLineAlgorithm for terrain generation
 * - Creates Cell records via Eloquent
 * - Creates Tile records from Cell data
 */
class MapFirstStepGenerator
{
    private const DEFAULT_HEIGHT_MAP_SIZE = 30;
    
    // These will be calculated from actual heightmap statistics
    private float $waterThreshold = 0;
    private float $mountainThreshold = 0;

    /**
     * Initialize cells
     * Execute the first step: generate cells with height and cellType
     * and persist cells and tiles to MySQL.
     *
     * @param string $mapId
     * @return void
     *
     * @throws \Exception
     */
    public function generate(string $mapId): void
    {
        $size = self::DEFAULT_HEIGHT_MAP_SIZE;

        // Get or create map record
        $map = Map::find($mapId);
        if (!$map) {
            $map = new Map();
            $map->id = $mapId;
            $map->name = 'Map ' . date('Ymd-His');
            $map->description = 'Auto-created (fallback)';
            $map->coordinateX = $size;
            $map->coordinateY = $size;
            $map->save();
        }

        // Mark as generating
        $map->is_generating = true;
        $map->state = 'Cell_Process_Started';
        $map->save();

        // Generate heightmap seed from mapId (deterministic)
        $seed = crc32($mapId . 'FaultLine');

        // Generate heightmap
        $heightmapGenerator = new FaultLineAlgorithm($size, $size, $seed);
        $heightmap = $heightmapGenerator->generate(
            iterations: 200,
            stepAmount: 1.5,
            useSmoothing: true
        );

        // Calculate statistical thresholds from the heightmap
        $this->calculateThresholds($heightmap);

        try {
            DB::transaction(function () use ($map, $mapId, $heightmap, &$cellCount) {
                // Delete existing cells and tiles for this map (idempotent)
                Cell::where('map_id', $mapId)->delete();
                DB::table('tile')->where('map_id', '=', $mapId)->delete();

                // Persist cells to MySQL
                foreach ($heightmap as $y => $row) {
                    foreach ($row as $x => $height) {
                        $roundedHeight = (int) round($height);
                        $cellTypeId = $this->determineCellType($roundedHeight);

                        // Create cell with appropriate cellType based on height
                        Cell::create([
                            'id' => (string) Str::uuid(),
                            'map_id' => $mapId,
                            'coordinateX' => $x,
                            'coordinateY' => $y,
                            'height' => $roundedHeight,
                            'name' => 'Cell',
                            'description' => "Height: " . $roundedHeight,
                            'cellType_id' => $cellTypeId,
                        ]);

                        $cellCount++;
                    }
                }

                // Create tiles for all cells
                $this->createTilesFromCells($mapId);

                Log::info("MapFirstStepGenerator: Transaction committed for map {$mapId}.");

            });
        } catch (\Throwable $e) {
            Log::error("MapFirstStepGenerator: Failed to generate map {$mapId}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

        // Mark generation complete
        $map->is_generating = false;
        $map->state = 'Cell_Process_Completed';
        $map->save();
    }

    /**
     * Determine cellType based on height value
     *
     * @param int $height
     * @return int cellType_id
     */
    private function determineCellType(int $height): int
    {
        if ($height < $this->waterThreshold) {
            return $this->getCellTypeIdByName(CellType::WATER);
        } elseif ($height >= $this->mountainThreshold) {
            return $this->getCellTypeIdByName(CellType::MOUNTAIN);
        } else {
            return $this->getCellTypeIdByName(CellType::BASIC_LAND);
        }
    }

    /**
     * Calculate statistical thresholds from the heightmap
     *
     * - Water threshold: 20% of average height
     * - Mountain threshold: 90th percentile of heights
     *
     * @param array $heightmap
     * @return void
     */
    private function calculateThresholds(array $heightmap): void
    {
        // Flatten heightmap to get all heights
        $allHeights = [];
        foreach ($heightmap as $row) {
            foreach ($row as $height) {
                $allHeights[] = (float) $height;
            }
        }

        // Calculate average
        $average = array_sum($allHeights) / count($allHeights);
        $this->waterThreshold = $average * 0.2;

        // Calculate 90th percentile for mountain threshold
        sort($allHeights);
        $percentileIndex = (int) (count($allHeights) * 0.9);
        $this->mountainThreshold = $allHeights[$percentileIndex];
    }

    /**
     * Get cellType ID by name, with caching for performance
     *
     * @param string $name
     * @return int
     */
    private function getCellTypeIdByName(string $name): int
    {
        static $cellTypeCache = [];

        if (!isset($cellTypeCache[$name])) {
            $cellType = CellType::firstWhere('name', $name);
            if ($cellType) {
                $cellTypeCache[$name] = $cellType->id;
            } else {
                // Fallback to default if cellType not found
                $cellTypeCache[$name] = CellType::DEFAULT_TYPE_ID;
            }
        }

        return $cellTypeCache[$name];
    }

    /**
     * Create tiles for all cells in the map using raw database insert
     *
     * @param string $mapId
     * @return void
     */
    private function createTilesFromCells(string $mapId): void
    {
        $cells = Cell::where('map_id', $mapId)->get();
        
        Log::info("Creating tiles for map {$mapId}", [
            'cell_count' => $cells->count(),
            'sample_cell_id' => $cells->first()?->id,
        ]);

        $tileCount = 0;
        $errorCount = 0;

        foreach ($cells as $cell) {
            try {
                // Validate cell has required data
                if (!$cell->id) {
                    Log::error("Cell missing ID", [
                        'map_id' => $mapId,
                        'coordinates' => "({$cell->coordinateX}, {$cell->coordinateY})",
                    ]);
                    $errorCount++;
                    continue;
                }

                // Use Eloquent model for proper exception handling
                \App\Models\Tile::create([
                    'map_id' => $mapId,
                    'cell_id' => $cell->id,
                    'coordinateX' => $cell->coordinateX,
                    'coordinateY' => $cell->coordinateY,
                    'mapCoordinateX' => $cell->coordinateX,
                    'mapCoordinateY' => $cell->coordinateY,
                    'name' => 'Tile',
                    'description' => "Tile at ({$cell->coordinateX}, {$cell->coordinateY})",
                    'tileType_id' => $cell->cellType_id,
                ]);
                
                $tileCount++;
            } catch (\Exception $e) {
                Log::error("Failed to create tile", [
                    'map_id' => $mapId,
                    'cell_id' => $cell->id,
                    'coordinates' => "({$cell->coordinateX}, {$cell->coordinateY})",
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errorCount++;
            }
        }

        Log::info("Tile creation complete", [
            'map_id' => $mapId,
            'tiles_created' => $tileCount,
            'errors' => $errorCount,
            'total_cells' => $cells->count(),
        ]);
    }
}


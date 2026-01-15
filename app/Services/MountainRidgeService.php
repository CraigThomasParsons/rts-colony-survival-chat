<?php
namespace App\Services;

use App\Models\Map;
use App\Models\Cell;
use App\Models\Tile as EloquentTile;
use App\Helpers\Processing\TileAdapter;
use App\Helpers\Processing\MountainProcessing;

/**
 * MountainRidgeService orchestrates Steps 4 and 5 of terrain generation
 * by adapting data from MySQL (Eloquent) to the legacy MountainProcessing
 * helper which expects a tile grid and a set of mountain cell locations.
 *
 * Two-pass approach:
 * - Pass 1 (foothills): cells with height >= mountainThresholdLow
 * - Pass 2 (peaks): cells with height >= mountainThresholdHigh
 * Both passes classify ridge tiles via neighbor analysis and persist
 * tileTypeId and tile_display_type to the tiles table.
 */
class MountainRidgeService
{
    /**
     * Tiny notes for maintainers:
     * - Coordinate indexing is [$x][$y] throughout to match legacy helper.
     * - Masks are simple boolean grids derived from cell heights.
     * - The two passes are independent; peak pass may overwrite prior
     *   foothill classifications where masks overlap.
     */
    /**
     * Execute ridge classification for a map using provided thresholds.
     */
    public function run(string $mapId, int $mountainThresholdLow, int $mountainThresholdHigh): void
    {
        $map = Map::findOrFail($mapId);

        // Build tiles grid as adapters
        $tilesGrid = $this->loadTilesGrid($mapId);

    // Load cells and create masks for both passes
    // NOTE: coordinate fields are coordinateX/coordinateY in schema
    $cells = Cell::where('map_id', $mapId)->get(['coordinateX', 'coordinateY', 'height']);

        $lowMask = [];
        $highMask = [];
        foreach ($cells as $cell) {
            $x = (int)$cell->coordinateX;
            $y = (int)$cell->coordinateY;
            if ($cell->height >= $mountainThresholdLow) {
                $lowMask[$x][$y] = true;
            }
            if ($cell->height >= $mountainThresholdHigh) {
                $highMask[$x][$y] = true;
            }
        }

        // Pass 1: foothills
        $this->classifyRidges($tilesGrid, $lowMask);

        // Pass 2: peaks (may overwrite/enhance classifications)
        $this->classifyRidges($tilesGrid, $highMask);
    }

    /**
     * Load all tiles for the map and build a [$x][$y] TileAdapter grid.
     */
    protected function loadTilesGrid(string $mapId): array
    {
        $tiles = EloquentTile::where('map_id', $mapId)->get();
        $grid = [];
        foreach ($tiles as $tile) {
            $adapter = TileAdapter::fromModel($tile);
            $grid[$adapter->x][$adapter->y] = $adapter;
        }
        return $grid;
    }

    /**
     * Perform neighbor-based ridge classification on a tiles grid using
     * a boolean mask of rocky coordinates.
     */
    protected function classifyRidges(array &$tilesGrid, array $mask): void
    {
        // Prepare MountainProcessing with adapters and mask
        $processor = (new MountainProcessing())->init();
        $processor->setTiles($tilesGrid);

        // Emulate mountainCells->addTileLocations() by synthesizing an array
        // of objects with that method to fill the $tileLocations structure.
        $mountainCells = [];
        foreach ($mask as $x => $col) {
            foreach ($col as $y => $_) {
                $mountainCells[$x][$y] = new class($x, $y) {
                    private $x; private $y;
                    public function __construct($x, $y){ $this->x=$x; $this->y=$y; }
                    public function addTileLocations(&$tileLocations){ $tileLocations[$this->x][$this->y] = true; }
                };
            }
        }
        $processor->setMountainCells($mountainCells);

        // Execute classification; MountainProcessing will mutate tileTypeId
        // and set tile display types on adapters, then call save().
        $processor->createRidges();
    }
}

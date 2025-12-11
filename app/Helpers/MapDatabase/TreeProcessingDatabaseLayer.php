<?php
namespace App\Helpers\MapDatabase;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\TileRepository;
use App\Helpers\Coordinates;

/**
 * Database layer for Tree Processing.
 *
 * Goal: Provide data-side helpers used by the TreeProcessing algorithm without
 * leaking DB-specific details into the algorithm itself.
 */
class TreeProcessingMapDatabaseLayer
{
    /**
     * For each cell, randomly pick one of its four tiles and convert it from
     * a tree tile to passable land. This creates “holes” so Conway’s Life
     * produces more interesting spreads in subsequent passes.
     *
     * Why: The life algorithm benefits from breaking up contiguous tree blocks.
     * This pre-step makes clusters less uniform.
     */
    public function holePuncher(string $mapId)
    {
        // Each cell maps to 4 tiles on the overall map grid using offsets (0/1, 0/1).
        $offsets = [
            new Coordinates(0, 0),
            new Coordinates(0, 1),
            new Coordinates(1, 1),
            new Coordinates(1, 0),
        ];

        // Fetch all cells for this map from the repository.
        $cells = MapRepository::findAllCells($mapId);
        if (!$cells) {
            // No cells means nothing to punch; return early.
            return $this;
        }

        // Iterate each cell, randomly select one of the 4 tiles, and flip it to land.
        foreach ($cells as $x => $col) {
            foreach ($col as $y => $cell) {
                // Randomly select one tile in the cell.
                $selected = $offsets[array_rand($offsets, 1)];

                // Compute absolute map tile coordinates: (2 * cellCoord) + tileOffset
                $mapX = ($cell->getX() * 2) + $selected->getXAxis();
                $mapY = ($cell->getY() * 2) + $selected->getYAxis();

                // Find the tile record by its map coordinates.
                $tile = TileRepository::findByMapCoordinates($mapId, $mapX, $mapY);
                if (!$tile) {
                    // If the repository returns null, skip gracefully.
                    continue;
                }

                // Flip the selected tile to passable land and save.
                // Note: Setting name + tileTypeId is sufficient for later processing.
                $tile->set('name', 'Passable Land');
                $tile->set('tileTypeId', 1);
                $tile->setCellId($cell->getId());
                $tile->save();
            }
        }

        return $this;
    }
}
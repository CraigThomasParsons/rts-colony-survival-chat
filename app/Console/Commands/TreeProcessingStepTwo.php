<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\MapHelper;
use App\Helpers\Processing\TreeProcessing;
use App\Models\MapStatus;
use Illuminate\Console\Command;

/**
 * Artisan command: map:3tree-step2
 *
 * Second step in tree processing with hole punching and orphan purging.
 * This is Step 3b in the map generation pipeline.
 */
class TreeProcessingStepTwo extends Command
{
    protected $signature = 'map:3tree-step2 {mapId : The Map ID to process}';
    protected $description = 'Run second tree processing algorithm (Step 3b)';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        
        $this->info("Starting tree processing step 2 for map {$mapId}...");
        
        $map = MapRepository::findFirst($mapId);
        
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        $mapRecord = MapRepository::findFirst($mapId);
        $cells = MapRepository::findAllCells($mapId);
        $tiles = MapRepository::findAllTiles($mapId);
        $treeCells = MapRepository::findAllTreeCells($mapId);

        // Require tree step 1 completion state before proceeding.
        if ($map->mapstatuses_id === null) {
            $this->error("Aborting: tree step 1 state missing for map {$mapId}.");
            return self::FAILURE;
        }

        // Check if cells exist (they might have been deleted by a concurrent map:1init)
        if ($cells === false || empty($cells)) {
            $this->error("No cells found for map {$mapId}. Map data may have been reset by another process.");
            $this->error("Please avoid clicking 'Generate Map' multiple times simultaneously.");
            return self::FAILURE;
        }

        $mapLoader = new MapHelper($mapRecord->id, $tiles, $cells);
        
        $this->info("Running hole puncher...");
        $mapLoader->holePuncher($mapId);

        // Tree creation started.
    $map->mapstatuses_id = MapStatus::firstWhere('name', MapStatus::TREE_2ND_COMPLETED)?->id;
    $map->state = MapStatus::TREE_2ND_COMPLETED;
    $map->save();

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);

        // Setting the amount of iterations to run when runJohnConwaysGameOfLife is called.
        $treeProcessing->setMapLoader($mapLoader)->setIterations(5);

        // Inverts the process. Life equals death.
        $treeProcessing->setBoolInvertSave(true);

        $this->info("Running Conway's Game of Life with 5 iterations (inverted)...");
        $treeProcessing->runJohnConwaysGameOfLife();

        // Purge Orphans will purge any tree tiles out on its own.
        $this->info("Purging orphan trees (3 passes: 5, 5, 7)...");
        $treeProcessing->purgeOrphans(5);
        $treeProcessing->purgeOrphans(5);
        $treeProcessing->purgeOrphans(7);

        $this->info("Assigning final tile types...");
        $tileCount = 0;
        
        foreach ($tiles as $something) {
            foreach ($something as $tile) {
                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Trees') {
                    $tile->name = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId = 29;
                    if (property_exists($tile, 'has_trees')) {
                        $tile->has_trees = true;
                    }
                } else if ($currentCell->name == 'Water') {
                    $tile->name = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId = 3;
                    if (property_exists($tile, 'has_trees')) {
                        $tile->has_trees = false;
                    }
                } else if ($currentCell->name == 'Impassable Rocks') {
                    $tile->name = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId = 2;
                    if (property_exists($tile, 'has_trees')) {
                        $tile->has_trees = false;
                    }
                }
                $tile->save();
                $tileCount++;
            }
        }
        
        $this->info("Processed {$tileCount} tiles.");
        $this->info("Tree processing step 2 completed for map {$mapId}.");
        
        return self::SUCCESS;
    }
}

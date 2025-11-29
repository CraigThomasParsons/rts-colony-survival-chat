<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\MapHelper;
use App\Helpers\Processing\TreeProcessing;
use App\Models\MapStatus;
use Illuminate\Console\Command;

/**
 * Artisan command: map:3tree-step3
 *
 * Final step in tree processing with minimal iterations and final orphan purge.
 * This is Step 3c in the map generation pipeline.
 */
class TreeProcessingStepThree extends Command
{
    protected $signature = 'map:3tree-step3 {mapId : The Map ID to process}';
    protected $description = 'Run final tree processing algorithm (Step 3c)';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        
        $this->info("Starting tree processing step 3 (final) for map {$mapId}...");
        
        $map = MapRepository::findFirst($mapId);
        
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        $mapRecord = MapRepository::findFirst($mapId);
        $tiles = MapRepository::findAllTiles($mapId);
        $treeCells = MapRepository::findAllTreeCells($mapId);

        $mapLoader = new MapHelper($mapRecord->id, $tiles, $treeCells);
        
        $this->info("Running hole puncher...");
        $mapLoader->holePuncher($mapId);

        // Tree creation started.
        $map->setState(MapStatus::TREE_3RD_STARTED);
        $map->save();

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);

        // Setting the amount of iterations to run when runJohnConwaysGameOfLife is called.
        $treeProcessing->setMapLoader($mapLoader)->setIterations(2);

        // Inverts the process. Life equals death.
        $treeProcessing->setBoolInvertSave(true);

        $this->info("Running Conway's Game of Life with 2 iterations (inverted)...");
        $treeProcessing->runJohnConwaysGameOfLife();

        $this->info("Final orphan purge (threshold: 7)...");
        $treeProcessing->purgeOrphans(7);

        $this->info("Assigning final tile types...");
        $cells = MapRepository::findAllCells($mapId);
        $tileCount = 0;
        
        foreach ($tiles as $something) {
            foreach ($something as $tile) {
                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Trees') {
                    $tile->name = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId = 29;
                } else if ($currentCell->name == 'Water') {
                    $tile->name = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId = 3;
                } else if ($currentCell->name == 'Impassable Rocks') {
                    $tile->name = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId = 2;
                }
                $tile->save();
                $tileCount++;
            }
        }
        
        $this->info("Processed {$tileCount} tiles.");
        $this->info("Tree processing step 3 (final) completed for map {$mapId}.");
        
        return self::SUCCESS;
    }
}

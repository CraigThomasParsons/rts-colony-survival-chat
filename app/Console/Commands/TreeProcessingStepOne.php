<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\MapHelper;
use App\Helpers\Processing\TreeProcessing;
use App\Models\MapStatus;
use Illuminate\Console\Command;

/**
 * Artisan command: map:3tree-step1
 *
 * First step in tree processing using Conway's Game of Life algorithm.
 * This is Step 3a in the map generation pipeline.
 */
class TreeProcessingStepOne extends Command
{
    protected $signature = 'map:3tree-step1 {mapId : The Map ID to process}';
    protected $description = 'Run first tree processing algorithm (Step 3a)';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        $size = 38; // DEFAULT_HEIGHT_MAP_SIZE
        
        $this->info("Starting tree processing step 1 for map {$mapId}...");
        
        $map = MapRepository::findFirst($mapId);
        
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        $tiles = MapRepository::findAllTiles($mapId);
        $allCells = MapRepository::findAllCells($mapId);

        // Ensure previous step (tiles processed) completed before starting tree step 1.
        if ($map->mapstatuses_id === null) {
            $this->error("Aborting: tile processing not marked completed yet for map {$mapId}.");
            return self::FAILURE;
        }

        // Check if cells exist (they might have been deleted by a concurrent map:1init)
        if ($allCells === false || empty($allCells)) {
            $this->error("No cells found for map {$mapId}. Map data may have been reset by another process.");
            $this->error("Please avoid clicking 'Generate Map' multiple times simultaneously.");
            return self::FAILURE;
        }

        // Tree creation started.
        $map->setState(MapStatus::TREE_FIRST_STEP);
        $map->set('nextStep', "treeStepSecond");
        $map->save();

        $mapRecord = MapRepository::findFirst($mapId);
        $mapLoader = new MapHelper($mapRecord->id, $tiles, $allCells);

        // Using this to process the tiles we need and start the work of randomizing tree tiles.
        $treeProcessing = new TreeProcessing($mapLoader);
        
        $this->info("Running Conway's Game of Life with 20 iterations...");
        $treeProcessing->setMapLoader($mapLoader)
            ->setIterations(20)
            ->runJohnConwaysGameOfLife();

        $treeCells = MapRepository::findAllTreeCells($mapId);

        // Invert what we just did.
        $this->info("Inverting tree/land tiles...");
        $tileCount = 0;
        
        foreach ($tiles as $something) {
            foreach ($something as $tile) {
                if ($tile->tileTypeId == 29) {
                    $tile->name = 'inner-Land';
                    $tile->description = 'Passable';
                    $tile->tileTypeId = 1;
                } else if ($tile->tileTypeId == 1) {
                    $tile->name = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId = 29;
                }
                $tile->save();
                $tileCount++;
            }
        }
        
        $this->info("Processed {$tileCount} tiles.");
        $this->info("Killing all trees in cells...");
        $mapLoader->killAllTreesInCell($mapId);

        $this->info("Tree processing step 1 completed for map {$mapId}.");
        
        return self::SUCCESS;
    }
}

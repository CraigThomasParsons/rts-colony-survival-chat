<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\MapHelper;
use App\Helpers\Processing\TreeProcessing;
use App\Models\MapStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    protected array $statusCache = [];

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

        // Retry logic
        $attempts = 0;
        $maxAttempts = 5;
        $retryDelay = 3; // seconds

        while ($attempts < $maxAttempts) {
            $tiles = MapRepository::findAllTiles($mapId);
            if ($tiles !== false) {
                break; // Success
            }

            $attempts++;
            $this->warn("Tiles not found for map {$mapId} in TreeProcessing. Retrying in {$retryDelay}s... (Attempt {$attempts}/{$maxAttempts})");
            sleep($retryDelay);
        }

        if ($tiles === false) {
            $errorMsg = "Map {$mapId} cannot be processed by TreeProcessing: missing tiles after {$maxAttempts} attempts.";
            $this->error($errorMsg);
            Log::error($errorMsg);
            return self::FAILURE;
        }

        $allCells = MapRepository::findAllCells($mapId);

        // Check if cells exist (they might have been deleted by a concurrent map:1init)
        if ($allCells === false || empty($allCells)) {
            $this->error("No cells found for map {$mapId}. Map data may have been reset by another process.");
            $this->error("Please avoid clicking 'Generate Map' multiple times simultaneously.");
            return self::FAILURE;
        }

    // Tree creation started.
    $this->updateMapStatus($map, MapStatus::TREE_FIRST_STEP, 'treeStepSecond');

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
                        // Ensure has_trees flag is set when tileTypeId = 29 (trees)
                        if (property_exists($tile, 'has_trees')) {
                            $tile->has_trees = true;
                        }
                    $tile->name = 'inner-Land';
                    $tile->description = 'Passable';
                    $tile->tileTypeId = 1;
                } else if ($tile->tileTypeId == 1) {
                    $tile->name = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId = 29;
                        if (property_exists($tile, 'has_trees')) {
                            $tile->has_trees = true;
                        }
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
    
    protected function updateMapStatus($map, string $statusName, ?string $nextStep = null): void
    {
        $map->state = $statusName;
        $map->mapstatuses_id = $this->resolveStatusId($statusName);

        if ($nextStep !== null) {
            $map->next_step = $nextStep;
        }

        $map->save();
    }

    protected function resolveStatusId(string $statusName): ?int
    {
        if (!array_key_exists($statusName, $this->statusCache)) {
            $this->statusCache[$statusName] = MapStatus::firstWhere('name', $statusName)?->id;
        }

        return $this->statusCache[$statusName];
    }
}

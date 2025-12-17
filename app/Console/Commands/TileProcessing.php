<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Models\MapStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Artisan command: map:2firststep-tiles
 *
 * Processes cells and assigns tile types based on parent cell properties.
 * This is Step 2 in the map generation pipeline.
 */
class TileProcessing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:2firststep-tiles {mapId : The Map ID to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process tiles based on cell data (Step 2)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        $this->info("Starting tile processing for map {$mapId}...");

        $map = MapRepository::findFirst($mapId);
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        // Retry logic
        $attempts = 0;
        $maxAttempts = 5;
        $retryDelay = 2; // seconds

        while ($attempts < $maxAttempts) {
            $tiles = MapRepository::findAllTilesReversedAxis($mapId);
            if ($tiles !== false) {
                break; // Success
            }

            $attempts++;
            $this->warn("Tiles not found for map {$mapId}. Retrying in {$retryDelay}s... (Attempt {$attempts}/{$maxAttempts})");
            sleep($retryDelay);
        }

        if ($tiles === false) {
            $errorMsg = "Map {$mapId} cannot be processed: missing tiles from MapRepository::findAllTilesReversedAxis after {$maxAttempts} attempts.";
            $this->error($errorMsg);
            Log::error($errorMsg);
            return self::FAILURE;
        }

        // Tile creation started.
        $map->state = MapStatus::TILE_PROCESSING_STARTED;
        $map->mapstatuses_id = MapStatus::firstWhere('name', MapStatus::TILE_PROCESSING_STARTED)?->id;
        $map->save();

        // All the cells in the current Map.
        $cells = MapRepository::findAllCells($mapId);

        if (!is_iterable($cells)) {
            $errorMsg = "Map {$mapId} cannot be processed: missing cells from MapRepository::findAllCells";
            $this->error($errorMsg);
            Log::error($errorMsg);
            return self::FAILURE;
        }


        $tileCount = 0;
        
        foreach ($tiles as $something) {
            foreach ($something as $tile) {
                $currentCell = $cells[$tile->getCellX()][$tile->getCellY()];

                if ($currentCell->name == 'Passable Land') {
                    $tile->name = 'inner-Land';
                    $tile->description = 'Passable';
                    $tile->tileTypeId = 1;
                } else if ($currentCell->name == 'Trees') {
                    $tile->name = 'inner-Tree';
                    $tile->description = 'The default tree tile';
                    $tile->tileTypeId = 29;
                } else if ($currentCell->name == 'Water') {
                    $tile->name = 'inner-WaterTile';
                    $tile->description = 'The Inside Water Tile.';
                    $tile->tileTypeId = 3;
                } else {
                    // Anything else becomes a Rock Tile.
                    $tile->name = 'inner-Rock';
                    $tile->description = 'Rocky area.';
                    $tile->tileTypeId = 2;
                }
                
                $tile->height = $currentCell->height;
                $tile->save();
                $tileCount++;
            }
        }

        $this->info("Processed {$tileCount} tiles.");

        // Running tree hole puncher prep
        $mapRecord = MapRepository::findFirst($mapId);
        $treeCells = MapRepository::findAllTreeCells($mapId);

        if ($treeCells !== false) {
            $this->info("Running hole puncher for tree cells...");
            $mapLoader = new \App\Helpers\MapDatabase\MapHelper($mapRecord->id, $tiles, $treeCells);
            $mapLoader->holePuncher($mapId);
        }

    $map->state = MapStatus::TILE_PROCESSING_STOPPED;
    $map->mapstatuses_id = MapStatus::firstWhere('name', MapStatus::TILE_PROCESSING_STOPPED)?->id;
    $map->save();

        $this->info("Tile processing completed for map {$mapId}.");
        
        return self::SUCCESS;
    }
}

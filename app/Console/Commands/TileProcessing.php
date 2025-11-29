<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Models\MapStatus;
use Illuminate\Console\Command;

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

        // Tile creation started.
        $map->setState(MapStatus::TILE_PROCESSING_STARTED);
        $map->save();

        // All the cells in the current Map.
        $cells = MapRepository::findAllCells($mapId);

        // The reversed x and y made it easier to check if a row existed before iterating over it in the view.
        $tiles = MapRepository::findAllTilesReversedAxis($mapId);

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

        $map->setState('Tile creation process completed.');
        $map->save();

        $this->info("Tile processing completed for map {$mapId}.");
        
        return self::SUCCESS;
    }
}

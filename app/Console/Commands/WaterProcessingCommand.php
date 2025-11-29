<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\Processing\WaterProcessing;
use App\Helpers\ModelHelpers\Map as MapMemory;
use Illuminate\Console\Command;

/**
 * Artisan command: map:4water
 *
 * Processes water tiles for the map.
 * This is Step 4 in the map generation pipeline.
 */
class WaterProcessingCommand extends Command
{
    protected $signature = 'map:4water {mapId : The Map ID to process}';
    protected $description = 'Process water tiles (Step 4)';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        $size = 38;
        
        $this->info("Starting water processing for map {$mapId}...");
        
        $map = MapRepository::findFirst($mapId);
        
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        $waterTileLocations = MapRepository::findAllWaterTileCoordinates($mapId);
        
        if (!$waterTileLocations) {
            $this->info("No water tiles found. Skipping water processing.");
            return self::SUCCESS;
        }

        $this->info("Processing water tiles...");

        // Check if the WaterProcessing class and dependencies exist
        if (!class_exists('\App\Helpers\Processing\WaterProcessing')) {
            $this->warn("WaterProcessing class not fully implemented yet. Skipping step.");
            return self::SUCCESS;
        }

        try {
            $mapMemory = new MapMemory();
            $mapMemory->setDatabaseRecord($map)->setSize($size);

            $WaterProcessor = app(WaterProcessing::class);
            
            if (method_exists($WaterProcessor, 'setWaterTileLocations')) {
                $WaterProcessor->setWaterTileLocations($waterTileLocations)
                    ->setMap($mapMemory);
                    
                if (method_exists($WaterProcessor, 'waterTiles')) {
                    $WaterProcessor->waterTiles();
                    $this->info("Water processing completed for map {$mapId}.");
                }
            }
        } catch (\Exception $e) {
            $this->error("Water processing failed: " . $e->getMessage());
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}

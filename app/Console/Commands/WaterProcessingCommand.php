<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\MapDatabase\WaterProcessingMapDatabaseLayer;
use App\Helpers\Processing\WaterProcessing;
use App\Helpers\ModelHelpers\Map as MapMemory;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Log;

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

    protected Application $app;

    public function __construct(Application $app)
    {
        parent::__construct();
        $this->app = $app;
    }

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
            
            $databaseLayer = new WaterProcessingMapDatabaseLayer($mapId);
            $waterProcessor = new WaterProcessing();
            $waterProcessor->setWaterProcessingDatabaseLayer($databaseLayer)
                ->setMap($mapMemory)
                ->setWaterTileLocations($waterTileLocations);

            if (method_exists($waterProcessor, 'waterTiles')) {
                $waterProcessor->waterTiles();
                $this->info("Water processing completed for map {$mapId}.");
            }
        } catch (\Exception $e) {
            $this->error("Water processing failed: " . $e->getMessage());
            Log::error($e);
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}

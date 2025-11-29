<?php

namespace App\Console\Commands;

use App\Helpers\MapDatabase\MapRepository;
use App\Helpers\Processing\MountainProcessing;
use Illuminate\Console\Command;

/**
 * Artisan command: map:5mountain
 *
 * Processes mountain ridges and tile types.
 * This is Step 5 in the map generation pipeline.
 */
class MountainProcessingCommand extends Command
{
    protected $signature = 'map:5mountain {mapId : The Map ID to process} {mountainLine? : The mountain height threshold}';
    protected $description = 'Process mountain tiles and ridges (Step 5)';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        $mountainLine = $this->argument('mountainLine') ?? 150; // Default threshold
        
        $this->info("Starting mountain processing for map {$mapId} with threshold {$mountainLine}...");
        
        $map = MapRepository::findFirst($mapId);
        
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        $this->info("Finding mountain cells...");
        $mountains = MapRepository::findAllMountainCells($mapId, $mountainLine);

        if (!$mountains || (is_countable($mountains) && count($mountains) === 0)) {
            $this->info("No mountain cells found above threshold {$mountainLine}. Skipping.");
            return self::SUCCESS;
        }

        $this->info("Processing mountain ridges...");
        
        try {
            if (!class_exists('\App\Helpers\Processing\MountainProcessing')) {
                $this->warn("MountainProcessing class not found. Skipping step.");
                return self::SUCCESS;
            }

            $mountainProcessor = new MountainProcessing();
            $tiles = MapRepository::findAllTiles($mapId);
            
            $mountainProcessor->init()
                ->setTiles($tiles)
                ->setMountainCells($mountains)
                ->setMountainLine($mountainLine)
                ->createRidges();

            $this->info("Mountain processing completed for map {$mapId}.");
        } catch (\Exception $e) {
            $this->error("Mountain processing failed: " . $e->getMessage());
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}

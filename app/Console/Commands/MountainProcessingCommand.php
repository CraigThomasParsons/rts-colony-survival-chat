<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Map;
use App\Models\Cell;
use App\Services\MountainRidgeService;

/**
 * Artisan command: map:5mountain
 *
 * Processes mountain ridges and tile types.
 * This is Step 5 in the map generation pipeline.
 */
class MountainProcessingCommand extends Command
{
    protected $signature = 'map:5mountain {mapId : The Map ID to process} {mountainLine? : Optional high threshold override}';
    protected $description = 'Process mountain tiles and ridges (Step 5) using two-pass thresholds';

    public function handle(): int
    {
        $mapId = $this->argument('mapId');
        $overrideHigh = $this->argument('mountainLine');

        $map = Map::find($mapId);
        if (!$map) {
            $this->error("Map {$mapId} not found.");
            return self::FAILURE;
        }

        // Gather all heights for percentile computation
        $heights = Cell::where('map_id', $mapId)->pluck('height')->all();
        if (empty($heights)) {
            $this->warn("No cells found for map {$mapId}; skipping mountain processing.");
            return self::SUCCESS;
        }

        sort($heights);
        $pct = function(array $arr, float $p): int {
            $n = count($arr);
            $idx = (int) floor($n * $p);
            if ($idx >= $n) { $idx = $n - 1; }
            if ($idx < 0) { $idx = 0; }
            return (int) $arr[$idx];
        };

        $computedLow = $pct($heights, 0.75);
        $computedHigh = $pct($heights, 0.90);

        // Allow CLI override for high threshold; derive low slightly below when provided
        $mountainThresholdHigh = $overrideHigh !== null ? (int) $overrideHigh : $computedHigh;
        $mountainThresholdLow  = $overrideHigh !== null ? max(0, (int)$overrideHigh - 10) : $computedLow;

        $this->info("Starting mountain processing for map {$mapId} (low={$mountainThresholdLow}, high={$mountainThresholdHigh})...");

        try {
            (new MountainRidgeService())->run($mapId, $mountainThresholdLow, $mountainThresholdHigh);
            $this->info("Mountain processing completed for map {$mapId}.");
        } catch (\Throwable $e) {
            $this->error("Mountain processing failed: " . $e->getMessage());
            Log::error('map:5mountain failed', ['map_id' => $mapId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

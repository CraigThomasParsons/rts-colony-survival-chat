<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use App\Services\MapFirstStepGenerator;

class HeightMapInit extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    // Modern Laravel console signature: define arguments/options here
    protected $signature = 'map:1init {mapId : The Map Id to initialize} {--force : Force re-initialization even if map appears initialized}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HeightMap-Inialization: Start the heightmap and generate cells.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!defined('BASEDIR')) {
            define('BASEDIR', dirname(__FILE__).'/../../');
        }

    $mapId = $this->argument('mapId');
    $force = (bool) $this->option('force');

        // Concurrency / re-run guard: do not re-initialize if map already initialized or generation locked.
        $map = \App\Models\Map::find($mapId);
        if ($map) {
            // If the map already has any cells generated previously we assume init ran.
            // Simpler heuristic: if is_generating flag set OR description mentions queued completion OR status set, skip.
            if (!$force && property_exists($map, 'is_generating') && $map->is_generating) {
                $this->warn("Map {$mapId} is already generating. Skipping re-init.");
                return Command::SUCCESS;
            }
            // If there is a status id we treat init as already completed (prevents wiping data).
            // When mapstatuses_id column is missing (older schema) or null, we allow init to run.
            if (!$force && Schema::hasColumn('map', 'mapstatuses_id') && !is_null($map->mapstatuses_id)) {
                $this->warn("Map {$mapId} already initialized (mapstatuses_id present). Skipping re-init.");
                return Command::SUCCESS;
            }
        }

        // Use the service to execute the first step (works in CLI context)
        $generator = new MapFirstStepGenerator();
        try {
            $this->info("Running first step for map {$mapId}" . ($force ? ' (forced)' : ''));
            $generator->generate($mapId);
            $this->info("Created Map({$mapId}) Heightmap. Completed map cell generation process.");
        } catch (\Throwable $e) {
            $this->error("Failed to generate map {$mapId}: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}

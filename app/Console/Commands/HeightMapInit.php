<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HeightMapInit extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    // Modern Laravel console signature: define arguments/options here
    protected $signature = 'map:1init {mapId : The Map Id to initialize}';

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

        // Concurrency / re-run guard: do not re-initialize if map already initialized or generation locked.
        $map = \App\Models\Map::find($mapId);
        if ($map) {
            // If the map already has any cells generated previously we assume init ran.
            // Simpler heuristic: if is_generating flag set OR description mentions queued completion OR status set, skip.
            if (property_exists($map, 'is_generating') && $map->is_generating) {
                $this->warn("Map {$mapId} is already generating. Skipping re-init.");
                return Command::SUCCESS;
            }
            // If there is a status id we treat init as already completed (prevents wiping data).
            if (!is_null($map->mapstatuses_id)) {
                $this->warn("Map {$mapId} already initialized (mapstatuses_id present). Skipping re-init.");
                return Command::SUCCESS;
            }
        }

        // Use the HTTP MapController to execute the first step
        $controller = new \App\Http\Controllers\MapController();
        $controller->runFirstStep($mapId);

        $this->info("Created Map(".$mapId.") Heightmap. Completed map cell generation process.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    // Legacy getArguments() not needed when using $signature

    /**
     * Get the console command options.
     *
     * @return array
     */
    // protected function getOptions()
    // {
    //     return array(
    //         array('size', null, InputOption::VALUE_OPTIONAL, 'The width and height of the map.', null),
    //     );
    // }

}

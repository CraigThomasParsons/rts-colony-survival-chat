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

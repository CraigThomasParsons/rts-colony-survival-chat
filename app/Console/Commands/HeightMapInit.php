<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HeightMapInit extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:1init';

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
    public function fire()
    {
        if (!defined('BASEDIR')) {
            define('BASEDIR', dirname(__FILE__).'/../../');
        }

        $mapId = $this->argument('mapId');

        $controller = new MapController();
        $controller->runFirstStep($mapId);

        $this->info("Created Map(".$mapId.") Heightmap. Completed map cell generation process.");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    // protected function getArguments()
    // {
    //     return array(
    //         array('mapId', InputArgument::REQUIRED, 'The Map Id.'),
    //     );
    // }

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

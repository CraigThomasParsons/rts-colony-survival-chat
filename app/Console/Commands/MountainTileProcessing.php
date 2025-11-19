<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MountainTileProcessing extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:6mountaincells';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Process mountain tiles.";

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
        $mountainLine = $this->argument('mountainLine');

        $controller = new MapController();
        $controller->runLastStep($mapId, $mountainLine);

        $this->info("Map(".$mapId.") : Completed mountain process.");
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
    //         array('mountainLine', InputArgument::REQUIRED, 'The Mountain Line.')
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

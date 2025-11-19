<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ThirdStepRunTreeAlgorithm extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:3trees-firststep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "John Conway's Game of Life: Start the first step of this tree process.";

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
        $controller->runThirdStep($mapId);

        $this->info("Map(".$mapId.") : Completed map first step in tree process.");
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

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ThirdStepRunSecondTreeAlgorithmStep extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'map:4trees-secondstep';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "John Conway's Game of Life: Invert the process for 25 iteration, life equals death.";

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
        $controller->runTreeStepTwo($mapId);

        // Adding extra information.
        $this->info("Map(".$mapId.") : Completed map second step in tree process.");
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

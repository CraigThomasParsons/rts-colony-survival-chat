<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MapGenerator;

class GenerateMap extends Command
{
    protected $signature = 'map:generate 
        {name=Generated Map} 
        {--w=32 : Cell width} 
        {--h=32 : Cell height}
        {--origin-x=0}
        {--origin-y=0}';

    protected $description = 'Generate a map (cells + tiles) into MySQL';

    public function handle(): int
    {
        $name     = $this->argument('name');
        $width    = (int) $this->option('w');
        $height   = (int) $this->option('h');
        $originX  = (int) $this->option('origin-x');
        $originY  = (int) $this->option('origin-y');

        $generator = new MapGenerator(
            cellWidth:  $width,
            cellHeight: $height,
            seed:       null,
            originX:    $originX,
            originY:    $originY,
        );

        $map = $generator->generate($name);

        $this->info("Map #{$map->id} generated at origin ({$map->coordinateX}, {$map->coordinateY})");

        return static::SUCCESS;
    }
}
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
        {--origin-y=0}
        {--game-id= : Optional game ID to attach the map to}';

    protected $description = 'Generate a map (cells + tiles) into MySQL';

    public function handle(): int
    {
        $name     = $this->argument('name');
        $width    = (int) $this->option('w');
        $height   = (int) $this->option('h');
        $originX  = (int) $this->option('origin-x');
        $originY  = (int) $this->option('origin-y');
        $gameId   = $this->option('game-id');

        if ($gameId !== null && ! \App\Models\Game::query()->whereKey($gameId)->exists()) {
            $this->error("Game #{$gameId} does not exist.");
            return static::FAILURE;
        }

        $generator = new MapGenerator(
            cellWidth:  $width,
            cellHeight: $height,
            seed:       null,
            originX:    $originX,
            originY:    $originY,
        );

        $map = $generator->generate($name, $gameId ? (int) $gameId : null);
        $origin = $map->meta['origin'] ?? ['x' => $originX, 'y' => $originY];

        $this->info("Map #{$map->id} generated for game {$map->game_id} at origin ({$origin['x']}, {$origin['y']})");

        return static::SUCCESS;
    }
}

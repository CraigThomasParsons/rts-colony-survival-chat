<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Game;
use App\Models\Map;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->everyMinute();

Schedule::command('app:execute-tasks-in-progress')->everySecond();

// Backfill: associate maps to games using naming convention "{game->name} Map"
Artisan::command('backfill:game-map', function () {
    $games = Game::orderBy('id')->get();
    $count = 0;

    foreach ($games as $game) {
        $expectedMapName = trim(($game->name ?? '') . ' Map');
        if ($expectedMapName === ' Map') {
            continue;
        }
        $maps = Map::where('name', $expectedMapName)->get();
        foreach ($maps as $map) {
            try {
                $game->maps()->syncWithoutDetaching([$map->id]);
                $count++;
            } catch (\Throwable $e) {
                // ignore duplicates or errors
            }
        }
    }

    $this->info("Backfilled {$count} game↔map links.");
})->describe('Associate maps to games based on name convention');
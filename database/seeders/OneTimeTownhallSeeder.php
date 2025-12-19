<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\BuildingType;
use App\Models\Game;
use Illuminate\Database\Seeder;

class OneTimeTownhallSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::first();
        if (!$game) {
            $this->command->warn('No game found. Seeding skipped.');
            return;
        }

        $townHallType = BuildingType::where('name', BuildingType::TOWN_HALL)->first();
        
        if (!$townHallType) {
            $this->call(BuildingTypeSeeder::class);
            $townHallType = BuildingType::where('name', BuildingType::TOWN_HALL)->firstOrFail();
        }

        // Check if map already has a town hall?
        // For now, just create one at 5,5 if no buildings exist.
        if (Building::where('game_id', $game->id)->exists()) {
            $this->command->info('Buildings already exist. Skipped specific TownHall seed.');
            return;
        }

        Building::create([
            'game_id' => $game->id,
            'building_type_id' => $townHallType->id,
            'x' => 5,
            'y' => 5,
            'hitpoints' => $townHallType->hitpoints,
            'status' => 'active',
        ]);

        $this->command->info('Seeded Town Hall at 5,5 for Game ID: ' . $game->id);
    }
}

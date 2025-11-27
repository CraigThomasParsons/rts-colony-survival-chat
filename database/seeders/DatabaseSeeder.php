<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creating the basics for this application to run.
        $this->call([
            StatusSeeder::class,
            TileTypeSeeder::class,
            MapStatusesSeeder::class,
            MapSeeder::class,
            UserSeeder::class,
        ]);
    }
}

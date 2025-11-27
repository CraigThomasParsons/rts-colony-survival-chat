<?php

namespace Database\Seeders;

use App\Models\MapStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class MapStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nowish = new \DateTime();

        if (MapStatus::firstWhere('name', MapStatus::CREATED_EMPTY) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::CREATED_EMPTY,
                'description' => 'Maps created but not generated.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::CELL_PROCESSING_STARTED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::CELL_PROCESSING_STARTED,
                'description' => 'Cell processing started.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::CELL_PROCESSING_FINNISHED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::CELL_PROCESSING_FINNISHED,
                'description' => 'Cell processing completed.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::TILE_PROCESSING_STARTED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::TILE_PROCESSING_STARTED,
                'description' => 'Running first step in tree algorithm.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::TREE_2ND_COMPLETED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::TREE_2ND_COMPLETED,
                'description' => 'Running second step in tree algorithm.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::TREE_3RD_STARTED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::TREE_3RD_STARTED,
                'description' => 'Running the third step in tree algorithm.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (MapStatus::firstWhere('name', MapStatus::TREE_GEN_COMPLETED) === null ) {
            DB::table('map_statuses')->insert([
                'name' => MapStatus::TREE_GEN_COMPLETED,
                'description' => 'Tree planting completed.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }
    }
}
<?php

namespace Database\Seeders;

use App\Models\CellType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nowish = new \DateTime();

        // If we don't see the Land cell type already then add it in.
        if (CellType::firstWhere('name', CellType::BASIC_LAND) === null ) {
            DB::table('cellType')->insert([
                'name' => CellType::BASIC_LAND,
                'description' => 'Passable Area on land.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (CellType::firstWhere('name', CellType::MOUNTAIN) === null ) {
            DB::table('cellType')->insert([
                'name' => CellType::MOUNTAIN,
                'description' => 'Elevated cell area.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (CellType::firstWhere('name', CellType::WATER) === null ) {
            DB::table('cellType')->insert([
                'name' => CellType::WATER,
                'description' => 'Under water cells',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (CellType::firstWhere('name', CellType::TREE) === null ) {
            DB::table('cellType')->insert([
                'name' => CellType::TREE,
                'description' => 'Tree cell area.',
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }
    }
}

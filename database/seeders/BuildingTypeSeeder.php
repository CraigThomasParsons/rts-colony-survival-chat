<?php

namespace Database\Seeders;

use App\Models\BuildingType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BuildingTypeSeeder extends Seeder
{
    /**
     * Seeding the building types as with these attributes:
     *
     * Town Hall : Storage
     * Statistics
     * Hit Points	2500
     * Armor	0
     * 2 (1 upgrade)
     * 4 (all upgrades)
     * Production Related
     * Gold	400 Gold
     * Lumber	400 Lumber
     * Build time	1000
     *
     * Farm : Provide capacity for units.
     * Statistics
     * Hit Points	400
     * Armor	0
     * 2 (1 upgrade)
     * 4 (all upgrades)
     * Production Related
     * Gold	500 Gold
     * Lumber	300 Lumber
     * Build time	1000
     */
    public function run(): void
    {
        $nowish = new \DateTime();

        if (BuildingType::firstWhere('name', BuildingType::TOWN_HALL) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::TOWN_HALL,
                'description' => 'Main Building, where all resources will be stored.',
                'order' => 1,
                'monetaryValue' => 400,
                'lumber'=> 400,
                'buildtime'=> 1000,
                'hitpoints'=> 2500,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        $townHall = BuildingType::firstWhere('name', BuildingType::TOWN_HALL);

        if (BuildingType::firstWhere('name', BuildingType::TOWER) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::TOWER,
                'description' => 'Research Tower',
                'order' => 2,
                'buildtypeRequirements_id' => $townHall->id,
                'monetaryValue' => 1400,
                'lumber'=> 300,
                'buildtime'=> 2000,
                'hitpoints'=> 900,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        $tower = BuildingType::firstWhere('name', BuildingType::TOWER);

        if (BuildingType::firstWhere('name', BuildingType::FARM) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::FARM,
                'description' => 'Farm',
                'order' => 3,
                'buildtypeRequirements_id' => $townHall->id,
                'hitpoints'=> 400,
                'monetaryValue' => 500,
                'lumber'=> 300,
                'buildtime'=> 1000,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (BuildingType::firstWhere('name', BuildingType::BARRACKS) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::BARRACKS,
                'description' => 'House and Train Soldiers or Knights',
                'order' => 4,
                'buildtypeRequirements_id' => $townHall->id,
                'hitpoints'=> 800,
                'monetaryValue' => 600,
                'lumber'=> 500,
                'buildtime'=> 1500,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (BuildingType::firstWhere('name', BuildingType::LUMBER_MILL) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::LUMBER_MILL,
                'order' => 5,
                'buildtypeRequirements_id' => $townHall->id,
                'hitpoints'=> 600,
                'monetaryValue' => 600,
                'lumber'=> 500,
                'buildtime'=> 1500,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        $lumberMill = BuildingType::firstWhere('name', BuildingType::LUMBER_MILL);

        if (BuildingType::firstWhere('name', BuildingType::BLACK_SMITH) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::BLACK_SMITH,
                'description' => 'Create Weapons, Tools and Armor.',
                'order' => 6,
                'buildtypeRequirements_id' => $lumberMill->id,
                'hitpoints'=> 2500,
                'monetaryValue' => 400,
                'lumber'=> 400,
                'buildtime'=> 1000,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        $buildingSmith = BuildingType::firstWhere('name', 'Black Smith');

        if (BuildingType::firstWhere('name', BuildingType::STABLES) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::STABLES,
                'description' => 'Where to keep horses.',
                'order' => 7,
                'buildtypeRequirements_id' => $buildingSmith->id,
                'hitpoints'=> 2500,
                'monetaryValue' => 400,
                'lumber'=> 400,
                'buildtime'=> 1000,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (BuildingType::firstWhere('name', BuildingType::TEMPLE) === null) {
            DB::table('buildtypes')->insert([
                'name' => BuildingType::TEMPLE,
                'description' => 'A place of worship. In the future I think this will be where Angels are summoned.',
                'order' => 8,
                'buildtypeRequirements_id' => $lumberMill->id,
                'hitpoints'=> 2500,
                'monetaryValue' => 400,
                'lumber'=> 400,
                'buildtime'=> 1000,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }
    }
}

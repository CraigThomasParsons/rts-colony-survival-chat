<?php

namespace Database\Seeders;

use App\Models\TileType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TileTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nowish = new \DateTime();

        /**
         * For each line of the following array "tileTypes"
         * INSERT INTO `tileType` (`id`, `name`, `description`) VALUES
         */ 
        $tileTypes = [
            'inner-Land' => 'Passable',
            'inner-Rock' => 'Rocky area',
            'inner-WaterTile' => 'The Inside Water Tile.',
            'TopRightConvexedCorner-CliffSide' => 'Cliff Top Right Convexed Corner',
            'BottomLeftConvexedCorner-CliffSideTile' => 'Cliff Bottom Left Convexed Corner',
            'TopLeftConvexedCorner-CliffSideTile' => 'Cliff Top Left Convexed Corner',
            'BottomRightConvexedCorner-CliffSideTile' => 'Cliff Bottom Right Convexed Corner',
            'TopRightConcaveCorner-CliffSideTile' => 'Cliff Top Right Concave Corner',
            'TopLeftConcaveCorner-CliffSideTile' => 'Cliff Top Left Concave Corner',
            'bottomRightConcaveCorner-CliffSideTile' => 'Cliff Bottom Right Concave Corner',
            'bottomLeftConcaveCorner-CliffSideTile' => 'Cliff Bottom Left Concave Corner',
            'topEdge-CliffSideTile' => 'Cliff Top Edge',
            'rightEdge-CliffSideTile' => 'Cliff Right Edge',
            'bottomEdge-CliffSideTile' => 'Cliff Bottom Edge',
            'leftEdge-CliffSideTile' => 'Cliff Left Edge',
            'TopRightConvexedCorner-WaterTile' => 'Water Tile Top Right Convexed Corner',
            'BottomLeftConvexedCorner-WaterTile' => 'Water Tile Bottom Left Convexed Corner',
            'TopLeftConvexedCorner-WaterTile' => 'Water Tile Top Left Convexed Corner',
            'BottomRightConvexedCorner-WaterTile' => 'Water Tile Bottom Right Convexed Corner',
            'TopRightConcaveCorner-WaterTile' => 'Water Tile Top Right Concave Corner',
            'TopLeftConcaveCorner-WaterTile' => 'Water Tile Top Left Concave Corner',
            'bottomRightConcaveCorner-WaterTile' => 'Water Tile Bottom Right Concave Corner',
            'bottomLeftConcaveCorner-WaterTile' => 'Water Tile Bottom Left Concave Corner',
            'topEdge-WaterTile' => 'Water Tile Top Edge',
            'rightEdge-WaterTile' => 'Water Tile Right Edge',
            'bottomEdge-WaterTile' => 'Water Tile Bottom Edge',
            'leftEdge-WaterTile' => 'Water Tile Left Edge',
            'inner-Tree' => 'The default tree tile'
        ];

        foreach ($tileTypes as $name => $description) {

            // If we don't see type already in the database then add it in.
            if (TileType::firstWhere('name', $name) === null ) {
                DB::table('tileType')->insert([
                    'name' => $name,
                    'description' => $description,
                    'created_at' => $nowish->format('Y-m-d H:i:s'),
                    'updated_at' => $nowish->format('Y-m-d H:i:s')
                ]);
            }
        }
    }
}

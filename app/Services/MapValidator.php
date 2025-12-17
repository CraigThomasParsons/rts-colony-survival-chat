<?php

namespace App\Services;

use App\Models\Map;
use App\Models\Tile;

class MapValidator
{
    /**
     * Validate required terrain conditions for a generated map.
     *
     * @return array{ok:bool,errors:array<int,string>,counts:array<string,int>}
     */
    public function validate(Map $map): array
    {
        $errors = [];

        $totalTiles = Tile::query()->where('map_id', $map->id)->count();
        if ($totalTiles <= 0) {
            $errors[] = 'tiles.count must be > 0';
        }

        // land tiles: default passable land (type 1)
        $land = Tile::query()
            ->where('map_id', $map->id)
            ->where('tileType_id', 1)
            ->count();
        if ($land < 1) {
            $errors[] = 'land tiles must be >= 1';
        }

        // water tiles: include base water (3) plus water edges/corners (16-27)
        $water = Tile::query()
            ->where('map_id', $map->id)
            ->where(function ($q) {
                $q->where('tileType_id', 3)
                  ->orWhereBetween('tileType_id', [16, 27]);
            })
            ->count();
        if ($water < 1) {
            $errors[] = 'water tiles must be >= 1';
        }

        // mountain tiles: ridge/corners/edges (4-15) plus explicit rock (2)
        $mountain = Tile::query()
            ->where('map_id', $map->id)
            ->where(function ($q) {
                $q->where('tileType_id', 2)
                  ->orWhereBetween('tileType_id', [4, 15]);
            })
            ->count();
        if ($mountain < 1) {
            $errors[] = 'mountain tiles must be >= 1';
        }

        // trees: authoritative flag is has_trees
        $trees = Tile::query()
            ->where('map_id', $map->id)
            ->where('has_trees', true)
            ->count();
        if ($trees < 1) {
            $errors[] = 'trees must be >= 1';
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'counts' => [
                'tiles' => (int) $totalTiles,
                'land' => (int) $land,
                'water' => (int) $water,
                'mountain' => (int) $mountain,
                'trees' => (int) $trees,
            ],
        ];
    }
}

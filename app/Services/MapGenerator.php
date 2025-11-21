<?php
namespace App\Services;

use App\Models\Game;
use App\Models\ResourceNode;
use App\Models\WorldMap;
use App\Models\WorldTile;
use Illuminate\Support\Facades\DB;

/**
 * MapGenerator - procedural map generation using simple noise + rules.
 *
 * Intent:
 * - Generate an X by Y grid of tiles with terrain types
 * - Scatter resource nodes (wood, stone, gold) in plausible clusters
 * - Save Map, Tiles, and ResourceNode rows to DB
 */

class MapGenerator
{
    protected int $width;
    protected int $height;
    protected int $seed;
    protected int $originX;
    protected int $originY;

    public function __construct(
        int $cellWidth = 32,
        int $cellHeight = 32,
        ?int $seed = null,
        int $originX = 0,
        int $originY = 0,
    ) {
        $this->width   = $cellWidth;
        $this->height  = $cellHeight;
        $this->seed    = $seed ?? time();
        $this->originX = $originX;
        $this->originY = $originY;

        mt_srand($this->seed);
    }

    /**
     * Generate the map and save it to the database.
     */
    public function generate(string $name, ?int $gameId = null): WorldMap
    {
        return DB::transaction(function () use ($name, $gameId) {
            $gameId = $gameId ?? Game::query()->create([
                'name' => $name,
            ])->id;

            $map = WorldMap::query()->create([
                'game_id' => $gameId,
                'name'    => $name,
                'meta'    => [
                    'seed'   => $this->seed,
                    'width'  => $this->width,
                    'height' => $this->height,
                    'origin' => [
                        'x' => $this->originX,
                        'y' => $this->originY,
                    ],
                ],
            ]);

            for ($x = 0; $x < $this->width; $x++) {
                for ($y = 0; $y < $this->height; $y++) {
                    $val = $this->noise($x, $y);
                    $terrain = 'grass';
                    if ($val < 0.18) {
                        $terrain = 'water';
                    } elseif ($val < 0.35) {
                        $terrain = 'sand';
                    } elseif ($val < 0.6) {
                        $terrain = 'grass';
                    } elseif ($val < 0.8) {
                        $terrain = 'forest';
                    } else {
                        $terrain = 'hill';
                    }

                    WorldTile::query()->create([
                        'map_id'  => $map->id,
                        'x'       => $x,
                        'y'       => $y,
                        'terrain' => $terrain,
                        'meta'    => [],
                    ]);
                }
            }

            $this->scatterNodes($map, 'wood', 40, 8, 20);
            $this->scatterNodes($map, 'stone', 20, 6, 30);
            $this->scatterNodes($map, 'gold', 6, 3, 80);

            return $map->fresh();
        });
    }

    /**
     * Generate noise value for terrain generation.
     * (Simple layered random thresholds)
     */
    protected function noise($x, $y)
    {
        // naive value: combine sine waves and rand for variation
        $v = (sin($x / 6.0) + cos($y / 5.0) + (mt_rand() / mt_getrandmax())) / 3.0;
        return ($v + 1) / 2.0; // normalize 0..1
    }

    /**
     * Scatter resource nodes on the map.
     */
    protected function scatterNodes(WorldMap $map, string $type, int $count, int $cluster, int $amount): void
    {
        for ($i = 0; $i < $count; $i++) {
            $cx = mt_rand(0, $this->width - 1);
            $cy = mt_rand(0, $this->height - 1);
            for ($j = 0; $j < $cluster; $j++) {
                $nx = max(0, min($this->width - 1, $cx + mt_rand(-3, 3)));
                $ny = max(0, min($this->height - 1, $cy + mt_rand(-3, 3)));
                ResourceNode::query()->create([
                    'map_id' => $map->id,
                    'type'   => $type,
                    'x'      => $nx,
                    'y'      => $ny,
                    'amount' => $amount,
                    'meta'   => [],
                ]);
            }
        }
    }
}

// AI Notes:
// - This service generates the game map.
// - It uses a simple noise function to create terrain.

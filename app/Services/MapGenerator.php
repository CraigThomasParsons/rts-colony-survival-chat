<?php
namespace App\Services;

use App\Models\Map;
use App\Models\Tile;
use App\Models\ResourceNode;

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
    protected $width;
    protected $height;
    protected $seed;

    public function __construct(
        int $cellWidth = 32,
        int $cellHeight = 32,
        ?int $seed = null,
        int $originX = 0,
        int $originY = 0,
    ) {
      $this->cellWidth  = $cellWidth;
      $this->cellHeight = $cellHeight;
      $this->seed       = $seed ?? time();
      $this->originX    = $originX;
      $this->originY    = $originY;

      mt_srand($this->seed);
    }

    public function generate(Map $map)
    {
        // store map meta
        $map->meta = ['seed' => $this->seed, 'w' => $this->width, 'h' => $this->height];
        $map->save();

        // simple procedural generation: perlin-like via layered random thresholds
        for ($x = 0; $x < $this->width; $x++) {
            for ($y = 0; $y < $this->height; $y++) {
                $val = $this->noise($x, $y);
                $terrain = 'grass';
                if ($val < 0.18) $terrain = 'water';
                elseif ($val < 0.35) $terrain = 'sand';
                elseif ($val < 0.6) $terrain = 'grass';
                elseif ($val < 0.8) $terrain = 'forest';
                else $terrain = 'hill';

                $tile = Tile::create(['map_id' => $map->id, 'x' => $x, 'y' => $y, 'terrain' => $terrain, 'meta' => []]);
            }
        }

        // resource node scatter
        $this->scatterNodes($map, 'wood', 40, 8, 20);
        $this->scatterNodes($map, 'stone', 20, 6, 30);
        $this->scatterNodes($map, 'gold', 6, 3, 80);

        return $map;
    }

    protected function noise($x, $y)
    {
        // naive value: combine sine waves and rand for variation
        $v = (sin($x / 6.0) + cos($y / 5.0) + (mt_rand() / mt_getrandmax())) / 3.0;
        return ($v + 1) / 2.0; // normalize 0..1
    }

    protected function scatterNodes(Map $map, $type, $count, $cluster, $amount)
    {
        for ($i = 0; $i < $count; $i++) {
            $cx = mt_rand(0, $this->width - 1);
            $cy = mt_rand(0, $this->height - 1);
            for ($j = 0; $j < $cluster; $j++) {
                $nx = max(0, min($this->width - 1, $cx + mt_rand(-3, 3)));
                $ny = max(0, min($this->height - 1, $cy + mt_rand(-3, 3)));
                ResourceNode::create(['map_id' => $map->id, 'type' => $type, 'x' => $nx, 'y' => $ny, 'amount' => $amount, 'meta' => []]);
            }
        }
    }
}

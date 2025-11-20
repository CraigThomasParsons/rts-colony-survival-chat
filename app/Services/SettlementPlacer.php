<?php
namespace App\Services;

use App\Helpers\MapDatabase\MapModel;
use App\Models\Settlement;
use Illuminate\Support\Str;

/**
 * SettlementPlacer
 *
 * Simple procedural settlement placement.
 *
 * Intention:
 * - Place a given number of settlements across the MapModel while respecting a minimum distance.
 * - Deterministic if a seed is provided.
 */
class SettlementPlacer
{
    /**
     * Place settlements on the given map.
     *
     * @param MapModel $map
     * @param int $count
     * @param int $minDistance
     * @param int|null $seed
     * @param int $gameId
     * @return array Created Settlement models
     */
    public function placeSettlements($map, int $count = 3, int $minDistance = 20, $seed = null, int $gameId = 1): array
    {
        if ($seed !== null) { srand((int)$seed); }
        
        $w = $map->width; $h = $map->height;
        $placed = [];
        $tries = 0;

        while (count($placed) < $count && $tries < $count * 300) {
            $tries++;
            // keep a border of 5 tiles
            $x = rand(5, max(5, $w-6));
            $y = rand(5, max(5, $h-6));

            $cell = $map->getCell($x,$y);
            if (!$cell) continue;
            if (in_array($cell->terrain, ['water','deep_water','mountain'])) continue;

            // ensure distance to existing settlements
            $ok = true;
            foreach ($placed as $p) {
                $dx = $p->x - $x; $dy = $p->y - $y;
                if (sqrt($dx*$dx + $dy*$dy) < $minDistance) { $ok = false; break; }
            }
            if (! $ok) continue;

            $name = 'Settlement-' . Str::random(4);
            $s = Settlement::create([
                'game_id' => $gameId,
                'name' => $name,
                'type' => 'village',
                'x' => $x,
                'y' => $y,
                'meta' => ['placed_by' => 'procedural', 'tries' => $tries],
            ]);
            $placed[] = $s;
        }

        return $placed;
    }
}

// AI Notes:
// - This service places settlements on the map.
// - It uses a procedural algorithm to ensure a minimum distance between settlements.
// - The placement is deterministic if a seed is provided.

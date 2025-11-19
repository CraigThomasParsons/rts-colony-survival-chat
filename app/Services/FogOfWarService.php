<?php
namespace App\Services;

use App\Helpers\MapDatabase\MapModel;
use App\Models\Colonist;

/**
 * FogOfWarService
 *
 * Computes visible tiles for a player based on colonist positions and vision radii.
 * Intention: simple circular visibility (no LOS), returned as a boolean matrix.
 */
class FogOfWarService
{
    /**
     * Compute visibility matrix for player.
     *
     * @param MapModel $map
     * @param int $playerId
     * @param int $vision default vision radius in tiles
     * @return array [ [bool,...], ... ] same size as map
     */
    public function computeVisibility($map, int $playerId, int $vision = 8): array
    {
        $w = $map->width; $h = $map->height;
        $vis = array_fill(0, $h, array_fill(0, $w, false));

        $colonists = Colonist::where('player_id', $playerId)->get();
        foreach ($colonists as $c) {
            $cx = intval($c->state['x'] ?? 0); $cy = intval($c->state['y'] ?? 0);
            for ($dy = -$vision; $dy <= $vision; $dy++) {
                for ($dx = -$vision; $dx <= $vision; $dx++) {
                    $x = $cx + $dx; $y = $cy + $dy;
                    if ($x < 0 || $y < 0 || $x >= $w || $y >= $h) continue;
                    if (sqrt($dx*$dx + $dy*$dy) <= $vision) {
                        $vis[$y][$x] = true;
                    }
                }
            }
        }

        return $vis;
    }
}

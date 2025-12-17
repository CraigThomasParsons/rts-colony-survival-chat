<?php
namespace App\Services;

use App\Models\Map;
use App\Models\Tile;

/**
 * Pathfinder - A* with diagonal movement and weighted terrain costs.
 */
class Pathfinder
{
    /** @var Map|null $map The map to pathfind on. */
    protected $map;
    /** @var array $tiles A lookup table of tiles on the map, keyed by "x,y". */
    protected $tiles;
    /** @var array $terrainCosts Terrain costs for pathfinding, higher is slower, INF is impassable. */
    protected $terrainCosts = ['grass'=>1.0,'road'=>0.7,'forest'=>1.8,'hill'=>2.5,'water'=>INF];

    /**
     * Pathfinder constructor.
     *
     * @param Map|null $map The map to pathfind on.
     */
    public function __construct(Map $map = null) {
        $this->map = $map;
        $this->loadTiles();
    }

    /**
     * Load tiles from the map into the $tiles array for quick lookup.
     *
     * @return void
     */
    protected function loadTiles()
    {
        $this->tiles = [];
        if (!$this->map) return;
        $ts = $this->map->tiles()->get();
        // key by x,y for fast lookup
        foreach ($ts as $t) $this->tiles[$t->x.','.$t->y] = $t;
    }

    /**
     * Check if a tile is passable (not water).
     * @param int $x
     * @param int $y
     * @return bool
     */
    protected function passable(int $x, int $y): bool { $k = $x.','.$y; if (!isset($this->tiles[$k])) return false; return ($this->tiles[$k]->terrain ?? 'grass') !== 'water'; }

    /**
     * Get the terrain cost for a tile.
     * @param int $x
     * @param int $y
     * @return float
     */
    protected function terrainCost(int $x, int $y): float { $k=$x.','.$y; if (!isset($this->tiles[$k])) return INF; $t = $this->tiles[$k]->terrain ?? 'grass'; return $this->terrainCosts[$t] ?? 1.0; }

    /**
     * Heuristic function for A* (diagonal distance).
     */
    protected function heuristic($ax,$ay,$bx,$by)
    {
        $dx = abs($ax-$bx); $dy = abs($ay-$by); $F = sqrt(2)-1; return ($dx<$dy) ? $F*$dx + $dy : $F*$dy + $dx;
    }

    protected function neighborsWithCost($x,$y)
    {
        $dirs = [[1,0,1.0],[-1,0,1.0],[0,1,1.0],[0,-1,1.0],[1,1,sqrt(2)],[1,-1,sqrt(2)],[-1,1,sqrt(2)],[-1,-1,sqrt(2)]];
        $res = [];
        foreach ($dirs as $d) {
            $nx=$x+$d[0]; $ny=$y+$d[1]; $base=$d[2];
            if ($this->passable($nx,$ny)) { $cost = $base * $this->terrainCost($nx,$ny); $res[] = [$nx,$ny,$cost]; }
        }
        return $res;
    }

    /**
     * Find a path from start to end using A*.
     *
     * @param int $startX
     * @param int $startY
     * @param int $endX
     * @param int $endY
     * @return array An array of ['x'=>int,'y'=>int] or [] if no path found.
     */
    public function findPath($startX,$startY,$endX,$endY)
    {
        if (!$this->map) return [];
        $start = $startX.','.$startY; $end = $endX.','.$endY;
        if (!$this->passable($endX,$endY)) return [];
        $open = new MinHeap(); $open->insert($start,0.0);
        $came = []; $g = [$start=>0.0]; $f = [$start=>$this->heuristic($startX,$startY,$endX,$endY)]; $closed=[];
        while(!$open->isEmpty()) {
            $cur = $open->extract(); if(isset($closed[$cur])) continue; $closed[$cur]=true;
            if ($cur === $end) {
                $path=[];$curk=$cur;
                while(isset($came[$curk])) { list($cx,$cy)=explode(',',$curk); $path[]=['x'=>(int)$cx,'y'=>(int)$cy]; $curk=$came[$curk]; }
                list($sx,$sy)=explode(',',$curk); $path[]=['x'=>(int)$sx,'y'=>(int)$sy]; return array_reverse($path);
            }
            list($cx,$cy)=array_map('intval',explode(',',$cur));
            foreach($this->neighborsWithCost($cx,$cy) as $n) {
                $nx=$n[0];$ny=$n[1];$moveCost=$n[2]; $neighbor=$nx.','.$ny;
                $tent = $g[$cur] + $moveCost;
                if(!isset($g[$neighbor]) || $tent < $g[$neighbor]) {
                    $came[$neighbor]=$cur; $g[$neighbor]=$tent; $f[$neighbor]=$tent + $this->heuristic($nx,$ny,$endX,$endY);
                    $open->insert($neighbor, $f[$neighbor]);
                }
            }
        }
        return [];
    }
}

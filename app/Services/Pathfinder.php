<?php
namespace App\Services;

use App\Models\Map;
use App\Models\Tile;

/**
 * Pathfinder - A* with diagonal movement and weighted terrain costs.
 */
class Pathfinder
{
    protected $map;
    protected $tiles;
    protected $terrainCosts = ['grass'=>1.0,'road'=>0.7,'forest'=>1.8,'hill'=>2.5,'water'=>INF];

    public function __construct(Map $map = null) { $this->map = $map; $this->loadTiles(); }

    protected function loadTiles()
    {
        $this->tiles = [];
        if (!$this->map) return;
        $ts = $this->map->tiles()->get();
        foreach ($ts as $t) $this->tiles[$t->x.','.$t->y] = $t;
    }

    protected function passable($x,$y) { $k = $x.','.$y; if (!isset($this->tiles[$k])) return false; return ($this->tiles[$k]->terrain ?? 'grass') !== 'water'; }
    protected function terrainCost($x,$y) { $k=$x.','.$y; if (!isset($this->tiles[$k])) return INF; $t = $this->tiles[$k]->terrain ?? 'grass'; return $this->terrainCosts[$t] ?? 1.0; }

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

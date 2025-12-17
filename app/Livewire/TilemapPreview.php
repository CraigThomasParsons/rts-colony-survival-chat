<?php
namespace App\Livewire;

use Livewire\Component;
use App\Models\Map;
use App\Models\Tile;

/**
 * TilemapPreview renders the tile grid for a map.
 * It groups 4 tiles per cell with a heavier outer border to visualize cells.
 */
class TilemapPreview extends Component
{
    public string $mapId;
    public int $sizeX = 0;
    public int $sizeY = 0;

    /** @var array<int, array<int, array>> tiles[x][y] = ['type' => int] */
    public array $tiles = [];

    public function mount(string $mapId): void
    {
        $this->mapId = $mapId;
        $map = Map::findOrFail($mapId);
        $this->sizeX = (int)($map->coordinateX ?? 0);
        $this->sizeY = (int)($map->coordinateY ?? 0);
        $this->loadTiles();
    }

    protected function loadTiles(): void
    {
        $rows = Tile::where('map_id', $this->mapId)
            ->select(['coordinateX as x', 'coordinateY as y', 'tileType_id as type'])
            ->orderBy('coordinateY')
            ->orderBy('coordinateX')
            ->get();

        $grid = [];
        foreach ($rows as $t) {
            $grid[$t->x][$t->y] = ['type' => (int)$t->type];
        }
        $this->tiles = $grid;
    }

    public function render()
    {
        return view('livewire.tilemap-preview');
    }
}

<?php
namespace App\Livewire;

use Livewire\Component;
use App\Helpers\MapDatabase\MapModel;
use App\Models\Tile;
use App\Models\ResourceNode;

/**
 * MapEditor Livewire component
 *
 * - Renders a canvas-based map editor via JS
 * - Listens for 'paintTile' events from the client and updates MapModel/DB
 */
class MapEditor extends Component
{
    public $mapId = 1;
    public $mapWidth = 128;
    public $mapHeight = 128;
    public $preview = [];

    protected $listeners = ['paintTile' => 'onPaintTile', 'requestPreview' => 'sendPreview'];

    public function mount($mapId = 1)
    {
        $this->mapId = $mapId;
        $map = MapModel::loadFromDatabase();
        if ($map) {
            $this->mapWidth = $map->width;
            $this->mapHeight = $map->height;
            $this->preview = $map->toColorMatrix();
        }
    }

    public function render()
    {
        return view('livewire.map-editor');
    }

    /**
     * Handle painting a tile from client-side.
     *
     * @param array $payload ['x'=>int,'y'=>int,'terrain'=>string,'resource'=>null|string]
     */
    public function onPaintTile($payload)
    {
        $x = intval($payload['x']);
        $y = intval($payload['y']);
        $terrain = $payload['terrain'] ?? 'grass';
        $resource = $payload['resource'] ?? null;

        $map = MapModel::loadFromDatabase();
        $cell = $map ? $map->getCell($x,$y) : null;

        if ($cell) {
            $cell->terrain = $terrain;
            $cell->resourceType = $resource;
            try { $cell->save(); } catch (\Throwable $e) { /* ignore if not available */ }
        }

        // Also update game's tiles / resource_nodes for immediate effect
        try {
            Tile::updateOrCreate(
                ['x'=>$x,'y'=>$y],
                ['terrain'=>$terrain]
            );
        } catch (\Throwable $e) { }

        try {
            if ($resource) {
                ResourceNode::updateOrCreate(
                    ['x'=>$x,'y'=>$y],
                    ['map_id'=>$map->id,'type'=>$resource,'amount'=>100]
                );
            } else {
                ResourceNode::where('x',$x)->where('y',$y)->delete();
            }
        } catch (\Throwable $e) { }

        $this->emit('tilePainted', ['x'=>$x,'y'=>$y,'terrain'=>$terrain,'resource'=>$resource]);
    }

    /**
     * Provide preview matrix on demand to client for rendering
     */
    public function sendPreview()
    {
        $this->emit('mapPreview', $this->preview);
    }
}

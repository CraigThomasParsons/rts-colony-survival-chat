<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Map;

class HeightmapPreview extends Component
{
    public string $mapId;
    public int $sizeX = 0;
    public int $sizeY = 0;

    /**
     * 
     */
    public function mount(string $mapId)
    {
        $this->mapId = $mapId;
        $map = Map::findOrFail($mapId);
        $this->sizeX = (int) ($map->coordinateX ?? 32);
        $this->sizeY = (int) ($map->coordinateY ?? 32);
    }

    public function render()
    {
        return view('livewire.heightmap-preview');
    }
}

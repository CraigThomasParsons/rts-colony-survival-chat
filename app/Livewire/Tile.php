<?php

namespace App\Livewire;

use Livewire\Component;

class Tile extends Component
{
    /**
     * @var array{map_x:int,map_y:int,type:int|null,name:string|null,has_trees:bool,exists:bool}
     */
    public array $tile = [];

    public function mount(array $tile): void
    {
        $this->tile = $tile;
    }

    public function getTreeEmojiProperty(): string
    {
        return ($this->tile['has_trees'] ?? false) ? '🌲' : '';
    }

    public function getCssClassProperty(): string
    {
        $type = $this->tile['type'] ?? null;

        if (($this->tile['has_trees'] ?? false) || $type === 29) return 'treeTile';
        if ($type === 3 || ($type !== null && $type >= 16 && $type <= 27)) return 'waterTile';
        if ($type === 2 || ($type !== null && $type >= 4 && $type <= 15)) return 'rockTile';
        return 'landTile';
    }

    public function render()
    {
        return view('livewire.tile');
    }
}

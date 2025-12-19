<?php

namespace App\Livewire;

use App\Models\Game;
use App\Models\Map;
use App\Models\Tile;
use Livewire\Component;

class GameMap extends Component
{
    public Game $game;
    public Map $map;

    public function mount(Game $game, Map $map)
    {
        $this->game = $game;
        $this->map = $map;
    }

    public function render()
    {
        // Fetch tiles sorted by Y then X to render in grid order
        // We use a caching key or just fetch. Since it's Livewire render, we might want to cache if it's heavy,
        // but for now direct fetch is fine.
        $tiles = $this->map->tiles()
            ->with(['tileType'])
            ->get();
            
        // Transform into [y][x] grid
        $grid = [];
        foreach ($tiles as $tile) {
            $grid[$tile->coordinateY][$tile->coordinateX] = $tile;
        }

        $buildings = $this->game->buildings()
            ->with('buildingType')
            ->get();

        return view('livewire.game-map', [
            'grid' => $grid,
            'buildings' => $buildings,
        ]);
    }

    public function getBuildingAsset(\App\Models\Building $building)
    {
        // Assuming we have assets in public/assets/buildings/ or similiar
        // For now, returning a generic placeholder or specific if TownHall
        $baseDir = 'assets/kenney-medieval-rts/PNG/Default/Structure/';
        
        if ($building->buildingType->name === \App\Models\BuildingType::TOWN_HALL) {
             return $baseDir . 'medievalStructure_19.png'; // Example TownHall
        }
        
        return $baseDir . 'medievalStructure_01.png'; // Fallback
    }

    public function getTileAsset(Tile $tile): string
    {
        // Path relative to public/
        $baseDir = 'assets/kenney-medieval-rts/PNG/Default/Tile/';
        
        $typeName = $tile->tileType->name ?? 'unknown';
        
        // Default Grass
        $filename = 'medievalTile_57.png'; 

        // Mapping
        if (str_contains($typeName, 'Water')) {
             $filename = 'medievalTile_73.png'; // Water
        } elseif (str_contains($typeName, 'Rock') || str_contains($typeName, 'Cliff')) {
             $filename = 'medievalTile_17.png'; // Rock/Cliff
        } elseif (str_contains($typeName, 'Land')) {
            $filename = 'medievalTile_57.png'; // Grass
        }
        
        // If type itself is a tree base, we might want grass under it
        if ($typeName === 'inner-Tree') {
             $filename = 'medievalTile_57.png';
        }

        return $baseDir . $filename;
    }
    
    public function hasOverlay(Tile $tile): ?string
    {
        // Environment objects (Trees, etc) are in Environment folder
        $envDir = 'assets/kenney-medieval-rts/PNG/Default/Environment/';
        
        // Check for trees
        if ($tile->has_trees || ($tile->tileType->name ?? '') === 'inner-Tree') {
             return $envDir . 'medievalEnvironment_03.png'; // Tree
        }
        return null;
    }
}

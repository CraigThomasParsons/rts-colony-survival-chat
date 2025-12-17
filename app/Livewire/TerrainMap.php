<?php

namespace App\Livewire;

use App\Models\Map;
use App\Models\Tile as TileModel;
use Livewire\Component;

class TerrainMap extends Component
{
    public const DEFAULT_TILE_SIZE = 32;

    public string $mapId;

    /** @var int Number of tiles per axis rendered in the viewport */
    public int $tileSize = self::DEFAULT_TILE_SIZE;

    /** @var int Top-left tile coordinate of the current viewport (world coordinates) */
    public int $offsetX = 0;
    public int $offsetY = 0;

    /** @var array<int, array<int, array>> Two-dimensional tile grid for the current viewport */
    public array $grid = [];

    /** @var array<string, int> Aggregated tile counts for quick stats */
    public array $counts = [
        'land' => 0,
        'water' => 0,
        'mountains' => 0,
        'trees' => 0,
    ];

    /** @var int Cached horizontal tile capacity for the underlying map */
    protected int $maxTileX = 0;

    /** @var int Cached vertical tile capacity for the underlying map */
    protected int $maxTileY = 0;

    /** Allow minimap JS to emit viewport events */
    protected $listeners = [
        'terrain-map:move' => 'onMoveFromMinimap',
        'terrain-map:refresh' => 'loadSlice',
    ];

    public function mount(string $mapId, int $tileSize = self::DEFAULT_TILE_SIZE, int $offsetX = 0, int $offsetY = 0): void
    {
        $this->mapId = $mapId;
        $this->tileSize = $this->sanitizeTileSize($tileSize);
        $this->offsetX = max(0, $offsetX);
        $this->offsetY = max(0, $offsetY);

        $this->hydrateBounds();
        $this->loadSlice();
    }

    public function updatedTileSize(): void
    {
        $this->tileSize = $this->sanitizeTileSize($this->tileSize);
        $this->clampViewport();
        $this->loadSlice();
    }

    public function onMoveFromMinimap(array $payload): void
    {
        $tileX = (int) ($payload['tileX'] ?? 0);
        $tileY = (int) ($payload['tileY'] ?? 0);
        $this->moveViewport($tileX, $tileY);
    }

    public function moveViewport(int $tileX, int $tileY): void
    {
        $this->offsetX = $tileX;
        $this->offsetY = $tileY;
        $this->clampViewport();
        $this->loadSlice();
    }

    public function previousPage(): void
    {
        $this->moveViewport($this->offsetX - $this->tileSize, $this->offsetY);
    }

    public function nextPage(): void
    {
        $this->moveViewport($this->offsetX + $this->tileSize, $this->offsetY);
    }

    public function pageUp(): void
    {
        $this->moveViewport($this->offsetX, $this->offsetY - $this->tileSize);
    }

    public function pageDown(): void
    {
        $this->moveViewport($this->offsetX, $this->offsetY + $this->tileSize);
    }

    public function render()
    {
        return view('livewire.terrain-map');
    }

    protected function hydrateBounds(): void
    {
        $map = Map::find($this->mapId);
        $cellsX = (int) ($map->coordinateX ?? 0);
        $cellsY = (int) ($map->coordinateY ?? 0);

        $this->maxTileX = max(0, $cellsX * 2);
        $this->maxTileY = max(0, $cellsY * 2);
    }

    protected function sanitizeTileSize(int $value): int
    {
        return max(8, min(128, $value));
    }

    protected function clampViewport(): void
    {
        if ($this->maxTileX === 0 || $this->maxTileY === 0) {
            $this->hydrateBounds();
        }

        $maxX = max(0, $this->maxTileX - $this->tileSize);
        $maxY = max(0, $this->maxTileY - $this->tileSize);

        $this->offsetX = max(0, min($this->offsetX, $maxX));
        $this->offsetY = max(0, min($this->offsetY, $maxY));
    }

    protected function loadSlice(): void
    {
        $this->clampViewport();

        $size = $this->tileSize;
        $startX = $this->offsetX;
        $startY = $this->offsetY;

        $grid = [];
        for ($y = 0; $y < $size; $y++) {
            $row = [];
            for ($x = 0; $x < $size; $x++) {
                $row[] = [
                    'map_x' => $startX + $x,
                    'map_y' => $startY + $y,
                    'type' => null,
                    'name' => null,
                    'has_trees' => false,
                    'exists' => false,
                ];
            }
            $grid[] = $row;
        }

        $counts = [
            'land' => 0,
            'water' => 0,
            'mountains' => 0,
            'trees' => 0,
        ];

        $tiles = TileModel::query()
            ->where('map_id', $this->mapId)
            ->whereBetween('mapCoordinateX', [$startX, $startX + $size - 1])
            ->whereBetween('mapCoordinateY', [$startY, $startY + $size - 1])
            ->orderBy('mapCoordinateY')
            ->orderBy('mapCoordinateX')
            ->get(['mapCoordinateX', 'mapCoordinateY', 'tileType_id', 'name', 'has_trees']);

        foreach ($tiles as $tile) {
            $x = (int) $tile->mapCoordinateX - $startX;
            $y = (int) $tile->mapCoordinateY - $startY;

            if ($x < 0 || $y < 0 || $x >= $size || $y >= $size) {
                continue;
            }

            $payload = [
                'map_x' => (int) $tile->mapCoordinateX,
                'map_y' => (int) $tile->mapCoordinateY,
                'type' => $tile->tileType_id !== null ? (int) $tile->tileType_id : null,
                'name' => $tile->name,
                'has_trees' => (bool) $tile->has_trees,
                'exists' => true,
            ];

            $grid[$y][$x] = $payload;
            $counts = $this->incrementCounts($counts, $payload['type'], $payload['has_trees']);
        }

        $this->grid = $grid;
        $this->counts = $counts;
    }

    protected function incrementCounts(array $counts, ?int $type, bool $hasTrees): array
    {
        if ($hasTrees || $type === 29) {
            $counts['trees']++;
            return $counts;
        }

        if ($type === 3 || ($type !== null && $type >= 16 && $type <= 27)) {
            $counts['water']++;
            return $counts;
        }

        if ($type !== null && $type >= 4 && $type <= 15) {
            $counts['mountains']++;
            return $counts;
        }

        $counts['land']++;
        return $counts;
    }
}

<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Cell;
use App\Models\Map;

class HeightmapTable extends Component
{
    /**
     * The UUID of the map whose cells we will render.
     */
    public string $mapId;
    /**
     * Horizontal grid size (number of columns / X dimension).
     */
    public int $sizeX = 0;
    /**
     * Vertical grid size (number of rows / Y dimension).
     */
    public int $sizeY = 0;

    /** @var array<int, array<int, int>> */
    public array $grid = [];

    /**
     * Computed thresholds based on height statistics.
     * waterThreshold: 20% of average height
     * mountainThreshold: 90th percentile of heights
     */
    public int $waterThreshold = 0;
    /** Lower mountain threshold (foothills). */
    public int $mountainThresholdLow = 0;
    /** Upper mountain threshold (peaks). */
    public int $mountainThresholdHigh = 0;

    /**
     * Initialize the component from the provided mapId.
     * Loads the Map record for dimensions and builds the grid once.
     */
    public function mount(string $mapId)
    {
        $this->mapId = $mapId;
        $map = Map::findOrFail($mapId);
        $this->sizeX = (int) ($map->coordinateX ?? 32);
        $this->sizeY = (int) ($map->coordinateY ?? 32);

        $this->loadGrid();
    }

    /**
     * Build a 2D array of heights keyed by [y][x].
     * This keeps access efficient when rendering rows first.
     */
    private function loadGrid(): void
    {
        $cells = Cell::where('map_id', $this->mapId)->get(['coordinateX', 'coordinateY', 'height']);
        $grid = [];
        $allHeights = [];
        foreach ($cells as $cell) {
            $x = (int) $cell->coordinateX;
            $y = (int) $cell->coordinateY;
            $grid[$y][$x] = (int) $cell->height;
            $allHeights[] = (int) $cell->height;
        }
        $this->grid = $grid;

        // Calculate thresholds
        if (!empty($allHeights)) {
            $average = array_sum($allHeights) / count($allHeights);
            $this->waterThreshold = (int) floor($average * 0.2);

            sort($allHeights);
            $count = count($allHeights);
            $idxLow = (int) floor($count * 0.75);
            $idxHigh = (int) floor($count * 0.90);
            $idxLow = max(0, min($idxLow, $count - 1));
            $idxHigh = max(0, min($idxHigh, $count - 1));
            $this->mountainThresholdLow = (int) $allHeights[$idxLow];
            $this->mountainThresholdHigh = (int) $allHeights[$idxHigh];
        }
    }

    /**
     * Render the table view. The blade template iterates over Y then X
     * to output one <td> per cell, colored by height.
     */
    public function render()
    {
        return view('livewire.heightmap-table');
    }
}

<?php

namespace App\Livewire;

use App\Services\SurfacePreviewGenerator;
use Livewire\Component;

class MapGeneratorPreview extends Component
{
    /**
     * Number of columns shown in the preview grid.
     */
    public int $width = 32;

    /**
     * Number of rows shown in the preview grid.
     */
    public int $height = 18;

    /**
     * Optional seed provided by the admin (blank = random seed).
     */
    public string $seed = '';

    /**
     * Two-dimensional array of terrain tokens produced by the generator.
     */
    public array $grid = [];

    /**
     * Terrain counts (water, sand, etc.) for quick stats.
     */
    public array $counts = [];

    /**
     * Seed actually used during the last preview (handy when blank input).
     */
    public ?int $lastSeed = null;

    /**
     * Palette metadata for the Blade view (label + CSS class for each tile).
     */
    public array $palette = [
        'water'  => ['label' => 'Water', 'class' => 'tile-water'],
        'sand'   => ['label' => 'Sand', 'class' => 'tile-sand'],
        'grass'  => ['label' => 'Grass', 'class' => 'tile-grass'],
        'forest' => ['label' => 'Forest', 'class' => 'tile-forest'],
        'hill'   => ['label' => 'Hill', 'class' => 'tile-hill'],
    ];

    /**
     * Validation rules for the preview form.
     */
    protected $rules = [
        'width' => 'required|integer|min:16|max:96',
        'height' => 'required|integer|min:16|max:96',
        'seed' => 'nullable|string|max:32',
    ];

    /**
     * Render the Livewire view.
     */
    public function render()
    {
        return view('livewire.map-generator-preview');
    }

    /**
     * Generate a preview based on the current width/height/seed inputs.
     */
    public function generate(): void
    {
        $this->validate();

        $seedValue = $this->seed !== ''
            ? $this->stringToSeed($this->seed)
            : null;

        $generator = new SurfacePreviewGenerator(
            $this->width,
            $this->height,
            $seedValue,
        );

        $result = $generator->generate();

        $this->grid = $result['grid'];
        $this->counts = $result['counts'];
        $this->lastSeed = $result['meta']['seed'];
    }

    /**
     * Generate a preview with a fresh random seed.
     */
    public function generateRandom(): void
    {
        $this->seed = (string) random_int(1, PHP_INT_MAX);
        $this->generate();
    }

    /**
     * Convert an arbitrary string to a deterministic integer seed.
     */
    protected function stringToSeed(string $value): int
    {
        return abs(crc32($value)) ?: random_int(1, PHP_INT_MAX);
    }
}

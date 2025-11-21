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
     * Palette used by the Blade view (symbol + tailwind class).
     */
    public array $palette = [
        'water'  => ['symbol' => '~', 'class' => 'text-sky-400'],
        'sand'   => ['symbol' => '.', 'class' => 'text-amber-400'],
        'grass'  => ['symbol' => ',', 'class' => 'text-green-400'],
        'forest' => ['symbol' => 'T', 'class' => 'text-emerald-500'],
        'hill'   => ['symbol' => '^', 'class' => 'text-stone-400'],
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

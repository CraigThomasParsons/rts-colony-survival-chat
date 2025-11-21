<?php

namespace App\Services;

/**
 * SurfacePreviewGenerator
 *
 * Lightweight map generator intended for quick previews.
 * Produces a 2-D array of terrain strings without touching the DB.
 */
class SurfacePreviewGenerator
{
    /**
     * Width of the preview grid in tiles.
     */
    protected int $width;

    /**
     * Height of the preview grid in tiles.
     */
    protected int $height;

    /**
     * Seed value driving the pseudo-random generation.
     */
    protected int $seed;

    public function __construct(int $width = 32, int $height = 18, ?int $seed = null)
    {
        $this->width = max(8, $width);
        $this->height = max(8, $height);
        $this->seed = $seed ?? random_int(PHP_INT_MIN, PHP_INT_MAX);

        mt_srand($this->seed);
    }

    /**
     * Generate a 2D terrain matrix along with summary stats.
     */
    public function generate(): array
    {
        $grid = [];
        $counts = [
            'water' => 0,
            'sand' => 0,
            'grass' => 0,
            'forest' => 0,
            'hill' => 0,
        ];

        for ($y = 0; $y < $this->height; $y++) {
            $row = [];
            for ($x = 0; $x < $this->width; $x++) {
                $terrain = $this->terrainFromNoise($this->noise($x, $y));
                $row[] = $terrain;
                $counts[$terrain] = ($counts[$terrain] ?? 0) + 1;
            }
            $grid[] = $row;
        }

        return [
            'grid' => $grid,
            'counts' => $counts,
            'meta' => [
                'seed' => $this->seed,
                'width' => $this->width,
                'height' => $this->height,
            ],
        ];
    }

    /**
     * Simple noise function combining sine/cosine with random jitter.
     */
    protected function noise(int $x, int $y): float
    {
        $sin = sin(($x + $this->seed) / 6.0);
        $cos = cos(($y - $this->seed) / 5.0);
        $rand = mt_rand() / mt_getrandmax();

        return ($sin + $cos + $rand) / 3.0;
    }

    /**
     * Translate a normalized noise value into a terrain identifier.
     */
    protected function terrainFromNoise(float $value): string
    {
        $normalized = ($value + 1) / 2.0;

        if ($normalized < 0.18) {
            return 'water';
        }
        if ($normalized < 0.32) {
            return 'sand';
        }
        if ($normalized < 0.6) {
            return 'grass';
        }
        if ($normalized < 0.8) {
            return 'forest';
        }

        return 'hill';
    }
}

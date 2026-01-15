<?php
namespace App\Helpers\MapGenerators;

/**
 * FaultLineAlgorithm - Classical Fault Line Procedural Heightmap Generator
 * 
 * Implements the fault line algorithm for generating large-scale terrain features.
 * This algorithm creates mountain ranges and valleys by repeatedly displacing
 * the terrain along random fault lines.
 * 
 * Algorithm Overview:
 * 1. Start with a flat heightmap
 * 2. For each iteration:
 *    - Pick a random line across the map
 *    - Determine which side of the line each tile is on
 *    - Increase/decrease height based on side
 *    - Optionally smooth the result
 * 3. Normalize the final heightmap
 */
class FaultLineAlgorithm
{
    /**
     * The 2D heightmap array
     * @var array
     */
    private $heightmap;

    /**
     * Map width in tiles
     * @var int
     */
    private $width;

    /**
     * Map height in tiles
     * @var int
     */
    private $height;

    /**
     * Random seed for reproducibility
     * @var int
     */
    private $seed;

    /**
     * Random number generator state
     * @var int
     */
    private $rngState;

    /**
     * Constructor
     * 
     * @param int $width Width of the heightmap
     * @param int $height Height of the heightmap
     * @param int|null $seed Random seed (null for random)
     */
    public function __construct(int $width, int $height, ?int $seed = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->seed = $seed ?? mt_rand(0, PHP_INT_MAX);
        $this->rngState = $this->seed;

        // Initialize heightmap with zeros
        $this->heightmap = array();
        for ($x = 0; $x < $width; $x++) {
            $this->heightmap[$x] = array_fill(0, $height, 0.0);
        }
    }

    /**
     * Simple seeded random number generator (0.0 to 1.0)
     * Uses a linear congruential generator for reproducibility
     * 
     * @return float Random value between 0.0 and 1.0
     */
    private function random(): float
    {
        // LCG parameters from Numerical Recipes
        $a = 1664525;
        $c = 1013904223;
        $m = 4294967296; // 2^32

        $this->rngState = ($a * $this->rngState + $c) % $m;
        return $this->rngState / $m;
    }

    /**
     * Generate a random integer within a range
     * 
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return int Random integer
     */
    private function randomInt(int $min, int $max): int
    {
        return (int)floor($this->random() * ($max - $min + 1)) + $min;
    }

    /**
     * Generate a random angle in radians (0 to 2π)
     * 
     * @return float Random angle in radians
     */
    private function randomAngle(): float
    {
        return $this->random() * 2 * M_PI;
    }

    /**
     * Determine which side of a line a point is on
     * 
     * Using the cross product formula:
     * side = (x - x0) * dy - (y - y0) * dx
     * 
     * Positive = left side, Negative = right side
     * 
     * @param float $x Point x coordinate
     * @param float $y Point y coordinate
     * @param float $x0 Line point x coordinate
     * @param float $y0 Line point y coordinate
     * @param float $dx Line direction x component
     * @param float $dy Line direction y component
     * @return float The signed distance from the line
     */
    private function getLineSide(float $x, float $y, float $x0, float $y0, float $dx, float $dy): float
    {
        return ($x - $x0) * $dy - ($y - $y0) * $dx;
    }

    /**
     * Apply a single fault line displacement
     * 
     * Finds a random line and displaces terrain on each side
     * 
     * @param float $stepAmount Height change amount per fault line
     * @return void
     */
    private function applyFaultLine(float $stepAmount): void
    {
        // Pick a random point on the map edge or within bounds
        $x0 = $this->random() * $this->width;
        $y0 = $this->random() * $this->height;

        // Pick a random angle for the line direction
        $angle = $this->randomAngle();
        $dx = cos($angle);
        $dy = sin($angle);

        // Apply the displacement to each tile
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $side = $this->getLineSide((float)$x, (float)$y, $x0, $y0, $dx, $dy);

                if ($side > 0) {
                    $this->heightmap[$x][$y] += $stepAmount;
                } else {
                    $this->heightmap[$x][$y] -= $stepAmount;
                }
            }
        }
    }

    /**
     * Apply a simple 3×3 averaging smoothing filter
     * 
     * Reduces noise by averaging each tile with its neighbors
     * 
     * @return void
     */
    private function smooth(): void
    {
        $smoothed = array();
        for ($x = 0; $x < $this->width; $x++) {
            $smoothed[$x] = array_fill(0, $this->height, 0.0);
        }

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $sum = 0.0;
                $count = 0;

                // 3×3 neighborhood (with wrapping at edges)
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        $nx = ($x + $dx + $this->width) % $this->width;
                        $ny = ($y + $dy + $this->height) % $this->height;
                        $sum += $this->heightmap[$nx][$ny];
                        $count++;
                    }
                }

                $smoothed[$x][$y] = $sum / $count;
            }
        }

        $this->heightmap = $smoothed;
    }

    /**
     * Normalize the heightmap to a specific range
     * 
     * Scales all values so the minimum is 0 and maximum is maxHeight
     * 
     * @param float $maxHeight Maximum height value in final map (default 255)
     * @return void
     */
    private function normalize(float $maxHeight = 255.0): void
    {
        // Find min and max values
        $min = PHP_FLOAT_MAX;
        $max = -PHP_FLOAT_MAX;

        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $val = $this->heightmap[$x][$y];
                if ($val < $min) {
                    $min = $val;
                }
                if ($val > $max) {
                    $max = $val;
                }
            }
        }

        // Avoid division by zero
        if (abs($max - $min) < 0.001) {
            $max = $min + 1.0;
        }

        // Scale to range [0, maxHeight]
        $range = $max - $min;
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $normalized = ($this->heightmap[$x][$y] - $min) / $range;
                $this->heightmap[$x][$y] = $normalized * $maxHeight;
            }
        }
    }

    /**
     * Generate the heightmap using the fault line algorithm
     * 
     * @param int $iterations Number of fault lines to apply (default 200)
     * @param float $stepAmount Height change per iteration (default 1.0)
     * @param bool $useSmoothing Whether to smooth after each iteration (default true)
     * @return array The generated 2D heightmap
     */
    public function generate(
        int $iterations = 200,
        float $stepAmount = 1.0,
        bool $useSmoothing = true
    ): array {
        // Reset RNG to seed
        $this->rngState = $this->seed;

        // Apply fault lines
        for ($i = 0; $i < $iterations; $i++) {
            $this->applyFaultLine($stepAmount);

            // Optional smoothing every few iterations
            if ($useSmoothing && ($i % 10 == 0)) {
                $this->smooth();
            }
        }

        // Final smoothing pass
        if ($useSmoothing) {
            $this->smooth();
        }

        // Normalize to 0-255 range
        $this->normalize(255.0);

        return $this->heightmap;
    }

    /**
     * Generate a simple fault map with default settings
     * 
     * Quick convenience method for basic terrain generation
     * 
     * @param int $width Map width
     * @param int $height Map height
     * @param int|null $seed Random seed
     * @return array The generated heightmap
     */
    public static function generateSimple(int $width, int $height, ?int $seed = null): array
    {
        $generator = new self($width, $height, $seed);
        return $generator->generate(iterations: 150, stepAmount: 1.5, useSmoothing: true);
    }

    /**
     * Generate a detailed fault map with custom parameters
     * 
     * @param int $width Map width
     * @param int $height Map height
     * @param int $iterations Number of fault lines (more = more detail)
     * @param float $stepAmount Height change per line (larger = bigger features)
     * @param bool $useSmoothing Whether to smooth the result
     * @param int|null $seed Random seed
     * @return array The generated heightmap
     */
    public static function generateDetailed(
        int $width,
        int $height,
        int $iterations = 200,
        float $stepAmount = 1.0,
        bool $useSmoothing = true,
        ?int $seed = null
    ): array {
        $generator = new self($width, $height, $seed);
        return $generator->generate($iterations, $stepAmount, $useSmoothing);
    }

    /**
     * Get the current seed value
     * 
     * @return int
     */
    public function getSeed(): int
    {
        return $this->seed;
    }

    /**
     * Get the width of the heightmap
     * 
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Get the height of the heightmap
     * 
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Get the current heightmap
     * 
     * @return array
     */
    public function getHeightmap(): array
    {
        return $this->heightmap;
    }

    /**
     * Generate an ASCII visualization of the heightmap
     * 
     * Useful for debugging and visualization
     * 
     * @param int $width Width of ASCII output (default 80 chars)
     * @param int $height Height of ASCII output (default 24 lines)
     * @return string ASCII art representation
     */
    public function getASCIIVisualization(int $width = 80, int $height = 24): string
    {
        $chars = '.,-~=+*#%@';
        $output = '';

        $xScale = $this->width / $width;
        $yScale = $this->height / $height;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $mapX = (int)($x * $xScale);
                $mapY = (int)($y * $yScale);

                $mapX = min($mapX, $this->width - 1);
                $mapY = min($mapY, $this->height - 1);

                $value = $this->heightmap[$mapX][$mapY];
                $normalized = $value / 255.0;
                $charIndex = (int)($normalized * (strlen($chars) - 1));

                $output .= $chars[$charIndex];
            }
            $output .= "\n";
        }

        return $output;
    }

    /**
     * Convert heightmap to hex-encoded string format (for database storage)
     * 
     * @return array Array of hex-encoded height values [x][y]
     */
    public function getHeightmapAsHex(): array
    {
        $hexMap = array();
        for ($x = 0; $x < $this->width; $x++) {
            $hexMap[$x] = array();
            for ($y = 0; $y < $this->height; $y++) {
                $value = (int)round($this->heightmap[$x][$y]);
                $hexMap[$x][$y] = dechex($value);
            }
        }
        return $hexMap;
    }
}

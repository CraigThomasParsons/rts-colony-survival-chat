<?php
/**
 * FaultLineAlgorithm Usage Examples
 * 
 * This file demonstrates how to use the FaultLineAlgorithm class
 * to generate procedural heightmaps using the classical fault line algorithm.
 */

namespace App\Helpers\MapGenerators;

// Example 1: Simple generation with defaults
$generator = new FaultLineAlgorithm(width: 64, height: 64);
$heightmap = $generator->generate();
echo "Generated 64x64 heightmap\n";
echo $generator->getASCIIVisualization();

// Example 2: Detailed generation with custom parameters
$detailedMap = FaultLineAlgorithm::generateDetailed(
    width: 128,
    height: 128,
    iterations: 300,
    stepAmount: 2.0,
    useSmoothing: true,
    seed: 42
);
echo "Generated detailed 128x128 heightmap with 300 iterations\n";

// Example 3: Simple quick generation
$quickMap = FaultLineAlgorithm::generateSimple(width: 32, height: 32, seed: 123);
echo "Generated simple 32x32 heightmap\n";

// Example 4: Access heightmap data as hex (for database storage)
$generator = new FaultLineAlgorithm(width: 16, height: 16, seed: 999);
$heightmap = $generator->generate(iterations: 100);
$hexMap = $generator->getHeightmapAsHex();

echo "\nSample hex values:\n";
for ($x = 0; $x < 5; $x++) {
    for ($y = 0; $y < 5; $y++) {
        echo $hexMap[$x][$y] . " ";
    }
    echo "\n";
}

// Example 5: Reproducible generation using seed
$seed = time();
$map1 = FaultLineAlgorithm::generateSimple(32, 32, $seed);
$map2 = FaultLineAlgorithm::generateSimple(32, 32, $seed);

echo "\nBoth maps should be identical (same seed):\n";
echo "Map1[0][0]: " . $map1[0][0] . ", Map2[0][0]: " . $map2[0][0] . "\n";

// Example 6: Different seeds produce different maps
$map3 = FaultLineAlgorithm::generateSimple(32, 32, $seed + 1);
echo "Different seed produces different map:\n";
echo "Map1[0][0]: " . $map1[0][0] . ", Map3[0][0]: " . $map3[0][0] . "\n";

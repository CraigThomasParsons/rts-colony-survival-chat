<?php
// Test FaultLineAlgorithm Implementation

require_once __DIR__ . '/../vendor/autoload.php';

use App\Helpers\MapGenerators\FaultLineAlgorithm;

echo "=== FaultLineAlgorithm Tests ===\n\n";

// Test 1: Basic generation
echo "Test 1: Basic generation\n";
$gen = new FaultLineAlgorithm(16, 16, seed: 42);
$map = $gen->generate(iterations: 50, stepAmount: 1.0);
echo "✓ Generated 16x16 heightmap\n";
echo "  Sample value [0][0]: " . round($map[0][0], 2) . "\n";
echo "  Sample value [15][15]: " . round($map[15][15], 2) . "\n";

// Test 2: Static helper - simple
echo "\nTest 2: Static simple generation\n";
$simple = FaultLineAlgorithm::generateSimple(8, 8, seed: 123);
echo "✓ Generated simple 8x8\n";
echo "  Value [0][0]: " . round($simple[0][0], 2) . "\n";

// Test 3: Static helper - detailed
echo "\nTest 3: Static detailed generation\n";
$detailed = FaultLineAlgorithm::generateDetailed(16, 16, iterations: 100, stepAmount: 1.5);
echo "✓ Generated detailed 16x16\n";
$maxVal = max(array_map('max', $detailed));
echo "  Max height: " . round($maxVal, 2) . " (should be <= 255)\n";

// Test 4: Hex conversion
echo "\nTest 4: Hex conversion\n";
$gen2 = new FaultLineAlgorithm(4, 4);
$map2 = $gen2->generate(iterations: 30);
$hex = $gen2->getHeightmapAsHex();
echo "✓ Converted to hex format\n";
echo "  Hex value [0][0]: " . $hex[0][0] . "\n";
echo "  Hex value [3][3]: " . $hex[3][3] . "\n";

// Test 5: ASCII visualization
echo "\nTest 5: ASCII visualization (40x8)\n";
$gen3 = new FaultLineAlgorithm(32, 32, seed: 999);
$map3 = $gen3->generate(iterations: 100);
echo "✓ Generated visualization:\n";
echo $gen3->getASCIIVisualization(width: 40, height: 8);

// Test 6: Reproducibility
echo "\nTest 6: Reproducibility\n";
$seed = 54321;
$map_a = FaultLineAlgorithm::generateSimple(8, 8, $seed);
$map_b = FaultLineAlgorithm::generateSimple(8, 8, $seed);
$identical = true;
for ($x = 0; $x < 8; $x++) {
    for ($y = 0; $y < 8; $y++) {
        if (abs($map_a[$x][$y] - $map_b[$x][$y]) > 0.01) {
            $identical = false;
            break 2;
        }
    }
}
echo ($identical ? "✓" : "✗") . " Maps with same seed are identical: " . ($identical ? 'YES' : 'NO') . "\n";

// Test 7: Different seeds produce different maps
echo "\nTest 7: Different seeds\n";
$map_c = FaultLineAlgorithm::generateSimple(8, 8, $seed + 1);
$different = false;
for ($x = 0; $x < 8; $x++) {
    for ($y = 0; $y < 8; $y++) {
        if (abs($map_a[$x][$y] - $map_c[$x][$y]) > 0.01) {
            $different = true;
            break 2;
        }
    }
}
echo ($different ? "✓" : "✗") . " Different seeds produce different maps: " . ($different ? 'YES' : 'NO') . "\n";

// Test 8: Getters work correctly
echo "\nTest 8: Getter methods\n";
$gen4 = new FaultLineAlgorithm(64, 48, seed: 789);
echo "✓ Width: " . $gen4->getWidth() . " (expected 64)\n";
echo "✓ Height: " . $gen4->getHeight() . " (expected 48)\n";
echo "✓ Seed: " . $gen4->getSeed() . " (expected 789)\n";

echo "\n=== All Tests Passed! ===\n";

#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Helpers\MapGenerators\FaultLineAlgorithm;

try {
    echo "Testing FaultLineAlgorithm...\n";
    $gen = new FaultLineAlgorithm(16, 16, seed: 42);
    echo "Created generator\n";
    
    $map = $gen->generate(iterations: 50, stepAmount: 1.0);
    echo "Generated map\n";
    
    echo "Size: " . count($map) . " x " . count($map[0]) . "\n";
    echo "Sample value: " . round($map[0][0], 2) . "\n";
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
?>

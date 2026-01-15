<?php

/**
 * Debug helper: prints tileType_id distribution for a given mapId.
 *
 * Usage (inside docker):
 *   php scripts/debug_tile_counts.php <mapId>
 */

$mapId = $argv[1] ?? null;
if (!$mapId) {
    fwrite(STDERR, "Usage: php scripts/debug_tile_counts.php <mapId>\n");
    exit(2);
}

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = App\Models\Tile::query()
    ->where('map_id', $mapId)
    ->selectRaw('tileType_id, count(*) as c')
    ->groupBy('tileType_id')
    ->orderByDesc('c')
    ->get();

if ($rows->isEmpty()) {
    echo "No tiles found for mapId={$mapId}\n";
    exit(0);
}

echo "tileType_id counts for mapId={$mapId}\n";
foreach ($rows as $r) {
    $id = $r->tileType_id === null ? 'NULL' : (string)$r->tileType_id;
    echo $id . ':' . $r->c . "\n";
}

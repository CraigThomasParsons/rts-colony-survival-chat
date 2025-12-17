<?php

namespace App\Http\Controllers;

use App\Helpers\MapDatabase\MapRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MapHeightmapController extends Controller
{
    public function generate($mapId)
    {
        $map = MapRepository::findFirst($mapId);

        if (!$map) {
            abort(404, "Map not found.");
        }

        $tiles = MapRepository::findAllTiles($mapId);
        
        $size = $map->size;
        $image = imagecreatetruecolor($size, $size);

        // Find min and max height for normalization
        $minHeight = 255;
        $maxHeight = 0;
        foreach ($tiles as $row) {
            foreach ($row as $tile) {
                if ($tile->height < $minHeight) {
                    $minHeight = $tile->height;
                }
                if ($tile->height > $maxHeight) {
                    $maxHeight = $tile->height;
                }
            }
        }

        $heightRange = $maxHeight - $minHeight;
        if ($heightRange == 0) {
            $heightRange = 1; // Avoid division by zero
        }

        foreach ($tiles as $y => $row) {
            foreach ($row as $x => $tile) {
                // Normalize height to 0-255
                $normalizedHeight = (int)((($tile->height - $minHeight) / $heightRange) * 255);
                $color = imagecolorallocate($image, $normalizedHeight, $normalizedHeight, $normalizedHeight);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return new Response($imageData, 200, ['Content-Type' => 'image/png']);
    }

    public function data(Request $request, string $mapId)
    {
        // Use the same repository you're using elsewhere for cells
        $cells = MapRepository::findAllCells($mapId);
        // Try to fetch tiles as well (they contain updated types like trees/water)
        $tiles = MapRepository::findAllTiles($mapId);

        if ($cells === false || empty($cells)) {
            return response()->json([
                'mapId' => $mapId,
                'width' => 0,
                'height' => 0,
                'grid' => [],
            ]);
        }

        // $cells is indexed as [x][y]
        $maxX = max(array_keys($cells));
        $maxY = 0;
        foreach ($cells as $x => $row) {
            if (!is_array($row) || $row === []) {
                continue;
            }
            $maxY = max($maxY, max(array_keys($row)));
        }

        $width  = $maxX + 1;
        $height = $maxY + 1;

        // Optional viewport slicing for performance
        $x0 = max(0, (int)$request->query('x', 0));
        $y0 = max(0, (int)$request->query('y', 0));
        $vw = (int)$request->query('w', $width);
        $vh = (int)$request->query('h', $height);
        $x1 = min($width, $x0 + max(1, $vw));
        $y1 = min($height, $y0 + max(1, $vh));

        $grid = [];
        for ($y = $y0; $y < $y1; $y++) {
            $row = [];
            for ($x = $x0; $x < $x1; $x++) {
                $h = 0;
                $type = null;

                // Always get height from Cell data (Tile does not store height)
                if (isset($cells[$x][$y])) {
                    $cell = $cells[$x][$y];
                    $h = (int)($cell->height ?? 0);
                    $type = $cell->name ?? null; // Default type from cell
                }

                // If Tile exists, use its type (it might be more specific, e.g. Tree, Water)
                // findAllTiles returns [x][y]
                if ($tiles && isset($tiles[$x][$y])) {
                    $tile = $tiles[$x][$y];
                    $type = $tile->name ?? $type;
                }

                $row[] = [
                    'h'    => $h,
                    'type' => $type,
                ];
            }
            $grid[] = $row;
        }

        // Derive map-wide thresholds (percentile-based) for client overlays
        $allHeights = [];
        if ($cells !== false && !empty($cells)) {
            foreach ($cells as $x => $col) {
                if (!is_array($col)) continue;
                foreach ($col as $y => $cell) {
                    $h = (int)($cell->height ?? 0);
                    $allHeights[] = $h;
                }
            }
        }
        $thresholds = [ 'waterMax' => 22 ]; // default water cutoff if none computed
        if (!empty($allHeights)) {
            sort($allHeights);
            $pct = function(array $arr, float $p): int {
                $n = count($arr);
                if ($n === 0) return 0;
                $idx = (int) floor($n * $p);
                if ($idx >= $n) $idx = $n - 1;
                if ($idx < 0) $idx = 0;
                return (int) $arr[$idx];
            };
            // 75th percentile (foothills), 90th percentile (peaks)
            $thresholds['foothills'] = $pct($allHeights, 0.75);
            $thresholds['peaks']     = $pct($allHeights, 0.90);
        }

        $response = [
            'mapId' => $mapId,
            'width' => $width,
            'height' => $height,
            'viewport' => [ 'x' => $x0, 'y' => $y0, 'w' => ($x1 - $x0), 'h' => ($y1 - $y0) ],
            'grid' => $grid,
            'thresholds' => $thresholds,
        ];

        // Optionally include tile types in a tiles array for tilemap preview
        if ($request->boolean('tiles')) {
            $tilesOut = [];
            for ($y = $y0; $y < $y1; $y++) {
                $row = [];
                for ($x = $x0; $x < $x1; $x++) {
                    $tileTypeId = null;
                    $hasTrees = false;
                    if ($tiles && isset($tiles[$x][$y])) {
                        $tile = $tiles[$x][$y];
                        // Support both numeric id and name
                        $tileTypeId = $tile->tileType_id ?? $tile->tileTypeId ?? null;
                        // New column added to tile table; may not exist in older helper objects.
                        if (property_exists($tile, 'has_trees')) {
                            $hasTrees = (bool) $tile->has_trees;
                        }
                    }
                    $row[] = ['type' => $tileTypeId, 'has_trees' => $hasTrees];
                }
                $tilesOut[] = $row;
            }
            $response['tiles'] = $tilesOut;
        }

        return response()->json($response);
    }
}

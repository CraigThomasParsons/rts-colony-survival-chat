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

    public function data(string $mapId)
    {
        // Use the same repository you're using elsewhere for cells
        $cells = MapRepository::findAllCells($mapId);
        // Try to fetch tiles as well (they contain updated types like trees/water)
        // findAllTiles returns [y][x]
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

        $grid = [];
        for ($y = 0; $y < $height; $y++) {
            $row = [];
            for ($x = 0; $x < $width; $x++) {
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

        return response()->json([
            'mapId' => $mapId,
            'width' => $width,
            'height' => $height,
            'grid' => $grid,
        ]);
    }
}

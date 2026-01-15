<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Map;
use App\Models\MapStatus;

class MapAiController extends Controller
{
    /**
     * Suggest the next map generation step with a brief rationale.
     */
    public function suggest(string $mapId)
    {
        $map = Map::find($mapId);
        if (!$map) {
            return response()->json(['error' => 'Map not found'], 404);
        }

        $state = $map->state ?? MapStatus::CREATED_EMPTY;
        $suggestion = [
            'mapId' => $mapId,
            'state' => $state,
            'is_generating' => (bool) ($map->is_generating ?? false),
            'nextRoute' => null,
            'title' => 'Proceed to next generation step',
            'rationale' => 'Continue the pipeline to transform cells into tiles, then trees, then water and mountains.',
            'params' => [],
        ];

        // Simple state-based next step mapping
        $mapping = [
            MapStatus::CREATED_EMPTY => ["/Map/step1/{$mapId}/", 'Initialize cells with height map'],
            MapStatus::CELL_PROCESSING_STARTED => ["/Map/step1/{$mapId}/", 'Finish cell generation before continuing'],
            MapStatus::CELL_PROCESSING_FINNISHED => ["/Map/step2/{$mapId}/", 'Create tiles from cells'],
            MapStatus::TILE_PROCESSING_STARTED => ["/Map/step2/{$mapId}/", 'Finish tile processing'],
            MapStatus::TILE_PROCESSING_STOPPED => [route('mapgen.step3', ['mapId' => $mapId]), 'Run tree distribution (first pass)'],
            MapStatus::TREE_FIRST_STEP => ["/Map/treeStep2/{$mapId}/", 'Refine trees (hole punching + smoothing)'],
            MapStatus::TREE_2ND_COMPLETED => ["/Map/treeStep3/{$mapId}/", 'Final tree smoothing pass'],
            MapStatus::TREE_3RD_STARTED => ["/Map/step4/{$mapId}/", 'Run water processing'],
            MapStatus::TREE_GEN_COMPLETED => ["/Map/step4/{$mapId}/", 'Run water processing'],
        ];

        if (isset($mapping[$state])) {
            [$route, $why] = $mapping[$state];
            $suggestion['nextRoute'] = $route;
            $suggestion['rationale'] = $why;
        }

        // If water done, suggest mountains default parameter
        if ($state === MapStatus::TREE_GEN_COMPLETED) {
            $suggestion['title'] = 'Add mountains';
            $suggestion['params'] = ['mountainLine' => 400];
        }

        return response()->json($suggestion);
    }
}

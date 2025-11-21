<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Map;
use App\Services\MapGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class GameController extends Controller
{
    /**
     * Create a new game, generate a map, and redirect.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|integer|min:32|max:128',
            'height' => 'required|integer|min:32|max:128',
            'seed' => 'nullable|integer',
        ]);

        $game = Game::create([
            'name' => $validated['name'],
        ]);

        $map = Map::create([
            'game_id' => $game->id,
            'width' => $validated['width'],
            'height' => $validated['height'],
        ]);

        $generator = new MapGenerator(
            cellWidth: $validated['width'],
            cellHeight: $validated['height'],
            seed: $validated['seed']
        );

        $generator->generate($map);

        // For now, redirect to the main menu. Later, this could redirect to the game screen.
        return Redirect::route('main.entrance')->with('status', 'Game created successfully!');
    }
}
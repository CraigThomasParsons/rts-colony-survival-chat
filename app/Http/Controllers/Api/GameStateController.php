<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameStateController extends Controller
{
    public function initial(Request $request)
    {
        // later: load from DB
        return response()->json([
            'resources' => [
                'wood' => 100,
                'stone' => 100,
                'gold' => 100,
            ],
            'workers' => [
                ['id' => 1, 'x' => 5, 'y' => 5],
            ],
        ]);
    }

    public function sync(Request $request)
    {
        // later: return diff since timestamp
        return response()->json([
            'events' => [],
        ]);
    }

    public function moveWorker(Request $request)
    {
        // later: validate / store / broadcast
        return response()->json(['ok' => true]);
    }
}

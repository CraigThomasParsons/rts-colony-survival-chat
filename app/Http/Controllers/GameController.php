<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Map;
use App\Jobs\RunMapGenerationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;

class GameController extends Controller
{
    /**
     * Create a new game and redirect to the map-generation seed form.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "width" => "required|integer|min:32|max:128",
            "height" => "required|integer|min:32|max:128",
        ]);

        $game = Game::create([
            "name" => $validated["name"],
        ]);

        $map = Map::create([
            "game_id" => $game->id,
            "width" => $validated["width"],
            "height" => $validated["height"],
        ]);

        return Redirect::route("game.mapgen.form", ["mapId" => $map->id])->with(
            "status",
            "Game created. Continue to map generation to provide a seed.",
        );
    }

    /**
     * Show a simple map generation form where the user provides a seed.
     */
    public function mapGenForm($mapId)
    {
        $map = Map::findOrFail($mapId);

        return view("game.mapgen", [
            "map" => $map,
        ]);
    }

    /**
     * Start the map generation artisan commands in the background for the given map.
     */
    public function mapGenStart(Request $request, $mapId)
    {
        $validated = $request->validate([
            "seed" => "nullable|integer",
        ]);

        $map = Map::findOrFail($mapId);

        if (isset($validated["seed"]) && $validated["seed"] !== null) {
            $map->seed = $validated["seed"];
            $map->save();
        }

        $steps = [
            "map:1init",
            "map:2firststep-tiles",
            "map:3mountain",
            "map:4water",
        ];

        $jobs = [];
        foreach ($steps as $step) {
            $jobs[] = new RunMapGenerationStep($map->id, $step);
        }

        Bus::chain($jobs)->dispatch();

        return Redirect::route("game.mapgen.progress", [
            "mapId" => $map->id,
        ])->with(
            "status",
            "Map generation queued. Open progress page to watch logs.",
        );
    }

    /**
     * Show a progress page that tails the map generation log.
     */
    public function mapGenProgress($mapId)
    {
        return view("game.progress", ["mapId" => $mapId]);
    }

    /**
     * Server-Sent Events stream that emits new lines from the map generation log.
     */
    public function mapGenProgressStream($mapId)
    {
        $logFile = storage_path("logs/mapgen-{$mapId}.log");

        return new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($logFile) {
                $lastSize = 0;

                echo ": connected\n\n";
                @ob_flush();
                @flush();

                while (!connection_aborted()) {
                    clearstatcache(true, $logFile);

                    if (!file_exists($logFile)) {
                        sleep(1);
                        continue;
                    }

                    $size = filesize($logFile);

                    if ($size > $lastSize) {
                        $fp = fopen($logFile, "r");
                        if ($fp !== false) {
                            fseek($fp, $lastSize);
                            while (($line = fgets($fp)) !== false) {
                                $line = rtrim($line, "\r\n");
                                echo "data: {$line}\n\n";
                                @ob_flush();
                                @flush();
                            }
                            $lastSize = ftell($fp);
                            fclose($fp);
                        }
                    }

                    sleep(1);
                }
            },
            200,
            [
                "Content-Type" => "text/event-stream",
                "Cache-Control" => "no-cache",
                "X-Accel-Buffering" => "no",
            ],
        );
    }
}

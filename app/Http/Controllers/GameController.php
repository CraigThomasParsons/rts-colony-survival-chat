<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Map;
use App\Services\MapGenerator;
use App\Jobs\RunMapGenerationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;

class GameController extends Controller
{
    /**
     * Create a new game and redirect to the map-generation seed form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "width" => "required|integer|min:32|max:128",
            "height" => "required|integer|min:32|max:128",
        ]);

        // Create the Game record
        $game = Game::create([
            "name" => $validated["name"],
        ]);

        // Create an empty Map record that will be filled by the background generator steps.
        $map = Map::create([
            "name" => "{$validated['name']} Map",
            "description" => "Queued for generation (seed pending)",
            "coordinateX" => $validated["width"],
            "coordinateY" => $validated["height"],
            "mapstatuses_id" => null,
        ]);

        // Redirect the user to a simple map generation page where they can enter a seed.
        // The form will POST to GameController::mapGenStart to trigger background artisan steps.
        return Redirect::route("game.mapgen.form", ["mapId" => $map->id])->with(
            "status",
            "Game created. Continue to map generation to provide a seed.",
        );
    }

    /**
     * Show a simple map generation form where the user provides a seed.
     *
     * @param int $mapId
     * @return \Illuminate\View\View
     */
    public function mapGenForm($mapId)
    {
        $map = Map::findOrFail($mapId);

        // Note: view('game.mapgen') should be created in resources/views/game/mapgen.blade.php
        // It should present a simple form that POSTs to route name 'game.mapgen.start'.
        return view("game.mapgen", [
            "map" => $map,
        ]);
    }

    /**
     * Start the map generation artisan commands in the background for the given map.
     * Commands are executed in sequence in a single background shell so steps run one after another.
     *
     * @param Request $request
     * @param int $mapId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mapGenStart(Request $request, $mapId)
    {
        $validated = $request->validate([
            "seed" => "nullable|integer",
        ]);

        $map = Map::findOrFail($mapId);

        // Save the seed on the map record (optional, helps debugging).
        if (isset($validated["seed"]) && $validated["seed"] !== null) {
            $map->seed = $validated["seed"];
            $map->save();
        }

        // Map generation commands that accept a map id as an argument.
        $steps = [
            "map:1init",
            "map:2firststep-tiles",
            "map:3mountain",
            "map:4water",
        ];

        // Build job instances for each step. Run them as a chain so they execute sequentially.
        $jobs = [];
        foreach ($steps as $step) {
            $jobs[] = new RunMapGenerationStep($map->id, $step);
        }

        // Dispatch the chain to the default queue. Workers will run steps in order and append output to the mapgen log.
        Bus::chain($jobs)->dispatch();

        // Redirect the user to the progress page where they can watch log output in real time.
        return Redirect::route("game.mapgen.progress", [
            "mapId" => $map->id,
        ])->with(
            "status",
            "Map generation queued. Open progress page to watch logs.",
        );
    }

    /**
     * Show a progress page that tails the map generation log.
     *
     * @param int $mapId
     * @return \Illuminate\View\View
     */
    public function mapGenProgress($mapId)
    {
        return view("game.progress", ["mapId" => $mapId]);
    }

    /**
     * Server-Sent Events stream that emits new lines from the map generation log.
     *
     * @param int $mapId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function mapGenProgressStream($mapId)
    {
        $logFile = storage_path("logs/mapgen-{$mapId}.log");

        return new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($logFile) {
                $lastSize = 0;

                // Send a comment to establish the SSE connection
                echo ": connected\n\n";
                @ob_flush();
                @flush();

                while (!connection_aborted()) {
                    // Ensure fresh file metadata
                    clearstatcache(true, $logFile);

                    if (!file_exists($logFile)) {
                        // No log yet, wait a bit
                        sleep(1);
                        continue;
                    }

                    $size = filesize($logFile);

                    if ($size > $lastSize) {
                        $fp = fopen($logFile, "r");
                        if ($fp !== false) {
                            // Seek to the last read position and stream new lines
                            fseek($fp, $lastSize);
                            while (($line = fgets($fp)) !== false) {
                                $line = rtrim($line, "\r\n");
                                // Emit a single SSE data event per log line
                                echo "data: {$line}\n\n";
                                @ob_flush();
                                @flush();
                            }
                            $lastSize = ftell($fp);
                            fclose($fp);
                        }
                    }

                    // Brief sleep to avoid busy loop
                    sleep(1);
                }
            },
            200,
            [
                "Content-Type" => "text/event-stream",
                "Cache-Control" => "no-cache",
                // Disable buffering on some proxies
                "X-Accel-Buffering" => "no",
            ],
        );
    }
}

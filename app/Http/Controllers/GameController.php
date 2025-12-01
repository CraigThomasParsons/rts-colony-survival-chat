<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Map;
use App\Services\MapGenerator;
use App\Jobs\RunMapGenerationStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;

class GameController extends Controller
{
    /**
     * Simple GET endpoint so visiting /game shows a lightweight index
     * with a create form and existing games list. Avoids MethodNotAllowed
     * for GET /game which is currently only POST create.
     */
    public function index()
    {
        $games = Game::with('maps')->orderByDesc('created_at')->limit(25)->get();
        return view('game.index', [
            'games' => $games,
        ]);
    }
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

        // Attach the map to the game via pivot (many-to-many)
        try {
            $game->maps()->attach($map->id);
        } catch (\Throwable $e) {
            // ignore if duplicate, relation may already exist
        }

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
     * Commands are executed in sequence using Laravel job chains.
     * Each step processes a different aspect of map generation.
     *
     * @param Request $request
     * @param int $mapId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mapGenStart(Request $request, $mapId)
    {
        $validated = $request->validate([
            "seed" => "nullable|integer",
            "mountainLine" => "nullable|integer|min:50|max:255",
        ]);

        $map = Map::findOrFail($mapId);

        // Concurrency guard: refuse to start if already generating.
        if ($map->is_generating) {
            return Redirect::route("game.mapgen.progress", ["mapId" => $map->id])
                ->with("status", "Map generation already in progress for map {$map->id}. No new chain started.");
        }

        // Save the seed on the map record (optional, helps debugging).
        if (isset($validated["seed"]) && $validated["seed"] !== null) {
            $map->seed = $validated["seed"];
            $map->save();
        }

        // Complete map generation pipeline with all steps:
        // 1. Height map initialization and cell generation
        // 2. Tile processing based on cell data
        // 3a-3c. Tree processing (3 steps with Conway's Game of Life)
        // 4. Water tile processing
        // 5. Mountain ridge processing
        $mountainLine = $validated["mountainLine"] ?? 150;
        
        $steps = [
            "map:1init",              // Step 1: Height map and cells
            "map:2firststep-tiles",   // Step 2: Tile processing
            "map:3tree-step1",        // Step 3a: First tree algorithm
            "map:3tree-step2",        // Step 3b: Second tree algorithm
            "map:3tree-step3",        // Step 3c: Final tree algorithm
            "map:4water",             // Step 4: Water processing
            "map:5mountain",          // Step 5: Mountain ridges
        ];

        // Build job instances for each step. Run them as a chain so they execute sequentially.
        $jobs = [];
        foreach ($steps as $step) {
            $jobs[] = new RunMapGenerationStep($map->id, $step);
        }

        // Dispatch the chain to the default queue. Workers will run steps in order and append output to the mapgen log.
    // Mark map as generating before dispatching chain.
    $map->is_generating = true;
    $map->save();

    // Append a terminal unlock job that clears is_generating.
    $finalUnlockJob = new \App\Jobs\FinalizeMapGeneration($map->id);
    $jobs[] = $finalUnlockJob;

    Bus::chain($jobs)->dispatch();

        // Redirect the user to the progress page where they can watch log output in real time.
        return Redirect::route("game.mapgen.progress", [
            "mapId" => $map->id,
        ])->with(
            "status",
            "Map generation queued (7 steps). Open progress page to watch logs.",
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
        // Keep PHP alive indefinitely for long-running SSE
        @set_time_limit(0);
        // Prevent Laravel from buffering output
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        $logFile = storage_path("logs/mapgen-{$mapId}.log");

        return new \Symfony\Component\HttpFoundation\StreamedResponse(
            function () use ($logFile) {
                $lastSize = 0;
                $lastHeartbeat = time();

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

                    // Heartbeat to keep proxies and client alive (comment line in SSE)
                    if (time() - $lastHeartbeat >= 15) {
                        echo ": ping\n\n";
                        @ob_flush();
                        @flush();
                        $lastHeartbeat = time();
                    }

                    // Brief sleep to avoid busy loop
                    usleep(200_000); // 200ms
                }
            },
            200,
            [
                "Content-Type" => "text/event-stream",
                "Cache-Control" => "no-cache",
                // Disable buffering on some proxies
                "X-Accel-Buffering" => "no",
                // Explicitly disable Nginx/fastcgi buffering when honored
                "X-Content-Type-Options" => "nosniff",
            ],
        );
    }

    /**
     * Load page: list existing games with their maps.
     *
     * @return \Illuminate\View\View
     */
    public function loadList()
    {
        // Fetch games with their maps via many-to-many relation
        $games = Game::with('maps')->orderByDesc('created_at')->get();

        return view('game.load', [ 'games' => $games ]);
    }

    /**
     * Standalone page showing a game's maps in a DataTable.
     */
    public function mapsTable(Game $game)
    {
        return view('game.maps', ['game' => $game]);
    }

    /**
     * JSON data for a game's maps DataTable.
     */
    public function mapsTableData(Game $game)
    {
        $maps = $game->maps()->select(['map.id', 'map.name', 'map.description', 'map.coordinateX', 'map.coordinateY', 'map.created_at'])->get();
        return DataTables::of($maps)
            ->addColumn('actions', function ($map) {
                return '<a class="btn btn-primary" href="'.url('/Map/load/'.$map->id.'/').'">Load</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}

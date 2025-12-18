<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Map;
use App\Services\MapGenerator;
use App\Jobs\RunMapGenerationStep;
use App\Jobs\ValidateGeneratedMap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Throwable;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

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

        // Create the Game record,
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
            "Game created. Continue to map generation when validation completed.",
        );
    }

    /**
     * Show a simple map generation form where the user provides a seed.
     *
    * @param string $mapId
     * @return \Illuminate\View\View
     */
    public function mapGenForm(string $mapId)
    {
        $map = Map::findOrFail($mapId);
        $gameId = $map->games()->orderBy('games.created_at')->value('games.id');

        // Note: view('game.mapgen') should be created in resources/views/game/mapgen.blade.php
        // It should present a simple form that POSTs to route name 'game.mapgen.start'.
        return view("game.mapgen", [
            "map" => $map,
            "gameId" => $gameId,
        ]);
    }

    /**
     * Start the map generation artisan commands in the background for the given map.
     * Commands are executed in sequence using Laravel job chains.
     * Each step processes a different aspect of map generation.
     *
     * @param Request $request
    * @param string $mapId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function mapGenStart(Request $request, string $mapId)
    {
        $validated = $request->validate([
            "mountainLine" => "nullable|integer|min:50|max:255",
        ]);

        $map = Map::findOrFail($mapId);

        // Concurrency guard: refuse to start if already generating.
        if ($map->is_generating) {
            return Redirect::route("game.mapgen.progress", ["mapId" => $map->id])
                ->with("status", "Map generation already in progress for map {$map->id}. No new chain started.");
        }

        // Keep backend support for explicit seeds but default to UUID when missing.
        $providedSeed = $request->input('seed');

        $map->seed = (string) ($providedSeed ?? $map->seed ?? $map->id);
        $map->save();

        // Complete map generation pipeline with all steps:
        // 1. Height map initialization and cell generation
        // 2. Tile processing based on cell data
        // 3a-3c. Tree processing (3 steps with Conway's Game of Life)
        // 4. Water tile processing
        // 5. Mountain ridge processing
        $mountainLine = $validated["mountainLine"] ?? 150;
        
        // Limit to 300 seconds (5 minutes) just in case, but usually 0 is fine for CLI.
        // Since we are running sync, we don't want the request to time out.
        set_time_limit(0);

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

        // Mark map as generating before dispatching chain.
        $map->is_generating = true;
        // Task lifecycle: map begins generating here.
        $map->status = 'generating';
        $map->validated_at = null;
        $map->validation_errors = null;
        $map->save();

        // Create an initial log line immediately so the progress page has something to stream.
        // (If jobs never start, we'll still have a clue that dispatch happened.)
        $logFile = storage_path("logs/mapgen-{$map->id}.log");
        try {
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @file_put_contents(
                $logFile,
                '[' . date('Y-m-d H:i:s') . "] Queued map generation chain for map {$map->id}\n",
                FILE_APPEND | LOCK_EX,
            );
        } catch (Throwable $e) {
            // Best-effort only.
        }

        // Final validation step (sets ready/failed and clears is_generating).
        $jobs[] = new ValidateGeneratedMap($map->id);

        try {
            Bus::chain($jobs)->catch(function (Throwable $e) use ($map) {
                // If any step in the chain fails, ensure the lifecycle updates so UI doesn't stall.
                $fresh = Map::find($map->id);
                if (!$fresh) {
                    return;
                }
                $fresh->status = 'failed';
                $fresh->is_generating = false;
                $fresh->validation_errors = [
                    'Map generation pipeline failed',
                    $e->getMessage(),
                ];
                $fresh->save();

                try {
                    $logFile = storage_path("logs/mapgen-{$fresh->id}.log");
                    @file_put_contents(
                        $logFile,
                        '[' . date('Y-m-d H:i:s') . "] !!! Map generation pipeline failed: {$e->getMessage()}\n",
                        FILE_APPEND | LOCK_EX,
                    );
                } catch (Throwable $ignored) {
                }
            })->dispatch();
        } catch (Throwable $e) {
            // Dispatch itself failed—fail fast.
            $map->status = 'failed';
            $map->is_generating = false;
            $map->validation_errors = [
                'Failed to dispatch map generation chain',
                $e->getMessage(),
            ];
            $map->save();

            return Redirect::route("game.mapgen.form", ["mapId" => $map->id])
                ->with('status', 'Failed to queue map generation. Check logs for details.');
        }

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
    * @param string $mapId
     * @return \Illuminate\View\View
     */
    public function mapGenProgress(string $mapId)
    {
        return view("game.progress", ["mapId" => $mapId]);
    }

    /**
     * Server-Sent Events stream that emits new lines from the map generation log.
     *
    * @param string $mapId
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function mapGenProgressStream(string $mapId)
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
     * Lightweight JSON progress endpoint for the map generation pipeline.
     *
     * This parses storage/logs/mapgen-<mapId>.log and counts completed steps
     * by matching lines like:
     *   === END map:1init (exit code: 0) ===
     *
     * Response shape:
     * {
     *   ok: bool,
     *   mapId: string,
     *   exists: bool,
     *   bytes: int,
     *   completed: int,
     *   total: int,
     *   percent: int,
     *   completedSteps: string[],
     *   lastMarker: string|null
     * }
     */
    public function mapGenLogProgress(string $mapId): JsonResponse
    {
        $map = Map::find($mapId);

        // Keep total in sync with mapGenStart(). We include validation as the final step.
        $total = 8; // 7 generation steps + 1 validation

        $logFile = storage_path("logs/mapgen-{$mapId}.log");
        if (!file_exists($logFile)) {
            return response()->json([
                'ok' => true,
                'mapId' => (string) $mapId,
                'exists' => false,
                'bytes' => 0,
                'completed' => 0,
                'total' => $total,
                'percent' => 0,
                'completedSteps' => [],
                'lastMarker' => null,
                'mapStatus' => $map?->status,
                'isGenerating' => (bool) ($map?->is_generating),
            ]);
        }

        $bytes = @filesize($logFile) ?: 0;
        $contents = @file_get_contents($logFile);
        if ($contents === false) {
            return response()->json([
                'ok' => false,
                'mapId' => (string) $mapId,
                'exists' => true,
                'bytes' => $bytes,
                'error' => 'Unable to read log file',
                'mapStatus' => $map?->status,
                'isGenerating' => (bool) ($map?->is_generating),
            ], 500);
        }

        $completed = [];
        $lastMarker = null;

        // Match both timestamped and non-timestamped variants.
        // Example line from RunMapGenerationStep:
        //   [2025-12-14 12:34:56] === END map:1init (exit code: 0) ===
        if (preg_match_all('/===\\s*END\\s+([^=]+?)\\s*(?:\\(exit code:\\s*\\d+\\))?\\s*===/m', $contents, $matches)) {
            foreach ($matches[1] as $m) {
                $step = trim($m);
                $completed[$step] = true;
                $lastMarker = $step;
            }
        }

        // Heuristic: if validation job writes anything like "status=ready" it won't be an END marker.
        // Instead, we treat lifecycle state as authoritative for counting validation completion.
        $validationDone = in_array($map?->status, ['ready', 'active', 'failed'], true) || !empty($map?->validated_at);

        $completedSteps = array_keys($completed);
        sort($completedSteps);

        $completedCount = count($completedSteps);
        if ($validationDone) {
            $completedCount = min($total, $completedCount + 1);
        }

        $percent = (int) floor(($completedCount / max($total, 1)) * 100);

        return response()->json([
            'ok' => true,
            'mapId' => (string) $mapId,
            'exists' => true,
            'bytes' => $bytes,
            'completed' => $completedCount,
            'total' => $total,
            'percent' => max(0, min(100, $percent)),
            'completedSteps' => $completedSteps,
            'lastMarker' => $lastMarker,
            'mapStatus' => $map?->status,
            'isGenerating' => (bool) ($map?->is_generating),
        ]);
    }

    /**
     * Return live queue status: pending jobs, failed jobs, current job info.
     *
     * @param string $mapId
     * @return JsonResponse
     */
    public function queueStatus(string $mapId): JsonResponse
    {
        $pendingCount = DB::table('jobs')->count();
        $failedCount = DB::table('failed_jobs')->count();
        
        // Get the oldest pending job (if any)
        $currentJob = DB::table('jobs')
            ->orderBy('id', 'asc')
            ->first();
        
        $jobInfo = null;
        if ($currentJob) {
            $payload = json_decode($currentJob->payload, true);
            $jobInfo = [
                'id' => $currentJob->id,
                'queue' => $currentJob->queue,
                'attempts' => $currentJob->attempts,
                'displayName' => $payload['displayName'] ?? 'Unknown',
                'available_at' => date('Y-m-d H:i:s', $currentJob->available_at),
            ];
        }
        
        // Get most recent failed job for this map
        $recentFailure = DB::table('failed_jobs')
            ->where('payload', 'like', '%' . $mapId . '%')
            ->orderBy('id', 'desc')
            ->first();
        
        $failureInfo = null;
        if ($recentFailure) {
            $failureInfo = [
                'id' => $recentFailure->id,
                'queue' => $recentFailure->queue,
                'failed_at' => $recentFailure->failed_at,
                'exception' => substr($recentFailure->exception, 0, 200) . '...',
            ];
        }
        
        return response()->json([
            'ok' => true,
            'pending' => $pendingCount,
            'failed' => $failedCount,
            'currentJob' => $jobInfo,
            'recentFailure' => $failureInfo,
        ]);
    }

    /**
     * Task 3: Start a map (ready -> active) and redirect into the game view.
     */
    public function startMap(Map $map)
    {
        // I'm not sure if this check for null is necessary, I usually consider that a bug that there isn't a default value.
        if (($map->status ?? null) !== 'ready') {
            return Redirect::route('game.mapgen.progress', ['mapId' => $map->id])
                ->with('status', "Map {$map->id} is not ready to start (status='{$map->status}').");
        }

        $map->status = 'active';
        $map->started_at = now();
        $map->save();

        $gameId = $map->games()->orderBy('games.created_at')->value('games.id');
        if (!$gameId) {
            // If the map isn't attached to a game, fall back to map generation progress.
            return Redirect::route('game.mapgen.progress', ['mapId' => $map->id])
                ->with('status', "Map {$map->id} started, but no owning game was found.");
        }

        // Preferred entrypoint is now the game id.
        return Redirect::route('game.view', ['game' => $gameId])
            ->with('status', "Map {$map->id} started.");
    }

    /**
     * Task 3 (preferred): Start a game by its primary key.
     * Picks a ready map for that game, marks it active, and redirects to the game view.
     */
    public function startGame(Game $game)
    {
        $map = $game->maps()
            ->where('status', 'ready')
            ->orderByDesc('validated_at')
            ->first();

        if (!$map) {
            return Redirect::route('game.index')
                ->with('status', "Game {$game->id} has no ready map to start.");
        }

        $map->status = 'active';
        $map->started_at = now();
        $map->save();

        return Redirect::route('game.view', ['game' => $game->id])
            ->with('status', "Game {$game->id} started using map {$map->id}.");
    }

    /**
     * Game view route handler.
     * Route param is the Game primary key; we load the game's active map (or fall back).
     */
    public function view(Game $game)
    {
        $map = $game->maps()
            ->where('status', 'active')
            ->orderByDesc('started_at')
            ->first();

        if (!$map) {
            // No active map yet - fall back to most recent ready map.
            $map = $game->maps()
                ->where('status', 'ready')
                ->orderByDesc('validated_at')
                ->first();
        }

        if (!$map) {
            return Redirect::route('game.index')
                ->with('status', "Game {$game->id} has no maps yet.");
        }

        if (($map->status ?? null) !== 'active') {
            Log::info('Game view accessed for non-active map', [
                'gameId' => $game->id,
                'mapId' => $map->id,
                'status' => $map->status,
            ]);
        }

        return view('game.screen', [
            'game' => $game,
            'map' => $map,
        ]);
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

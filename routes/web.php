<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameViewController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MapController;
// Livewire game screen route: wrap component in a blade view to avoid invalid route action
// Livewire Game Screen Component
// Correct namespace for Livewire components (App\Livewire\..., not App\Http\Livewire)
use App\Livewire\GameScreen as LivewireGameScreen;

Route::get('/feudal-frontiers', LivewireGameScreen::class)->name('game.screen');

Route::get("/", function () {
    return view("mainEntrance");
})->name("main.entrance");

// Main Menu Routes
Route::get("/new-game", function () {
    return view("game.new");
})
    ->middleware("auth")
    ->name("game.new");

// Game index & creation
Route::get('/game', [GameController::class, 'index'])->middleware('auth')->name('game.index');
Route::post('/game', [GameController::class, 'create'])->middleware('auth')->name('game.create');

Route::get("/game/{mapId}/mapgen", [GameController::class, "mapGenForm"])
    ->middleware("auth")
    ->name("game.mapgen.form");
Route::post("/game/{mapId}/mapgen", [GameController::class, "mapGenStart"])
    ->middleware("auth")
    ->name("game.mapgen.start");

//
// Progress view & stream for map generation logs
// - GET  /game/{mapId}/progress         -> shows the progress page (tails the log via SSE or AJAX)
// - GET  /game/{mapId}/progress/stream  -> server-sent events endpoint that streams log lines
//
// Progress view & stream: gate unauthenticated access only in local env
if (app()->environment(['local', 'development'])) {
    Route::get("/game/{mapId}/progress", [GameController::class, "mapGenProgress"])
        ->name("game.mapgen.progress");
    Route::get("/game/{mapId}/progress/stream", [
        GameController::class,
        "mapGenProgressStream",
    ])
        ->name("game.mapgen.progress.stream");
} else {
    Route::get("/game/{mapId}/progress", [GameController::class, "mapGenProgress"])
        ->middleware("auth")
        ->name("game.mapgen.progress");
    Route::get("/game/{mapId}/progress/stream", [
        GameController::class,
        "mapGenProgressStream",
    ])
        ->middleware("auth")
        ->name("game.mapgen.progress.stream");
}

// The actual "Game View" page
Route::get("/game/{game}/view", [GameController::class, "view"])->name(
    "game.view",
);

Route::get("/load-game", function () {
    return app(\App\Http\Controllers\GameController::class)->loadList();
})->middleware('auth')->name("game.load");

Route::view("/control-panel", "control-panel")
    ->name("control-panel")
    ->middleware("auth");

Route::view("/settings", "settings")->name("settings");

// Map generator index.
Route::get("/Map", [MapController::class, "index"])->name("map.index");

// Consolidated generate endpoint: POST to create map and immediately kick off step1
Route::post('/Map/generate', [MapController::class, 'generateAndStepOne'])->name('map.generate');

// Map generation editor hub
Route::get('/Map/editor/{mapId}/', [MapController::class, 'editor'])->name('map.editor');

// Map generating steps.
// 'as'=>'mapgen.step1',
Route::get("/Map/step1/{mapId}/", [MapController::class, "runFirstStep"]);

//'as'=>'mapgen.step2',
Route::get("/Map/step2/{mapId}/", [MapController::class, "runSecondStep"]);

// Tree Steps.
//'mapgen.step3',
Route::get("/Map/step3/{mapId}/", [MapController::class, "runThirdStep"])->name('mapgen.step3');
Route::get("/Map/preview/{mapId}/", [MapController::class, "preview"])->name('mapgen.preview');

// Game maps DataTables views
Route::get('/game/{game}/maps', [GameController::class, 'mapsTable'])->name('game.maps.table');
Route::get('/api/game/{game}/maps', [GameController::class, 'mapsTableData'])->name('api.game.maps');

//'as'=>'mapgen.treeStepSecond',
Route::get("/Map/treeStep2/{mapId}/", [MapController::class, "runTreeStepTwo"])->name('mapgen.treeStepSecond');

//'as'=>'mapgen.treeStepThird',
Route::get("/Map/treeStep3/{mapId}/", [
    MapController::class,
    "runTreeStepThree",
])->name('mapgen.treeStepThird');

//'as'=>'mapgen.step4',
Route::get("/Map/step4/{mapId}/", [MapController::class, "runFourthStep"])->name('mapgen.step4');

//'as'=>'mapgen.step5',
Route::get("/Map/step5/{mapId}/{mountainLine}", [
    MapController::class,
    "runLastStep",
])->name('mapgen.step5');

//'as'=>'mapgen.load',
Route::get("/Map/load/{mapId}/", [MapController::class, "runMapLoad"])->name('mapgen.load');

//'as'=>'mapgen.load',
Route::get("/Map/save/{mapId}/", [MapController::class, "saveMongoToMysql"])->name('mapgen.save');

Route::view("profile", "profile")
    ->middleware(["auth"])
    ->name("profile");

Route::get("/dev/codex-report", function () {
    $report = file_exists("/tmp/codex-report.txt")
        ? file_get_contents("/tmp/codex-report.txt")
        : "No QA report found.";

    return response("<pre>{$report}</pre>", 200)->header(
        "Content-Type",
        "text/html",
    );
});

Route::get("/dev/qa", function () {
    $log = @file_get_contents("/tmp/package-watcher.log");
    return response("<pre>$log</pre>", 200)->header(
        "Content-Type",
        "text/html",
    );
});

require __DIR__ . "/auth.php";

// Diagnostic route: lists registered application routes (excluding vendor + debug itself)
Route::get('/route-debug', function() {
    $routes = collect(Route::getRoutes())->map(function($r){
        return [
            'method' => implode('|', $r->methods()),
            'uri' => $r->uri(),
            'name' => $r->getName(),
            'action' => $r->getActionName(),
        ];
    })->filter(fn($r) => !str_starts_with($r['uri'], 'vendor') && $r['uri'] !== 'route-debug');
    return response()->json($routes->values());
})->name('debug.routes');

Route::get('/game/{mapId}/heightmap', [App\Http\Controllers\MapHeightmapController::class, 'generate'])->name('map.heightmap');
Route::get('/game/{mapId}/heightmap-data', [App\Http\Controllers\MapHeightmapController::class, 'data'])->name('map.heightmap.data');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameViewController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MapController;

Route::get("/", function () {
    return view("mainEntrance");
})->name("main.entrance");

// Main Menu Routes
Route::get("/new-game", function () {
    return view("game.new");
})
    ->middleware("auth")
    ->name("game.new");

Route::post("/game", [GameController::class, "create"])
    ->middleware("auth")
    ->name("game.create");

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
Route::get("/game/{mapId}/progress", [GameController::class, "mapGenProgress"])
    ->middleware("auth")
    ->name("game.mapgen.progress");
Route::get("/game/{mapId}/progress/stream", [
    GameController::class,
    "mapGenProgressStream",
])
    ->middleware("auth")
    ->name("game.mapgen.progress.stream");

// The actual "Game View" page
Route::get("/game/{game}/view", [GameController::class, "view"])->name(
    "game.view",
);

Route::get("/load-game", function () {
    return view("game.load");
})->name("game.load");

Route::view("/control-panel", "control-panel")
    ->name("control-panel")
    ->middleware("auth");

Route::get("/settings", function () {
    return "<h1>Settings</h1><p>Placeholder for game settings.</p>";
})->name("settings");

// Map generator index.
Route::get("/Map", [MapController::class, "index"])->name("map.index");

// Map generating steps.
// 'as'=>'mapgen.step1',
Route::get("/Map/step1/{mapId}/", [MapController::class, "runFirstStep"]);

//'as'=>'mapgen.step2',
Route::get("/Map/step2/{mapId}/", [MapController::class, "runSecondStep"]);

// Tree Steps.
//'mapgen.step3',
Route::get("/Map/step3/{mapId}/", [MapController::class, "runThirdStep"]);

//'as'=>'mapgen.treeStepSecond',
Route::get("/Map/treeStep2/{mapId}/", [MapController::class, "runTreeStepTwo"]);

//'as'=>'mapgen.treeStepThird',
Route::get("/Map/treeStep3/{mapId}/", [
    MapController::class,
    "runTreeStepThree",
]);

//'as'=>'mapgen.step4',
Route::get("/Map/step4/{mapId}/", [MapController::class, "runFourthStep"]);

//'as'=>'mapgen.step5',
Route::get("/Map/step5/{mapId}/{mountainLine}", [
    MapController::class,
    "runLastStep",
]);

//'as'=>'mapgen.load',
Route::get("/Map/load/{mapId}/", [MapController::class, "runMapLoad"]);

//'as'=>'mapgen.load',
Route::get("/Map/save/{mapId}/", [MapController::class, "saveMongoToMysql"]);

Route::view("dashboard", "dashboard")
    ->middleware(["auth", "verified"])
    ->name("dashboard");

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

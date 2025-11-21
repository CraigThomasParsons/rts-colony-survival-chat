<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;
use App\Http\Controllers\MapController;

Route::get('/', function()
{
    return view('mainEntrance');
})->name('main.entrance');

// Main Menu Routes
Route::get('/new-game', function () {
    return view('game.new');
})->name('game.new');

Route::post('/game', [GameController::class, 'create'])->name('game.create');

Route::get('/load-game', function () {
    return view('game.load');
})->name('game.load');

Route::get('/control-panel', function () {
    return '<h1>Admin Control Panel</h1><p>Placeholder for admin controls.</p>';
})->name('control-panel')->middleware('auth'); // Example middleware

Route::get('/settings', function () {
    return '<h1>Settings</h1><p>Placeholder for game settings.</p>';
})->name('settings');

// Map generator index.
Route::get('/Map', [ 
    MapController::class,
    'index'
]);

// Map generating steps.
// 'as'=>'mapgen.step1',
Route::get('/Map/step1/{mapId}/', array(
    MapController::class,
    'runFirstStep'
));

//'as'=>'mapgen.step2',
Route::get('/Map/step2/{mapId}/', array(
    MapController::class,
    'runSecondStep'
));

// Tree Steps.
//'mapgen.step3',
Route::get('/Map/step3/{mapId}/', array(
    MapController::class,
    'runThirdStep'
));

//'as'=>'mapgen.treeStepSecond',
Route::get('/Map/treeStep2/{mapId}/', array(
    MapController::class,
    'runTreeStepTwo'
));

//'as'=>'mapgen.treeStepThird',
Route::get('/Map/treeStep3/{mapId}/', array(
    MapController::class,
    'runTreeStepThree'
));

//'as'=>'mapgen.step4',
Route::get('/Map/step4/{mapId}/', array(
    MapController::class,
    'runFourthStep'
));

//'as'=>'mapgen.step5',
Route::get('/Map/step5/{mapId}/{mountainLine}', array(
    MapController::class,
    'runLastStep'
));

//'as'=>'mapgen.load',
Route::get('/Map/load/{mapId}/', array(
    MapController::class,
    'runMapLoad'
));

//'as'=>'mapgen.load',
Route::get('/Map/save/{mapId}/', array(
    MapController::class,
    'saveMongoToMysql'
));

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get('/dev/codex-report', function () {
    $report = file_exists('/tmp/codex-report.txt')
        ? file_get_contents('/tmp/codex-report.txt')
        : "No QA report found.";

    return response("<pre>{$report}</pre>", 200)
        ->header('Content-Type', 'text/html');
});

Route::get('/dev/qa', function () {
    $log = @file_get_contents('/tmp/package-watcher.log');
    return response("<pre>$log</pre>", 200)
        ->header('Content-Type', 'text/html');
});

/**
 * Add New Task
 */
//Route::post('/task', function (Request $request) {

//});

/**
 * Delete Task
 */
//Route::delete('/task/{task}', function (Task $task) {

//});

require __DIR__.'/auth.php';
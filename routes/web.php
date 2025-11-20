<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\MapController;

Route::get('/', function()
{
    //return View::make('mainEntrance');
    return view('mainEntrance');
});

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

// Update our 'home' route to redirect to /tasks
Route::get('/home', function () {
    return redirect()->route('tasks.index');
})->name('home');


Route::get('/tasks', [TaskController::class.'@show', 'tasks.index']);

Route::put('tasks/sync', TaskController::class.'@sync')->name('tasks.sync');


Route::resources([
    'task' => TaskController::class,
    'statuses' => StatusController::class,
]);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

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
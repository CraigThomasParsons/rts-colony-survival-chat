<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GameStateController;
use App\Http\Controllers\MapController;

Route::prefix('game')->group(function () {
    Route::get('initial', [GameStateController::class, 'initial']);
    Route::post('sync', [GameStateController::class, 'sync']);
    Route::post('workers/move', [GameStateController::class, 'moveWorker']);
});

\Lomkit\Rest\Facades\Rest::resource('users', \App\Rest\Controllers\UsersController::class);

// Map status polling endpoint
Route::get('map/{mapId}/status', [MapController::class, 'status']);
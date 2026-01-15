<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return tap(
    Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create(),
    function (Application $app) {
        if ($storage = env('APP_STORAGE')) {
            if ($storage !== '' && DIRECTORY_SEPARATOR !== $storage[0]) {
                $storage = $app->basePath($storage);
            }

            if (!is_dir($storage)) {
                @mkdir($storage, 0755, true);
            }

            $app->useStoragePath($storage);
        }
    }
);

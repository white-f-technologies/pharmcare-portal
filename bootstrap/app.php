<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'setup' => \App\Http\Middleware\EnsureSetupComplete::class,
            'license' => \App\Http\Middleware\CheckLicense::class,
        ]);

        $middleware->trustProxies(at: '*');

        // Run license enforcement on every web request
        $middleware->web(append: [
            \App\Http\Middleware\CheckLicense::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (env('PHARMCARE_DESKTOP_MODE', false) && function_exists('app_data_path')) {
    $app->useStoragePath(app_data_path('storage'));
}

return $app;


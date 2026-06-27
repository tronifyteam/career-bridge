<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['prefix' => 'api', 'middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->alias([
            'is_admin'    => \App\Http\Middleware\IsAdmin::class,
            'role'        => \App\Http\Middleware\RoleMiddleware::class,
            'is_employer' => \App\Http\Middleware\EnsureIsEmployer::class,
        ]);
        $middleware->append(\App\Http\Middleware\LogRequestResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

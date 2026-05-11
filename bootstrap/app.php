<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //registering the userActivity middleware
        $middleware->web(append: [
            \App\Http\Middleware\UserActivity::class
        ]);
        //redirecting unAuthenticated users
        $middleware->redirectTo(
            guests: '/user/register',
            users: '/chat/show'
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HtmlMinifyMiddleware;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: null,
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HtmlMinifyMiddleware::class,
            LocaleMiddleware::class,
            HandleInertiaRequests::class,
        ]);
        $middleware->encryptCookies(except: [
            'locale',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

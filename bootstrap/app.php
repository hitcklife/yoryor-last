<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'rate.limit.login' => \App\Http\Middleware\RateLimitAuth::class,
            'rate.limit.otp' => \App\Http\Middleware\RateLimitAuth::class.':otp',
            'secure.headers' => \App\Http\Middleware\SecureHeaders::class,
            'chat.rate.limit' => \App\Http\Middleware\ChatRateLimit::class,
        ]);

        // Apply secure headers middleware to all API routes
        $middleware->prependToGroup('api', \App\Http\Middleware\SecureHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

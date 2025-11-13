<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/user.php'));
                
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Override the default auth middleware to redirect to /start instead of /login
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'rate.limit.login' => \App\Http\Middleware\RateLimitAuth::class,
            'rate.limit.otp' => \App\Http\Middleware\RateLimitAuth::class.':otp',
            'secure.headers' => \App\Http\Middleware\SecureHeaders::class,
            'chat.rate.limit' => \App\Http\Middleware\ChatRateLimit::class,
            'api.rate.limit' => \App\Http\Middleware\ApiRateLimit::class,
            'performance.monitor' => \App\Http\Middleware\PerformanceMonitor::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'language' => \App\Http\Middleware\LanguageMiddleware::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'update.last.active' => \App\Http\Middleware\UpdateLastActive::class,
        ]);

        // Apply secure headers middleware to all API routes
        $middleware->prependToGroup('api', \App\Http\Middleware\SecureHeaders::class);
        
        // Apply performance monitoring to API routes in non-production environments
        if (env('APP_ENV') !== 'production') {
            $middleware->appendToGroup('api', \App\Http\Middleware\PerformanceMonitor::class);
        }
        
        // Apply language middleware to web routes
        $middleware->web(append: [
            \App\Http\Middleware\LanguageMiddleware::class,
            \App\Http\Middleware\SetLocale::class,
        ]);
        
        // Apply update last active to authenticated routes
        $middleware->group('auth', [
            \App\Http\Middleware\UpdateLastActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

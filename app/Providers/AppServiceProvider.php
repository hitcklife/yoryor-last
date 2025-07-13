<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Events\NewLikeEvent;
use App\Events\NewMatchEvent;
use App\Events\NewMessageEvent;
use App\Listeners\SendLikeNotification;
use App\Listeners\SendMatchNotification;
use App\Listeners\SendMessageNotification;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners for push notifications
        Event::listen(NewLikeEvent::class, SendLikeNotification::class);
        Event::listen(NewMatchEvent::class, SendMatchNotification::class);
        Event::listen(NewMessageEvent::class, SendMessageNotification::class);
    }
}

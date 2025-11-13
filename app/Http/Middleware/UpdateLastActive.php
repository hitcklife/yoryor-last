<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateLastActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Only update if last activity was more than 1 minute ago to avoid too many DB writes
            if (!$user->last_active_at || $user->last_active_at->lt(now()->subMinute())) {
                $user->updateLastActive();
            }
        }

        return $next($request);
    }
}
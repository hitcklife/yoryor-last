<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $guard = $guards[0] ?? 'web';
        
        if (!Auth::guard($guard)->check()) {
            // Redirect unauthenticated users to /start instead of /login
            return redirect()->route('start');
        }

        return $next($request);
    }
}

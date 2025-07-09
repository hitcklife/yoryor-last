<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\PresenceService;
use Illuminate\Support\Facades\Auth;

class UpdateUserPresence
{
    public function __construct(
        private PresenceService $presenceService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only update presence for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Mark user as online in presence system
            $this->presenceService->markUserOnline($user);
            
            // Track API activity
            if (class_exists(\App\Traits\TracksActivity::class)) {
                $user->logActivity('api_request', [
                    'endpoint' => $request->getPathInfo(),
                    'method' => $request->getMethod(),
                    'user_agent' => $request->header('User-Agent'),
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return $response;
    }
} 
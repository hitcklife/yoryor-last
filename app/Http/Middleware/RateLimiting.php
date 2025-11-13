<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class RateLimiting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $key = $this->getRateLimitKey($request);
        $maxAttempts = $this->getMaxAttempts($request);
        $decayMinutes = $this->getDecayMinutes($request);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'url' => $request->fullUrl(),
                'retry_after' => $retryAfter,
                'max_attempts' => $maxAttempts
            ]);
            
            return response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $retryAfter
            ], 429);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - RateLimiter::attempts($key)));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    /**
     * Get rate limit key based on request type
     */
    private function getRateLimitKey(Request $request): string
    {
        $userId = auth()->id();
        $ip = $request->ip();
        $route = $request->route()?->getName();
        
        // Different rate limits for different actions
        if ($request->is('api/user/like/*')) {
            return "like:{$userId}:{$ip}";
        }
        
        if ($request->is('api/user/message/*')) {
            return "message:{$userId}:{$ip}";
        }
        
        if ($request->is('api/user/search')) {
            return "search:{$userId}:{$ip}";
        }
        
        if ($request->is('api/user/upload/*')) {
            return "upload:{$userId}:{$ip}";
        }
        
        if ($request->is('api/user/verification/*')) {
            return "verification:{$userId}:{$ip}";
        }
        
        if ($request->is('api/user/emergency/*')) {
            return "emergency:{$userId}:{$ip}";
        }
        
        // General API rate limit
        if ($request->is('api/*')) {
            return "api:{$userId}:{$ip}";
        }
        
        // General web rate limit
        return "web:{$userId}:{$ip}";
    }
    
    /**
     * Get max attempts based on request type
     */
    private function getMaxAttempts(Request $request): int
    {
        // Like actions - 100 per hour
        if ($request->is('api/user/like/*')) {
            return 100;
        }
        
        // Message actions - 200 per hour
        if ($request->is('api/user/message/*')) {
            return 200;
        }
        
        // Search actions - 50 per hour
        if ($request->is('api/user/search')) {
            return 50;
        }
        
        // Upload actions - 20 per hour
        if ($request->is('api/user/upload/*')) {
            return 20;
        }
        
        // Verification actions - 5 per hour
        if ($request->is('api/user/verification/*')) {
            return 5;
        }
        
        // Emergency actions - 10 per hour
        if ($request->is('api/user/emergency/*')) {
            return 10;
        }
        
        // General API - 1000 per hour
        if ($request->is('api/*')) {
            return 1000;
        }
        
        // General web - 2000 per hour
        return 2000;
    }
    
    /**
     * Get decay minutes based on request type
     */
    private function getDecayMinutes(Request $request): int
    {
        // Like actions - 1 hour
        if ($request->is('api/user/like/*')) {
            return 60;
        }
        
        // Message actions - 1 hour
        if ($request->is('api/user/message/*')) {
            return 60;
        }
        
        // Search actions - 1 hour
        if ($request->is('api/user/search')) {
            return 60;
        }
        
        // Upload actions - 1 hour
        if ($request->is('api/user/upload/*')) {
            return 60;
        }
        
        // Verification actions - 1 hour
        if ($request->is('api/user/verification/*')) {
            return 60;
        }
        
        // Emergency actions - 1 hour
        if ($request->is('api/user/emergency/*')) {
            return 60;
        }
        
        // General API - 1 hour
        if ($request->is('api/*')) {
            return 60;
        }
        
        // General web - 1 hour
        return 60;
    }
}

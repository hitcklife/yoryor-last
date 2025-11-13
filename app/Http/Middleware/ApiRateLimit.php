<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimit
{
    /**
     * Rate limits for different operations
     */
    private const RATE_LIMITS = [
        'like_action' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
            'message' => 'Too many like actions. Please wait before performing another action.'
        ],
        'match_discovery' => [
            'max_attempts' => 50,
            'decay_minutes' => 1,
            'message' => 'Too many match discovery requests. Please wait before searching again.'
        ],
        'profile_update' => [
            'max_attempts' => 20,
            'decay_minutes' => 1,
            'message' => 'Too many profile updates. Please wait before updating again.'
        ],
        'photo_upload' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
            'message' => 'Too many photo uploads. Please wait before uploading another photo.'
        ],
        'story_action' => [
            'max_attempts' => 20,
            'decay_minutes' => 1,
            'message' => 'Too many story actions. Please wait before performing another action.'
        ],
        'call_action' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
            'message' => 'Too many call actions. Please wait before making another call.'
        ],
        'auth_action' => [
            'max_attempts' => 5,
            'decay_minutes' => 15,
            'message' => 'Too many authentication attempts. Please wait before trying again.'
        ],
        'sensitive_action' => [
            'max_attempts' => 5,
            'decay_minutes' => 5,
            'message' => 'Too many sensitive actions. Please wait before trying again.'
        ],
        'block_action' => [
            'max_attempts' => 10,
            'decay_minutes' => 5,
            'message' => 'Too many block/unblock actions. Please wait before trying again.'
        ],
        'verification_submit' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
            'message' => 'Too many verification submissions. Please wait before submitting again.'
        ],
        'panic_activation' => [
            'max_attempts' => 2,
            'decay_minutes' => 1,
            'message' => 'Too many panic activations. Please wait before activating again.'
        ],
        'report_action' => [
            'max_attempts' => 5,
            'decay_minutes' => 10,
            'message' => 'Too many reports submitted. Please wait before reporting again.'
        ],

        // Location and security operations
        'location_update' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
            'message' => 'Too many location updates. Please wait before updating again.'
        ],
        'password_change' => [
            'max_attempts' => 3,
            'decay_minutes' => 60,
            'message' => 'Too many password change attempts. Please wait 1 hour.'
        ],
        'email_change' => [
            'max_attempts' => 2,
            'decay_minutes' => 60,
            'message' => 'Too many email change attempts. Please wait 1 hour.'
        ],
        'account_deletion' => [
            'max_attempts' => 1,
            'decay_minutes' => 1440, // 24 hours
            'message' => 'Account deletion can only be attempted once per day.'
        ],
        'data_export' => [
            'max_attempts' => 2,
            'decay_minutes' => 60,
            'message' => 'Too many data export requests. Please wait 1 hour.'
        ],
        'default' => [
            'max_attempts' => 200,
            'decay_minutes' => 1,
            'message' => 'Too many requests. Please slow down.'
        ]
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $operation = 'default'): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            // Apply stricter limits for unauthenticated requests
            return $this->handleUnauthenticatedRequest($request, $next, $operation);
        }

        $limits = self::RATE_LIMITS[$operation] ?? self::RATE_LIMITS['default'];
        $key = $this->buildRateLimitKey($user->id, $operation, $request);
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limits['max_attempts']) {
            $retryAfter = $this->getRetryAfter($key, $limits['decay_minutes']);
            
            return response()->json([
                'status' => 'error',
                'message' => $limits['message'],
                'error_code' => 'RATE_LIMITED',
                'retry_after' => $retryAfter,
                'rate_limit' => [
                    'limit' => $limits['max_attempts'],
                    'remaining' => 0,
                    'reset' => time() + ($retryAfter * 60)
                ]
            ], 429);
        }

        // Increment the attempt count
        $newAttempts = $attempts + 1;
        Cache::put($key, $newAttempts, now()->addMinutes($limits['decay_minutes']));

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limits['max_attempts']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limits['max_attempts'] - $newAttempts));
        $response->headers->set('X-RateLimit-Reset', time() + ($limits['decay_minutes'] * 60));

        return $response;
    }

    /**
     * Handle unauthenticated requests with stricter limits
     */
    private function handleUnauthenticatedRequest(Request $request, Closure $next, string $operation): Response
    {
        $ip = $request->ip();
        $key = "rate_limit:unauthenticated:{$ip}:{$operation}";
        
        // Stricter limits for unauthenticated requests
        $maxAttempts = 10;
        $decayMinutes = 5;
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $maxAttempts) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many requests from this IP. Please authenticate or wait before trying again.',
                'error_code' => 'RATE_LIMITED',
                'retry_after' => $decayMinutes
            ], 429);
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        return $next($request);
    }

    /**
     * Build the rate limit cache key
     */
    private function buildRateLimitKey(int $userId, string $operation, Request $request): string
    {
        $base = "api_rate_limit:{$userId}:{$operation}";
        
        // For certain operations, include additional context
        switch ($operation) {
            case 'like_action':
                $targetUserId = $request->route('userId') ?? $request->input('user_id');
                return $targetUserId ? "{$base}:target:{$targetUserId}" : $base;
                
            case 'profile_update':
                $profileId = $request->route('profile') ?? $request->route('userId');
                return $profileId ? "{$base}:profile:{$profileId}" : $base;
                
            default:
                return $base;
        }
    }

    /**
     * Get the retry after time in minutes
     */
    private function getRetryAfter(string $key, int $decayMinutes): int
    {
        try {
            $store = Cache::store();
            if (method_exists($store, 'getRedis')) {
                $ttl = $store->getRedis()->ttl($key);
                return $ttl > 0 ? ceil($ttl / 60) : $decayMinutes;
            }
            return $decayMinutes;
        } catch (\Exception $e) {
            return $decayMinutes;
        }
    }
}
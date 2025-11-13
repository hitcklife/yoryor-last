<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ChatRateLimit
{
    /**
     * Rate limits for different operations
     */
    private const RATE_LIMITS = [
        'send_message' => [
            'max_attempts' => 60,
            'decay_minutes' => 1,
            'message' => 'Too many messages sent. Please wait before sending another message.'
        ],
        'create_chat' => [
            'max_attempts' => 10,
            'decay_minutes' => 1,
            'message' => 'Too many chat creation attempts. Please wait before creating another chat.'
        ],
        'edit_message' => [
            'max_attempts' => 30,
            'decay_minutes' => 1,
            'message' => 'Too many edit attempts. Please wait before editing another message.'
        ],
        'delete_message' => [
            'max_attempts' => 20,
            'decay_minutes' => 1,
            'message' => 'Too many delete attempts. Please wait before deleting another message.'
        ],
        'mark_read' => [
            'max_attempts' => 100,
            'decay_minutes' => 1,
            'message' => 'Too many read status updates. Please wait a moment.'
        ],
        'default' => [
            'max_attempts' => 300,
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
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated'
            ], 401);
        }

        $limits = self::RATE_LIMITS[$operation] ?? self::RATE_LIMITS['default'];
        $key = $this->buildRateLimitKey($user->id, $operation, $request);
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limits['max_attempts']) {
            $retryAfter = $this->getRetryAfter($key, $limits['decay_minutes']);
            
            return response()->json([
                'status' => 'error',
                'message' => $limits['message'],
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
     * Build the rate limit cache key
     */
    private function buildRateLimitKey(int $userId, string $operation, Request $request): string
    {
        $base = "rate_limit:{$userId}:{$operation}";
        
        // For certain operations, include additional context
        switch ($operation) {
            case 'send_message':
                $chatId = $request->route('id') ?? $request->input('chat_id');
                return $chatId ? "{$base}:chat:{$chatId}" : $base;
                
            case 'edit_message':
            case 'delete_message':
                $messageId = $request->route('message_id') ?? $request->route('messageId');
                return $messageId ? "{$base}:message:{$messageId}" : $base;
                
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

    /**
     * Check if request should bypass rate limiting
     */
    private function shouldBypassRateLimit(Request $request): bool
    {
        // Allow bypass for testing environment
        if (app()->environment('testing')) {
            return true;
        }

        // Allow bypass for admin users (if you have such logic)
        $user = Auth::user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Handle rate limit exceeded with exponential backoff suggestion
     */
    private function handleRateLimitExceeded(array $limits, int $attempts): array
    {
        $baseDelay = $limits['decay_minutes'];
        
        // Suggest exponential backoff for repeated violations
        if ($attempts > $limits['max_attempts'] * 2) {
            $backoffMultiplier = min(8, pow(2, floor($attempts / $limits['max_attempts']) - 1));
            $suggestedDelay = $baseDelay * $backoffMultiplier;
            
            return [
                'message' => $limits['message'] . ' Consider waiting longer before retrying.',
                'suggested_delay_minutes' => $suggestedDelay,
                'severity' => 'high'
            ];
        }

        return [
            'message' => $limits['message'],
            'suggested_delay_minutes' => $baseDelay,
            'severity' => 'normal'
        ];
    }
}
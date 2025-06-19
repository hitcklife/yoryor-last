<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RateLimitAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $limiterType
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $limiterType = 'login')
    {
        $key = $this->getLimiterKey($request, $limiterType);
        $maxAttempts = $this->getMaxAttempts($limiterType);
        $decayMinutes = $this->getDecayMinutes($limiterType);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return $this->buildTooManyAttemptsResponse($key, $limiterType);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // If the response indicates a failed attempt (e.g., invalid credentials),
        // we'll count it against the rate limit
        if ($this->isFailedAttempt($response, $limiterType)) {
            RateLimiter::hit($key, $decayMinutes * 60);
        }

        return $response;
    }

    /**
     * Get the rate limiter key for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $limiterType
     * @return string
     */
    protected function getLimiterKey(Request $request, string $limiterType): string
    {
        $identifier = $request->ip();

        if ($limiterType === 'login') {
            // For login, use email/phone if available
            $identifier = $request->input('email') ?? $request->input('phone') ?? $request->ip();
        } elseif ($limiterType === 'otp') {
            // For OTP, use phone number if available
            $identifier = $request->input('phone') ?? $request->ip();
        }

        return 'auth:' . $limiterType . ':' . $identifier;
    }

    /**
     * Get the maximum number of attempts for the given limiter type.
     *
     * @param  string  $limiterType
     * @return int
     */
    protected function getMaxAttempts(string $limiterType): int
    {
        return match ($limiterType) {
            'login' => 5,  // 5 login attempts
            'otp' => 3,    // 3 OTP requests
            default => 5,
        };
    }

    /**
     * Get the decay minutes for the given limiter type.
     *
     * @param  string  $limiterType
     * @return int
     */
    protected function getDecayMinutes(string $limiterType): int
    {
        return match ($limiterType) {
            'login' => 10, // 10 minutes lockout for login
            'otp' => 15,   // 15 minutes lockout for OTP
            default => 10,
        };
    }

    /**
     * Build the response for when too many attempts have been made.
     *
     * @param  string  $key
     * @param  string  $limiterType
     * @return \Illuminate\Http\JsonResponse
     */
    protected function buildTooManyAttemptsResponse(string $key, string $limiterType)
    {
        $seconds = RateLimiter::availableIn($key);
        $minutes = ceil($seconds / 60);

        $message = match ($limiterType) {
            'login' => "Too many login attempts. Please try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . ".",
            'otp' => "Too many OTP requests. Please try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . ".",
            default => "Too many attempts. Please try again in {$minutes} " . ($minutes === 1 ? 'minute' : 'minutes') . ".",
        };

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error_code' => 'rate_limit_exceeded',
            'retry_after' => $seconds
        ], 429);
    }

    /**
     * Determine if the response indicates a failed attempt.
     *
     * @param  mixed  $response
     * @param  string  $limiterType
     * @return bool
     */
    protected function isFailedAttempt($response, string $limiterType): bool
    {
        if (!$response instanceof SymfonyResponse) {
            return false;
        }

        // For login and OTP, consider 422 (validation error) as a failed attempt
        if (in_array($limiterType, ['login', 'otp']) && $response->getStatusCode() === 422) {
            return true;
        }

        return false;
    }
}

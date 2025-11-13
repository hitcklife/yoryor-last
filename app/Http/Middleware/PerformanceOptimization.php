<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceOptimization
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
        $startTime = microtime(true);
        
        // Add performance headers
        $response = $next($request);
        
        // Calculate execution time
        $executionTime = microtime(true) - $startTime;
        
        // Add performance headers
        $response->headers->set('X-Response-Time', round($executionTime * 1000, 2) . 'ms');
        $response->headers->set('X-Powered-By', 'Yoryor Dating App');
        
        // Log slow requests (> 1 second)
        if ($executionTime > 1.0) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime,
                'memory_usage' => memory_get_peak_usage(true),
                'user_id' => auth()->id(),
            ]);
        }
        
        // Add cache headers for static assets
        if ($this->isStaticAsset($request)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }
        
        // Add ETag for dynamic content
        if ($this->shouldAddETag($request)) {
            $etag = md5($response->getContent());
            $response->headers->set('ETag', $etag);
            
            if ($request->header('If-None-Match') === $etag) {
                return response('', 304);
            }
        }
        
        return $response;
    }
    
    /**
     * Check if the request is for a static asset
     */
    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        
        return str_starts_with($path, 'assets/') ||
               str_starts_with($path, 'build/') ||
               str_starts_with($path, 'vendor/') ||
               str_ends_with($path, '.css') ||
               str_ends_with($path, '.js') ||
               str_ends_with($path, '.png') ||
               str_ends_with($path, '.jpg') ||
               str_ends_with($path, '.jpeg') ||
               str_ends_with($path, '.gif') ||
               str_ends_with($path, '.svg') ||
               str_ends_with($path, '.ico');
    }
    
    /**
     * Check if ETag should be added
     */
    private function shouldAddETag(Request $request): bool
    {
        // Don't add ETag for API requests or POST requests
        if ($request->is('api/*') || $request->method() !== 'GET') {
            return false;
        }
        
        // Don't add ETag for authenticated user-specific content
        if (auth()->check()) {
            return false;
        }
        
        return true;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Performance thresholds (in milliseconds)
     */
    private const THRESHOLDS = [
        'slow_query' => 1000,     // 1 second
        'very_slow_query' => 3000, // 3 seconds
        'critical_slow' => 5000,   // 5 seconds
    ];

    /**
     * Routes to monitor closely
     */
    private const HIGH_PRIORITY_ROUTES = [
        'api/v1/chats',
        'api/v1/matches',
        'api/v1/likes',
        'api/v1/stories',
        'api/v1/profile',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Get initial DB query count
        $initialQueries = $this->getQueryCount();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $finalQueries = $this->getQueryCount();

        // Calculate metrics
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = $endMemory - $startMemory;
        $queryCount = $finalQueries - $initialQueries;

        // Collect performance data
        $performanceData = [
            'route' => $request->getPathInfo(),
            'method' => $request->getMethod(),
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
            'query_count' => $queryCount,
            'response_size_kb' => round(strlen($response->getContent()) / 1024, 2),
            'status_code' => $response->getStatusCode(),
            'user_id' => auth()->id(),
            'timestamp' => now(),
        ];

        // Monitor performance and alert if needed
        $this->monitorPerformance($performanceData, $request);

        // Add performance headers for debugging (in development)
        if (app()->environment(['local', 'development'])) {
            $response->headers->set('X-Execution-Time', $performanceData['execution_time_ms'] . 'ms');
            $response->headers->set('X-Memory-Usage', $performanceData['memory_usage_mb'] . 'MB');
            $response->headers->set('X-Query-Count', $performanceData['query_count']);
        }

        return $response;
    }

    /**
     * Monitor performance and log/alert if thresholds are exceeded
     */
    private function monitorPerformance(array $performanceData, Request $request): void
    {
        $route = $performanceData['route'];
        $executionTime = $performanceData['execution_time_ms'];
        $queryCount = $performanceData['query_count'];

        // Check execution time thresholds
        if ($executionTime > self::THRESHOLDS['critical_slow']) {
            $this->logPerformanceIssue('CRITICAL_SLOW_REQUEST', $performanceData, $request);
        } elseif ($executionTime > self::THRESHOLDS['very_slow_query']) {
            $this->logPerformanceIssue('VERY_SLOW_REQUEST', $performanceData, $request);
        } elseif ($executionTime > self::THRESHOLDS['slow_query']) {
            $this->logPerformanceIssue('SLOW_REQUEST', $performanceData, $request);
        }

        // Check for N+1 query problems
        if ($queryCount > 20) {
            $this->logPerformanceIssue('HIGH_QUERY_COUNT', $performanceData, $request);
        }

        // Monitor high-priority routes more closely
        if ($this->isHighPriorityRoute($route)) {
            $this->trackHighPriorityRoute($performanceData);
        }

        // Store aggregated metrics
        $this->storeAggregatedMetrics($performanceData);
    }

    /**
     * Log performance issues
     */
    private function logPerformanceIssue(string $type, array $performanceData, Request $request): void
    {
        $logData = array_merge($performanceData, [
            'issue_type' => $type,
            'request_data' => [
                'headers' => $request->headers->all(),
                'query_params' => $request->query(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ]);

        Log::warning("Performance Issue Detected: {$type}", $logData);
    }

    /**
     * Check if route is high priority
     */
    private function isHighPriorityRoute(string $route): bool
    {
        foreach (self::HIGH_PRIORITY_ROUTES as $priorityRoute) {
            if (str_starts_with($route, $priorityRoute)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Track high priority route performance
     */
    private function trackHighPriorityRoute(array $performanceData): void
    {
        $cacheKey = 'high_priority_route_performance:' . date('Y-m-d-H'); // Hourly buckets
        
        $existing = Cache::get($cacheKey, []);
        $route = $performanceData['route'];
        
        if (!isset($existing[$route])) {
            $existing[$route] = [
                'count' => 0,
                'total_time' => 0,
                'max_time' => 0,
                'total_queries' => 0,
                'max_queries' => 0,
            ];
        }
        
        $existing[$route]['count']++;
        $existing[$route]['total_time'] += $performanceData['execution_time_ms'];
        $existing[$route]['max_time'] = max($existing[$route]['max_time'], $performanceData['execution_time_ms']);
        $existing[$route]['total_queries'] += $performanceData['query_count'];
        $existing[$route]['max_queries'] = max($existing[$route]['max_queries'], $performanceData['query_count']);
        
        Cache::put($cacheKey, $existing, now()->addHours(25)); // Keep for just over 24 hours
    }

    /**
     * Store aggregated metrics for reporting
     */
    private function storeAggregatedMetrics(array $performanceData): void
    {
        $dailyKey = 'daily_performance_metrics:' . date('Y-m-d');
        
        $existing = Cache::get($dailyKey, [
            'total_requests' => 0,
            'total_time' => 0,
            'slow_requests' => 0,
            'error_requests' => 0,
            'average_queries' => 0,
            'total_queries' => 0,
        ]);
        
        $existing['total_requests']++;
        $existing['total_time'] += $performanceData['execution_time_ms'];
        $existing['total_queries'] += $performanceData['query_count'];
        
        if ($performanceData['execution_time_ms'] > self::THRESHOLDS['slow_query']) {
            $existing['slow_requests']++;
        }
        
        if ($performanceData['status_code'] >= 400) {
            $existing['error_requests']++;
        }
        
        $existing['average_queries'] = $existing['total_queries'] / $existing['total_requests'];
        
        Cache::put($dailyKey, $existing, now()->addDays(8)); // Keep for a week
    }

    /**
     * Get current database query count
     */
    private function getQueryCount(): int
    {
        try {
            return \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get performance summary for a date range
     */
    public static function getPerformanceSummary(string $startDate = null, string $endDate = null): array
    {
        $startDate = $startDate ?? date('Y-m-d', strtotime('-7 days'));
        $endDate = $endDate ?? date('Y-m-d');
        
        $summary = [
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'daily_metrics' => [],
            'high_priority_routes' => [],
        ];
        
        $currentDate = $startDate;
        while ($currentDate <= $endDate) {
            $dailyKey = 'daily_performance_metrics:' . $currentDate;
            $dailyData = Cache::get($dailyKey, null);
            
            if ($dailyData) {
                $summary['daily_metrics'][$currentDate] = $dailyData;
            }
            
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
        }
        
        return $summary;
    }

    /**
     * Get slow query report
     */
    public static function getSlowQueryReport(): array
    {
        // This would typically connect to a more persistent storage
        // For now, we'll return cached data
        return [
            'slow_queries_today' => Cache::get('slow_queries_' . date('Y-m-d'), []),
            'critical_slow_queries' => Cache::get('critical_slow_queries_' . date('Y-m-d'), []),
        ];
    }
}
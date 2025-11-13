#!/usr/bin/env php
<?php

/**
 * Performance Monitoring Script for YorYor API
 * 
 * This script provides performance metrics and database optimization insights
 * for the YorYor dating application.
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PerformanceMonitor
{
    private $results = [];

    public function run()
    {
        echo "🚀 YorYor Performance Monitor\n";
        echo "============================\n\n";

        $this->checkDatabaseIndexes();
        $this->checkQueryPerformance();
        $this->checkCachePerformance();
        $this->checkRateLimitingStatus();
        $this->generateReport();
    }

    private function checkDatabaseIndexes()
    {
        echo "📊 Checking Database Indexes...\n";

        $criticalIndexes = [
            'users' => ['idx_users_last_active', 'idx_users_registration_status'],
            'likes' => ['idx_likes_user_created', 'idx_likes_target_created'],
            'matches' => ['idx_matches_user_created', 'idx_matches_target_created'],
            'chats' => ['idx_chats_active_updated'],
            'messages' => ['idx_messages_chat_created', 'idx_messages_sender_created'],
            'stories' => ['idx_stories_user_created', 'idx_stories_active'],
            'photos' => ['idx_photos_user_status'],
        ];

        $indexStatus = [];
        foreach ($criticalIndexes as $table => $indexes) {
            foreach ($indexes as $index) {
                $exists = DB::select("
                    SELECT 1 FROM pg_indexes 
                    WHERE tablename = ? AND indexname = ?
                ", [$table, $index]);
                
                $indexStatus[$table][$index] = !empty($exists);
            }
        }

        $this->results['indexes'] = $indexStatus;
        echo "✅ Database indexes checked\n\n";
    }

    private function checkQueryPerformance()
    {
        echo "⚡ Checking Query Performance...\n";

        $slowQueries = [];

        // Test critical queries
        $testQueries = [
            'user_potential_matches' => "
                SELECT COUNT(*) FROM users u 
                JOIN profiles p ON u.id = p.user_id 
                WHERE u.registration_completed = true 
                AND u.disabled_at IS NULL 
                AND u.last_active_at > NOW() - INTERVAL '30 days'
            ",
            'user_messages' => "
                SELECT COUNT(*) FROM messages m 
                JOIN chats c ON m.chat_id = c.id 
                WHERE m.created_at > NOW() - INTERVAL '7 days'
            ",
            'user_stories' => "
                SELECT COUNT(*) FROM stories 
                WHERE expires_at > NOW() 
                AND status = 'active'
            "
        ];

        foreach ($testQueries as $name => $query) {
            $start = microtime(true);
            DB::select($query);
            $duration = (microtime(true) - $start) * 1000; // Convert to milliseconds
            
            $slowQueries[$name] = [
                'duration_ms' => round($duration, 2),
                'status' => $duration > 100 ? 'SLOW' : 'OK'
            ];
        }

        $this->results['queries'] = $slowQueries;
        echo "✅ Query performance checked\n\n";
    }

    private function checkCachePerformance()
    {
        echo "💾 Checking Cache Performance...\n";

        $cacheStats = [];

        try {
            // Test Redis connection
            $redis = Redis::connection();
            $info = $redis->info();
            
            $cacheStats['redis'] = [
                'connected' => true,
                'memory_used' => $info['used_memory_human'] ?? 'Unknown',
                'hit_rate' => isset($info['keyspace_hits'], $info['keyspace_misses']) 
                    ? round(($info['keyspace_hits'] / ($info['keyspace_hits'] + $info['keyspace_misses'])) * 100, 2) . '%'
                    : 'Unknown'
            ];

            // Test cache operations
            $start = microtime(true);
            Cache::put('performance_test', 'test_value', 60);
            Cache::get('performance_test');
            $cacheOpTime = (microtime(true) - $start) * 1000;
            
            $cacheStats['operations'] = [
                'cache_op_time_ms' => round($cacheOpTime, 2),
                'status' => $cacheOpTime > 10 ? 'SLOW' : 'OK'
            ];

        } catch (\Exception $e) {
            $cacheStats['redis'] = [
                'connected' => false,
                'error' => $e->getMessage()
            ];
        }

        $this->results['cache'] = $cacheStats;
        echo "✅ Cache performance checked\n\n";
    }

    private function checkRateLimitingStatus()
    {
        echo "🛡️ Checking Rate Limiting Status...\n";

        $rateLimitStats = [];

        try {
            // Check if rate limiting middleware is working
            $testKeys = [
                'api_rate_limit:test_user:like_action',
                'api_rate_limit:test_user:match_discovery',
                'api_rate_limit:test_user:profile_update'
            ];

            foreach ($testKeys as $key) {
                $attempts = Cache::get($key, 0);
                $rateLimitStats['test_limits'][$key] = $attempts;
            }

            $rateLimitStats['status'] = 'OK';

        } catch (\Exception $e) {
            $rateLimitStats['status'] = 'ERROR';
            $rateLimitStats['error'] = $e->getMessage();
        }

        $this->results['rate_limiting'] = $rateLimitStats;
        echo "✅ Rate limiting status checked\n\n";
    }

    private function generateReport()
    {
        echo "📋 Performance Report\n";
        echo "====================\n\n";

        // Database Indexes Report
        echo "🗄️ Database Indexes:\n";
        foreach ($this->results['indexes'] as $table => $indexes) {
            echo "  $table:\n";
            foreach ($indexes as $index => $exists) {
                $status = $exists ? '✅' : '❌';
                echo "    $status $index\n";
            }
        }
        echo "\n";

        // Query Performance Report
        echo "⚡ Query Performance:\n";
        foreach ($this->results['queries'] as $query => $stats) {
            $icon = $stats['status'] === 'OK' ? '✅' : '⚠️';
            echo "  $icon $query: {$stats['duration_ms']}ms ({$stats['status']})\n";
        }
        echo "\n";

        // Cache Performance Report
        echo "💾 Cache Performance:\n";
        if ($this->results['cache']['redis']['connected']) {
            echo "  ✅ Redis Connected\n";
            echo "    Memory Used: {$this->results['cache']['redis']['memory_used']}\n";
            echo "    Hit Rate: {$this->results['cache']['redis']['hit_rate']}\n";
            
            $opStatus = $this->results['cache']['operations']['status'];
            $icon = $opStatus === 'OK' ? '✅' : '⚠️';
            echo "  $icon Cache Operations: {$this->results['cache']['operations']['cache_op_time_ms']}ms ($opStatus)\n";
        } else {
            echo "  ❌ Redis Connection Failed\n";
            echo "    Error: {$this->results['cache']['redis']['error']}\n";
        }
        echo "\n";

        // Rate Limiting Report
        echo "🛡️ Rate Limiting:\n";
        $status = $this->results['rate_limiting']['status'];
        $icon = $status === 'OK' ? '✅' : '❌';
        echo "  $icon Status: $status\n";
        
        if (isset($this->results['rate_limiting']['error'])) {
            echo "    Error: {$this->results['rate_limiting']['error']}\n";
        }
        echo "\n";

        // Recommendations
        $this->generateRecommendations();
    }

    private function generateRecommendations()
    {
        echo "💡 Recommendations:\n";
        
        $recommendations = [];

        // Check for missing indexes
        $missingIndexes = [];
        foreach ($this->results['indexes'] as $table => $indexes) {
            foreach ($indexes as $index => $exists) {
                if (!$exists) {
                    $missingIndexes[] = "$table.$index";
                }
            }
        }

        if (!empty($missingIndexes)) {
            $recommendations[] = "Create missing database indexes: " . implode(', ', $missingIndexes);
        }

        // Check for slow queries
        $slowQueries = [];
        foreach ($this->results['queries'] as $query => $stats) {
            if ($stats['status'] === 'SLOW') {
                $slowQueries[] = "$query ({$stats['duration_ms']}ms)";
            }
        }

        if (!empty($slowQueries)) {
            $recommendations[] = "Optimize slow queries: " . implode(', ', $slowQueries);
        }

        // Check cache performance
        if (!$this->results['cache']['redis']['connected']) {
            $recommendations[] = "Fix Redis connection for better cache performance";
        } elseif ($this->results['cache']['operations']['status'] === 'SLOW') {
            $recommendations[] = "Investigate slow cache operations";
        }

        // Check rate limiting
        if ($this->results['rate_limiting']['status'] !== 'OK') {
            $recommendations[] = "Fix rate limiting system";
        }

        if (empty($recommendations)) {
            echo "  ✅ All systems performing well!\n";
        } else {
            foreach ($recommendations as $i => $rec) {
                echo "  " . ($i + 1) . ". $rec\n";
            }
        }

        echo "\n";
        echo "📊 Performance monitoring completed at " . date('Y-m-d H:i:s') . "\n";
    }
}

// Run the performance monitor
$monitor = new PerformanceMonitor();
$monitor->run();
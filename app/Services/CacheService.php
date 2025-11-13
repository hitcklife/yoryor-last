<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    /**
     * Cache duration constants
     */
    const CACHE_SHORT = 300; // 5 minutes
    const CACHE_MEDIUM = 1800; // 30 minutes
    const CACHE_LONG = 3600; // 1 hour
    const CACHE_VERY_LONG = 86400; // 24 hours
    
    /**
     * Cache user profile data
     */
    public static function cacheUserProfile($userId, $profileData, $duration = self::CACHE_MEDIUM)
    {
        $key = "user_profile_{$userId}";
        Cache::put($key, $profileData, $duration);
    }
    
    /**
     * Get cached user profile
     */
    public static function getCachedUserProfile($userId)
    {
        $key = "user_profile_{$userId}";
        return Cache::get($key);
    }
    
    /**
     * Cache user matches
     */
    public static function cacheUserMatches($userId, $matches, $duration = self::CACHE_SHORT)
    {
        $key = "user_matches_{$userId}";
        Cache::put($key, $matches, $duration);
    }
    
    /**
     * Get cached user matches
     */
    public static function getCachedUserMatches($userId)
    {
        $key = "user_matches_{$userId}";
        return Cache::get($key);
    }
    
    /**
     * Cache search results
     */
    public static function cacheSearchResults($query, $filters, $results, $duration = self::CACHE_SHORT)
    {
        $key = "search_" . md5($query . serialize($filters));
        Cache::put($key, $results, $duration);
    }
    
    /**
     * Get cached search results
     */
    public static function getCachedSearchResults($query, $filters)
    {
        $key = "search_" . md5($query . serialize($filters));
        return Cache::get($key);
    }
    
    /**
     * Cache analytics data
     */
    public static function cacheAnalytics($userId, $type, $data, $duration = self::CACHE_MEDIUM)
    {
        $key = "analytics_{$type}_{$userId}";
        Cache::put($key, $data, $duration);
    }
    
    /**
     * Get cached analytics data
     */
    public static function getCachedAnalytics($userId, $type)
    {
        $key = "analytics_{$type}_{$userId}";
        return Cache::get($key);
    }
    
    /**
     * Cache notification counts
     */
    public static function cacheNotificationCount($userId, $count, $duration = self::CACHE_SHORT)
    {
        $key = "notification_count_{$userId}";
        Cache::put($key, $count, $duration);
    }
    
    /**
     * Get cached notification count
     */
    public static function getCachedNotificationCount($userId)
    {
        $key = "notification_count_{$userId}";
        return Cache::get($key);
    }
    
    /**
     * Cache user preferences
     */
    public static function cacheUserPreferences($userId, $preferences, $duration = self::CACHE_LONG)
    {
        $key = "user_preferences_{$userId}";
        Cache::put($key, $preferences, $duration);
    }
    
    /**
     * Get cached user preferences
     */
    public static function getCachedUserPreferences($userId)
    {
        $key = "user_preferences_{$userId}";
        return Cache::get($key);
    }
    
    /**
     * Cache system settings
     */
    public static function cacheSystemSettings($settings, $duration = self::CACHE_VERY_LONG)
    {
        $key = "system_settings";
        Cache::put($key, $settings, $duration);
    }
    
    /**
     * Get cached system settings
     */
    public static function getCachedSystemSettings()
    {
        $key = "system_settings";
        return Cache::get($key);
    }
    
    /**
     * Invalidate user-related cache
     */
    public static function invalidateUserCache($userId)
    {
        $keys = [
            "user_profile_{$userId}",
            "user_matches_{$userId}",
            "notification_count_{$userId}",
            "user_preferences_{$userId}",
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Invalidate all cache
     */
    public static function invalidateAllCache()
    {
        Cache::flush();
    }
    
    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        if (config('cache.default') === 'redis') {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 'N/A',
                'total_commands_processed' => $info['total_commands_processed'] ?? 'N/A',
                'keyspace_hits' => $info['keyspace_hits'] ?? 'N/A',
                'keyspace_misses' => $info['keyspace_misses'] ?? 'N/A',
            ];
        }
        
        return ['driver' => config('cache.default')];
    }
    
    /**
     * Warm up cache with frequently accessed data
     */
    public static function warmUpCache()
    {
        // Cache system settings
        $settings = [
            'app_name' => config('app.name'),
            'app_version' => config('app.version', '1.0.0'),
            'features' => [
                'video_calls' => true,
                'premium_features' => true,
                'verification' => true,
            ]
        ];
        
        self::cacheSystemSettings($settings);
        
        // Cache could be warmed up with other frequently accessed data
        // This would typically be done in a scheduled job
    }
    
    /**
     * Cache with tags for better organization
     */
    public static function cacheWithTags($key, $value, $tags, $duration = self::CACHE_MEDIUM)
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->put($key, $value, $duration);
        } else {
            Cache::put($key, $value, $duration);
        }
    }

    /**
     * Invalidate cache by tags
     */
    public static function invalidateByTags($tags)
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            Cache::tags($tags)->flush();
        }
    }
}
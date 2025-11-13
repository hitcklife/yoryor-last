# YorYor Backend Optimization Guide

## Performance Bottlenecks & Solutions

### 1. Database Optimization

#### Current Issues
- N+1 query problems in user listings
- Missing indexes on frequently queried columns
- Inefficient joins in match suggestions
- Large message table without partitioning

#### Recommended Solutions

```php
// Before: N+1 Problem
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->bio; // Additional query per user
}

// After: Eager Loading
$users = User::with(['profile', 'photos', 'preferences'])->get();
```

#### Database Indexes to Add
```sql
-- High-priority indexes
CREATE INDEX idx_messages_chat_id_created_at ON messages(chat_id, created_at);
CREATE INDEX idx_users_last_active_at ON users(last_active_at);
CREATE INDEX idx_likes_user_id_liked_user_id ON likes(user_id, liked_user_id);
CREATE INDEX idx_device_tokens_user_id ON device_tokens(user_id);

-- Composite indexes for complex queries
CREATE INDEX idx_users_location_age ON users(latitude, longitude, birthdate);
```

#### Table Partitioning
```sql
-- Partition messages table by month
ALTER TABLE messages PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at));
```

### 2. Caching Strategy

#### Implement Redis Caching
```php
// CacheService.php
class CacheService
{
    const USER_PROFILE_TTL = 3600; // 1 hour
    const MATCH_SUGGESTIONS_TTL = 300; // 5 minutes
    const ONLINE_USERS_TTL = 60; // 1 minute
    
    public function getUserProfile($userId)
    {
        return Cache::remember("user_profile_{$userId}", self::USER_PROFILE_TTL, function () use ($userId) {
            return User::with(['profile', 'photos', 'preferences'])->find($userId);
        });
    }
    
    public function invalidateUserCache($userId)
    {
        Cache::forget("user_profile_{$userId}");
        Cache::forget("match_suggestions_{$userId}");
    }
}
```

#### Cache Warming Strategy
```php
// app/Console/Commands/WarmCache.php
class WarmCache extends Command
{
    public function handle()
    {
        // Warm frequently accessed data
        User::active()->chunk(100, function ($users) {
            foreach ($users as $user) {
                Cache::put("user_profile_{$user->id}", $user->load(['profile', 'photos']), 3600);
            }
        });
    }
}
```

### 3. API Response Optimization

#### Implement Field Selection
```php
// Allow clients to specify fields
// GET /api/v1/users?fields=id,name,photos

public function index(Request $request)
{
    $fields = explode(',', $request->get('fields', '*'));
    return User::select($fields)->paginate();
}
```

#### Response Compression
```php
// app/Http/Middleware/CompressResponse.php
class CompressResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        if ($this->shouldCompress($request, $response)) {
            $response->header('Content-Encoding', 'gzip');
            $response->setContent(gzencode($response->getContent(), 9));
        }
        
        return $response;
    }
}
```

### 4. Queue Optimization

#### Separate Queue Priorities
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
        'after_commit' => false,
    ],
    'high' => [
        'driver' => 'redis',
        'queue' => 'high',
        'retry_after' => 60,
    ],
    'low' => [
        'driver' => 'redis',
        'queue' => 'low',
        'retry_after' => 120,
    ],
],

// Usage
dispatch(new SendPushNotification($user))->onQueue('high');
dispatch(new ProcessMediaUpload($media))->onQueue('low');
```

#### Batch Processing
```php
// Batch push notifications
Bus::batch([
    new SendPushNotification($users->chunk(100)),
])->dispatch();
```

### 5. Real-time Optimization

#### WebSocket Connection Pooling
```javascript
// resources/js/websocket-manager.js
class WebSocketManager {
    constructor() {
        this.connections = new Map();
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
    }
    
    getConnection(channel) {
        if (!this.connections.has(channel)) {
            this.connections.set(channel, this.createConnection(channel));
        }
        return this.connections.get(channel);
    }
    
    createConnection(channel) {
        const connection = new Echo({
            broadcaster: 'pusher',
            key: process.env.MIX_PUSHER_APP_KEY,
            cluster: process.env.MIX_PUSHER_APP_CLUSTER,
            forceTLS: true,
            enabledTransports: ['ws', 'wss'],
        });
        
        connection.channel(channel)
            .listen('.reconnect', () => this.handleReconnect(channel));
            
        return connection;
    }
}
```

#### Message Batching
```php
// app/Services/MessageBatchingService.php
class MessageBatchingService
{
    private $messages = [];
    private $flushInterval = 100; // milliseconds
    
    public function addMessage($chatId, $message)
    {
        $this->messages[$chatId][] = $message;
        
        if (count($this->messages[$chatId]) >= 10) {
            $this->flush($chatId);
        }
    }
    
    public function flush($chatId)
    {
        if (empty($this->messages[$chatId])) return;
        
        broadcast(new MessageBatch($chatId, $this->messages[$chatId]));
        $this->messages[$chatId] = [];
    }
}
```

### 6. Media Optimization

#### Implement CDN
```php
// config/filesystems.php
'disks' => [
    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'options' => [
            'CacheControl' => 'max-age=31536000, public',
        ],
    ],
    'cdn' => [
        'driver' => 's3',
        'url' => env('CDN_URL', 'https://cdn.yoryor.com'),
    ],
],
```

#### Progressive Image Loading
```php
// app/Services/ImageOptimizationService.php
class ImageOptimizationService
{
    public function generateProgressiveVersions($image)
    {
        $versions = [
            'placeholder' => ['width' => 20, 'quality' => 20, 'blur' => 5],
            'thumbnail' => ['width' => 150, 'quality' => 70],
            'medium' => ['width' => 600, 'quality' => 80],
            'large' => ['width' => 1200, 'quality' => 85],
        ];
        
        foreach ($versions as $name => $config) {
            $this->createVersion($image, $name, $config);
        }
    }
}
```

### 7. Authentication Optimization

#### Token Caching
```php
// app/Services/TokenCacheService.php
class TokenCacheService
{
    public function cacheToken($token, $user)
    {
        Cache::put("token_{$token}", $user->id, 3600); // 1 hour
    }
    
    public function getUserFromToken($token)
    {
        $userId = Cache::get("token_{$token}");
        
        if ($userId) {
            return User::find($userId);
        }
        
        // Fallback to database
        $tokenRecord = PersonalAccessToken::findToken($token);
        if ($tokenRecord) {
            $this->cacheToken($token, $tokenRecord->tokenable);
            return $tokenRecord->tokenable;
        }
        
        return null;
    }
}
```

### 8. Monitoring & Alerts

#### Performance Monitoring
```php
// app/Services/PerformanceMonitor.php
class PerformanceMonitor
{
    public function trackApiResponse($route, $responseTime)
    {
        if ($responseTime > 1000) { // More than 1 second
            Log::warning("Slow API response", [
                'route' => $route,
                'response_time' => $responseTime,
                'user_id' => auth()->id(),
            ]);
            
            // Send alert
            dispatch(new SendSlowResponseAlert($route, $responseTime));
        }
        
        // Track in metrics
        Cache::increment("api_calls_{$route}");
        Cache::put("api_response_time_{$route}", $responseTime);
    }
}
```

### 9. Database Connection Pooling

```php
// config/database.php
'mysql' => [
    'driver' => 'mysql',
    'read' => [
        'host' => [
            env('DB_READ_HOST_1', '127.0.0.1'),
            env('DB_READ_HOST_2', '127.0.0.1'),
        ],
    ],
    'write' => [
        'host' => [
            env('DB_WRITE_HOST', '127.0.0.1'),
        ],
    ],
    'sticky' => true,
    'options' => [
        PDO::ATTR_PERSISTENT => true,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
    ],
],
```

### 10. Application-Level Optimizations

#### Implement Request Deduplication
```php
// app/Http/Middleware/PreventDuplicateRequests.php
class PreventDuplicateRequests
{
    public function handle($request, Closure $next)
    {
        $key = $this->getRequestKey($request);
        
        if (Cache::has($key)) {
            return response()->json(['error' => 'Duplicate request'], 429);
        }
        
        Cache::put($key, true, 5); // 5 seconds
        
        return $next($request);
    }
    
    private function getRequestKey($request)
    {
        return 'request_' . md5(
            $request->user()->id . 
            $request->path() . 
            json_encode($request->all())
        );
    }
}
```

## Implementation Priority

### Phase 1 (Immediate - Week 1)
1. Add database indexes
2. Implement basic caching
3. Fix N+1 queries
4. Enable response compression

### Phase 2 (Short-term - Week 2-3)
1. Implement Redis caching
2. Set up CDN for media
3. Optimize image processing
4. Add request deduplication

### Phase 3 (Medium-term - Month 1-2)
1. Implement message batching
2. Set up database read replicas
3. Add performance monitoring
4. Optimize queue processing

### Phase 4 (Long-term - Month 3+)
1. Implement table partitioning
2. Add ElasticSearch for search
3. Implement GraphQL for flexible queries
4. Consider microservices for heavy features

## Performance Metrics to Track

1. **API Response Times**
   - Target: < 200ms for simple queries
   - Target: < 500ms for complex queries

2. **Database Performance**
   - Query execution time
   - Connection pool usage
   - Slow query log

3. **Cache Hit Rates**
   - Target: > 80% for user profiles
   - Target: > 60% for match suggestions

4. **Queue Performance**
   - Job processing time
   - Queue depth
   - Failed job rate

5. **Real-time Performance**
   - WebSocket connection count
   - Message delivery latency
   - Broadcasting queue depth
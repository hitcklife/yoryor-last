# Presence Channel Implementation for User Online Status

## Overview

This implementation provides a comprehensive presence channel system for tracking users' online status in real-time. The system leverages Laravel Broadcasting with presence channels to track when users are online, offline, typing, and actively using the application.

## Features

- **Real-time Online Status**: Track when users are online/offline
- **Chat Presence**: See who is actively viewing specific chats
- **Typing Indicators**: Real-time typing status in chats
- **Match Presence**: Track online status of matched users
- **Activity Tracking**: Integration with existing user activity system
- **API Endpoints**: RESTful API for presence management
- **Cache-based**: Efficient caching for performance

## Architecture

### Components

1. **Presence Channels** (`routes/channels.php`)
   - `presence-online-users`: Global online users
   - `presence-chat.{chatId}`: Users active in specific chats
   - `presence-dating-active`: Users actively browsing for matches
   - `presence-user-matches.{userId}`: Online status of user's matches

2. **Services**
   - `PresenceService`: Core presence management
   - Handles online/offline status, caching, and cleanup

3. **Events**
   - `UserOnlineStatusChanged`: Broadcast status changes
   - `UserTypingStatusChanged`: Broadcast typing status

4. **Controllers**
   - `PresenceController`: API endpoints for presence management

5. **Middleware**
   - `UpdateUserPresence`: Auto-update presence on API calls

## Usage

### Client-Side Integration

#### 1. Subscribe to Presence Channels

```javascript
// Global online users
const presenceChannel = Echo.join('presence-online-users')
    .here((users) => {
        console.log('Currently online:', users);
    })
    .joining((user) => {
        console.log('User joined:', user);
    })
    .leaving((user) => {
        console.log('User left:', user);
    })
    .error((error) => {
        console.error('Presence channel error:', error);
    });

// Chat-specific presence
const chatPresence = Echo.join(`presence-chat.${chatId}`)
    .here((users) => {
        console.log('Users in chat:', users);
    })
    .joining((user) => {
        console.log('User joined chat:', user);
    })
    .leaving((user) => {
        console.log('User left chat:', user);
    });
```

#### 2. Listen for Status Changes

```javascript
// Listen for online status changes
Echo.private(`presence-user-matches.${userId}`)
    .listen('user.online.status.changed', (event) => {
        console.log('User status changed:', event);
        updateUserStatus(event.user_id, event.is_online);
    });

// Listen for typing status
Echo.private(`chat.${chatId}`)
    .listen('user.typing.status.changed', (event) => {
        console.log('Typing status:', event);
        updateTypingIndicator(event.user_id, event.is_typing);
    });
```

#### 3. Update User Status

```javascript
// Mark user as online
await axios.post('/api/v1/presence/status', {
    is_online: true
});

// Update typing status
await axios.post('/api/v1/presence/typing', {
    chat_id: chatId,
    is_typing: true
});

// Send heartbeat to keep user online
setInterval(async () => {
    await axios.post('/api/v1/presence/heartbeat');
}, 30000); // Every 30 seconds
```

### API Endpoints

#### User Status

```bash
# Get current user's online status
GET /api/v1/presence/status

# Update user's online status
POST /api/v1/presence/status
{
    "is_online": true
}

# Send heartbeat to keep user online
POST /api/v1/presence/heartbeat
```

#### Online Users

```bash
# Get all online users
GET /api/v1/presence/online-users

# Get online users in specific chat
GET /api/v1/presence/chats/{chatId}/online-users

# Get user's online matches
GET /api/v1/presence/online-matches
```

#### Typing Status

```bash
# Update typing status
POST /api/v1/presence/typing
{
    "chat_id": 123,
    "is_typing": true
}

# Get typing users in chat
GET /api/v1/presence/chats/{chatId}/typing-users
```

#### Statistics & Admin

```bash
# Get presence statistics
GET /api/v1/presence/statistics

# Get user's presence history
GET /api/v1/presence/history?days=7

# Admin: Sync online status from database
POST /api/v1/presence/sync

# Admin: Cleanup expired presence data
POST /api/v1/presence/cleanup
```

## Server-Side Usage

### User Model Methods

```php
// Check if user is online
$user->isOnline(); // Database-based (last 5 minutes)
$user->isOnlineViaPresence(); // Presence system-based

// Manage online status
$user->goOnline();
$user->goOffline();

// Get presence data
$presenceData = $user->getPresenceData();
$onlineMatches = $user->getOnlineMatches();

// Update typing status
$user->updateTypingStatus($chatId, true);

// Get detailed status
$status = $user->getOnlineStatus();
```

### PresenceService Methods

```php
$presenceService = app(PresenceService::class);

// Manage user presence
$presenceService->markUserOnline($user);
$presenceService->markUserOffline($user);
$presenceService->isUserOnline($userId);

// Get online users
$onlineUsers = $presenceService->getOnlineUsers();
$onlineInChat = $presenceService->getOnlineUsersInChat($chatId);
$onlineMatches = $presenceService->getOnlineMatches($user);

// Typing management
$presenceService->updateTypingStatus($user, $chatId, true);
$typingUsers = $presenceService->getTypingUsersInChat($chatId);

// Statistics
$stats = $presenceService->getOnlineStatistics();
```

## Configuration

### 1. Enable Broadcasting

Ensure broadcasting is configured in `config/broadcasting.php`:

```php
'default' => env('BROADCAST_DRIVER', 'pusher'),
```

### 2. Queue Configuration

For optimal performance, ensure events are queued:

```php
// In EventServiceProvider
protected $shouldBroadcast = true;
```

### 3. Cache Configuration

The presence system uses Redis for caching:

```php
// .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Middleware Registration

Add the presence middleware to `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ... other middleware
        \App\Http\Middleware\UpdateUserPresence::class,
    ],
];
```

## Performance Considerations

### Cache TTL Settings

```php
// PresenceService constants
private const ONLINE_STATUS_TTL = 300; // 5 minutes
private const PRESENCE_DATA_TTL = 60;  // 1 minute
```

### Cleanup Strategy

1. **Automatic Cleanup**: Expired presence data is cleaned automatically
2. **Scheduled Cleanup**: Run cleanup command periodically
3. **Database Sync**: Sync presence with database `last_active_at`

### Optimization Tips

1. **Heartbeat Frequency**: Send heartbeats every 30-60 seconds
2. **Channel Subscription**: Only subscribe to necessary channels
3. **Batch Updates**: Group multiple status updates when possible
4. **Caching**: Use Redis for high-performance caching

## Mobile App Integration

### iOS/Android Implementation

```javascript
// React Native with Laravel Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';

const echo = new Echo({
    broadcaster: 'pusher',
    key: 'your-pusher-key',
    cluster: 'your-cluster',
    auth: {
        headers: {
            Authorization: `Bearer ${userToken}`,
        },
    },
});

// Subscribe to presence channels
const presenceChannel = echo.join('presence-online-users');
const chatPresence = echo.join(`presence-chat.${chatId}`);
```

### Background Handling

```javascript
// Handle app state changes
import { AppState } from 'react-native';

AppState.addEventListener('change', (nextAppState) => {
    if (nextAppState === 'active') {
        // App came to foreground
        axios.post('/api/v1/presence/status', { is_online: true });
    } else if (nextAppState === 'background') {
        // App went to background
        axios.post('/api/v1/presence/status', { is_online: false });
    }
});
```

## Testing

### Unit Tests

```php
// Test presence service
public function test_user_can_be_marked_online()
{
    $user = User::factory()->create();
    $presenceService = app(PresenceService::class);
    
    $presenceService->markUserOnline($user);
    
    $this->assertTrue($presenceService->isUserOnline($user->id));
}

// Test presence channels
public function test_presence_channel_authorization()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/broadcasting/auth', [
            'channel_name' => 'presence-online-users',
        ]);
    
    $response->assertStatus(200);
}
```

### Integration Tests

```php
// Test typing status
public function test_typing_status_is_broadcasted()
{
    $user = User::factory()->create();
    $chat = Chat::factory()->create();
    
    $response = $this->actingAs($user)
        ->post('/api/v1/presence/typing', [
            'chat_id' => $chat->id,
            'is_typing' => true,
        ]);
    
    $response->assertStatus(200);
    Event::assertDispatched(UserTypingStatusChanged::class);
}
```

## Troubleshooting

### Common Issues

1. **Users Not Showing Online**
   - Check broadcasting configuration
   - Verify Redis connection
   - Ensure middleware is registered

2. **Presence Channels Not Working**
   - Check channel authorization
   - Verify authentication token
   - Check WebSocket connection

3. **Performance Issues**
   - Optimize cache TTL settings
   - Reduce heartbeat frequency
   - Implement cleanup strategies

### Debug Commands

```bash
# Check online users
php artisan tinker
>>> app(App\Services\PresenceService::class)->getOnlineUsers()

# Check presence data
>>> app(App\Services\PresenceService::class)->getPresenceData(1)

# Clean up expired data
>>> app(App\Services\PresenceService::class)->cleanupExpiredPresence()
```

## Migration Guide

### Database Changes

1. Run the new migration:
```bash
php artisan migrate
```

2. Update your existing activity tracking:
```php
// Update any existing activity tracking code
$user->logActivity('chat_presence_joined', ['chat_id' => $chatId]);
```

### Frontend Updates

1. Update your WebSocket listeners
2. Implement presence channel subscriptions
3. Add heartbeat mechanism
4. Update UI to show online status

## Security Considerations

1. **Channel Authorization**: Proper authorization in channel definitions
2. **Data Filtering**: Only expose necessary user data in presence channels
3. **Rate Limiting**: Implement rate limiting on presence endpoints
4. **Authentication**: Ensure all presence operations require authentication

This implementation provides a robust, scalable presence system that integrates seamlessly with your existing user activity tracking and chat system. 
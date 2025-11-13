# WebSocket & Real-Time Features Documentation

## Table of Contents
1. [Overview](#overview)
2. [Server Setup](#server-setup)
3. [Client Setup](#client-setup)
4. [Channel Types](#channel-types)
5. [Broadcasting Events](#broadcasting-events)
6. [Channel Authentication](#channel-authentication)
7. [Code Examples](#code-examples)
8. [Best Practices](#best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

YorYor uses **Laravel Reverb** as its WebSocket server to enable real-time features across the platform. Reverb is Laravel's official, first-party WebSocket server that provides seamless integration with Laravel's broadcasting system.

### Real-Time Architecture

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│  Laravel App    │────────▶│  Laravel Reverb  │────────▶│  Client Apps    │
│  (Port 8000)    │         │  (Port 8080)     │         │  (Web/Mobile)   │
└─────────────────┘         └──────────────────┘         └─────────────────┘
       │                             │                            │
       │ 1. Dispatch Event           │ 2. Broadcast Event         │
       │                             │                            │
       └─────────────────────────────┴────────────────────────────┘
                        3. Client receives and updates UI
```

### Broadcasting Flow

1. **Server-Side**: Laravel application dispatches an event implementing `ShouldBroadcast`
2. **Reverb Server**: Receives the event and broadcasts to subscribed channels
3. **Client-Side**: Echo client receives the event and triggers UI updates

### Key Features

- **Real-time messaging**: Instant message delivery with read receipts
- **Typing indicators**: See when other users are typing
- **Online presence**: Track user online/offline status
- **Video calling notifications**: Receive incoming call alerts
- **Match notifications**: Instant match alerts
- **Conversation updates**: Real-time conversation list updates

---

## Server Setup

### 1. Laravel Reverb Installation

Reverb comes pre-installed with Laravel 12. The package is configured in `config/reverb.php` and `config/broadcasting.php`.

### 2. Environment Variables

Configure the following variables in your `.env` file:

```bash
# Broadcasting Driver
BROADCAST_CONNECTION=reverb

# Laravel Reverb Server Configuration
REVERB_APP_ID=yoryor-app
REVERB_APP_KEY=yoryor-key-123456
REVERB_APP_SECRET=yoryor-secret-123456
REVERB_HOST=localhost              # Production: your-domain.com
REVERB_PORT=8080                   # WebSocket port
REVERB_SCHEME=http                 # Production: https

# Server Binding (Internal)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Reverb Performance Settings
REVERB_MAX_REQUEST_SIZE=10000
REVERB_APP_PING_INTERVAL=60
REVERB_APP_ACTIVITY_TIMEOUT=30
REVERB_APP_MAX_MESSAGE_SIZE=10000

# Vite Configuration (for client-side)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. Broadcasting Configuration

**File**: `config/broadcasting.php`

```php
'default' => env('BROADCAST_CONNECTION', 'reverb'),

'connections' => [
    'reverb' => [
        'driver' => 'reverb',
        'key' => env('REVERB_APP_KEY'),
        'secret' => env('REVERB_APP_SECRET'),
        'app_id' => env('REVERB_APP_ID'),
        'options' => [
            'host' => env('REVERB_HOST'),
            'port' => env('REVERB_PORT', 443),
            'scheme' => env('REVERB_SCHEME', 'https'),
            'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
        ],
    ],
],
```

### 4. Running Reverb Server

**Development:**

```bash
# Start Reverb server
php artisan reverb:start

# With debugging output
php artisan reverb:start --debug

# On specific host/port
php artisan reverb:start --host=0.0.0.0 --port=8080
```

**Production:**

Use a process manager like Supervisor to keep Reverb running:

```ini
[program:yoryor-reverb]
command=php /path/to/your/app/artisan reverb:start
directory=/path/to/your/app
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/yoryor-reverb.log
```

### 5. Queue Configuration

Reverb requires a queue worker to process broadcast events:

```bash
# Development
php artisan queue:listen --tries=1

# Production (use Supervisor)
php artisan queue:work --daemon
```

### 6. Starting All Services

**Using Composer Script** (Recommended):

```bash
composer dev
```

This runs Laravel server + Reverb + Queue worker + Vite concurrently.

**Manual Start:**

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Reverb WebSocket Server
php artisan reverb:start

# Terminal 3: Queue Worker
php artisan queue:listen --tries=1

# Terminal 4: Vite Dev Server
npm run dev
```

---

## Client Setup

### 1. Laravel Echo Configuration (Web)

**File**: `resources/js/echo.js`

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Get CSRF token for authentication
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Initialize Echo instance
window.Echo = new Echo({
    broadcaster: 'pusher',  // Uses Pusher protocol
    key: import.meta.env.VITE_REVERB_APP_KEY,
    cluster: '',  // Empty for Reverb (not using Pusher service)
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    disableStats: true,  // Disable Pusher analytics
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': token,
        },
    },
});
```

**Include in your main JavaScript file** (`resources/js/app.js`):

```javascript
import './echo';
```

### 2. React Native / Expo Setup

```bash
npm install pusher-js laravel-echo
```

**Echo Configuration**:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';

// Get auth token from storage
const authToken = await AsyncStorage.getItem('auth_token');

const echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.EXPO_PUBLIC_REVERB_APP_KEY,
    cluster: '',
    wsHost: process.env.EXPO_PUBLIC_REVERB_HOST,
    wsPort: process.env.EXPO_PUBLIC_REVERB_PORT || 80,
    wssPort: process.env.EXPO_PUBLIC_REVERB_PORT || 443,
    forceTLS: process.env.EXPO_PUBLIC_REVERB_SCHEME === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${API_URL}/broadcasting/auth`,
    auth: {
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'Accept': 'application/json',
        },
    },
});

export default echo;
```

### 3. Connection Verification

**Check if Echo is initialized:**

```javascript
if (window.Echo) {
    console.log('✅ Echo is available globally');
} else {
    console.error('❌ Echo failed to initialize');
}
```

**Monitor connection state:**

```javascript
const pusher = window.Echo.connector.pusher;

pusher.connection.bind('connected', () => {
    console.log('✅ WebSocket connected to Reverb');
});

pusher.connection.bind('disconnected', () => {
    console.log('❌ WebSocket disconnected');
});

pusher.connection.bind('error', (error) => {
    console.error('WebSocket error:', error);
});
```

---

## Channel Types

YorYor uses three types of WebSocket channels:

### 1. Public Channels

Public channels are accessible without authentication.

**Not currently used in YorYor** (all channels require authentication for security).

### 2. Private Channels

Private channels require authentication and are the primary channel type used in YorYor.

**Naming Convention**: `private-{resource}.{id}` or `{resource}.{id}`

**Examples:**

- `chat.123` - Chat channel for chat ID 123
- `user.456` - User-specific channel for user ID 456

**Subscription (JavaScript):**

```javascript
Echo.private('chat.123')
    .listen('NewMessage', (e) => {
        console.log('New message:', e);
    });
```

**Subscription (React Native):**

```javascript
echo.private(`user.${userId}`)
    .listen('NewMatch', (e) => {
        showMatchNotification(e.match);
    });
```

### 3. Presence Channels

Presence channels track which users are subscribed to a channel and provide member lists.

**Naming Convention**: `presence-{resource}.{id}`

**Examples:**

- `presence-chat.123` - Presence tracking for chat ID 123
- `presence-online-users` - Global online users tracking

**Subscription:**

```javascript
Echo.join('presence-chat.123')
    .here((users) => {
        console.log('Users currently in chat:', users);
    })
    .joining((user) => {
        console.log('User joined chat:', user.name);
    })
    .leaving((user) => {
        console.log('User left chat:', user.name);
    });
```

### Channel Authorization

All private and presence channels require authorization defined in `routes/channels.php`.

---

## Broadcasting Events

YorYor broadcasts the following events for real-time functionality:

### 1. NewMessageEvent

**Purpose**: Broadcast new chat messages to chat participants

**Channels**:
- `private-chat.{chatId}`
- `private-user.{userId}` (for each participant except sender)

**Event Class**: `App\Events\NewMessageEvent`

**Payload:**

```json
{
    "message": {
        "id": 123,
        "chat_id": 45,
        "sender_id": 10,
        "content": "Hello!",
        "type": "text",
        "metadata": {},
        "is_read": false,
        "created_at": "2025-10-07T12:00:00.000000Z",
        "updated_at": "2025-10-07T12:00:00.000000Z"
    },
    "sender": {
        "id": 10,
        "name": "John Doe",
        "profile_photo": "https://example.com/photo.jpg"
    },
    "chat": {
        "id": 45,
        "name": "John & Jane",
        "last_activity_at": "2025-10-07T12:00:00.000000Z"
    },
    "timestamp": "2025-10-07T12:00:00+00:00"
}
```

**Broadcast Name**: `NewMessage`

---

### 2. UserTyping

**Purpose**: Show typing indicators in real-time

**Channels**: `private-chat.{chatId}`

**Event Class**: `App\Events\UserTyping`

**Payload:**

```json
{
    "user_id": 10,
    "user_name": "John",
    "is_typing": true,
    "chat_id": 45
}
```

**Broadcast Name**: `user.typing`

---

### 3. MessageReadEvent

**Purpose**: Update read receipts when messages are read

**Channels**: `private-chat.{chatId}`

**Event Class**: `App\Events\MessageReadEvent`

**Payload:**

```json
{
    "chat_id": 45,
    "user_id": 10,
    "count": 5,
    "timestamp": "2025-10-07T12:00:00+00:00"
}
```

**Broadcast Name**: `messages.read`

---

### 4. ConversationUpdated

**Purpose**: Update conversation list without full reload

**Channels**: `private-user.{userId}`

**Event Class**: `App\Events\ConversationUpdated`

**Payload:**

```json
{
    "type": "new_message",
    "data": {
        "chat_id": 45,
        "message": {...},
        "unread_count": 3
    }
}
```

**Broadcast Name**: `conversation.updated`

---

### 5. CallInitiatedEvent

**Purpose**: Notify user of incoming video/audio call

**Channels**: `private-user.{receiverId}`

**Event Class**: `App\Events\CallInitiatedEvent`

**Payload:**

```json
{
    "call": {
        "id": 789,
        "channel_name": "call-123-456",
        "type": "video",
        "status": "initiated",
        "caller": {
            "id": 10,
            "name": "John Doe",
            "profile_photo_url": "https://example.com/photo.jpg"
        },
        "created_at": "2025-10-07T12:00:00.000000Z"
    }
}
```

**Broadcast Name**: `CallInitiated`

---

### 6. CallStatusChangedEvent

**Purpose**: Notify call status changes (answered, ended, missed)

**Channels**: Both caller and receiver user channels

**Event Class**: `App\Events\CallStatusChangedEvent`

**Payload:**

```json
{
    "call_id": 789,
    "status": "answered",
    "timestamp": "2025-10-07T12:00:00+00:00"
}
```

---

### 7. NewMatchEvent

**Purpose**: Notify users when they match with someone

**Channels**:
- `private-user.{initiatorId}`
- `private-user.{receiverId}`

**Event Class**: `App\Events\NewMatchEvent`

**Payload:**

```json
{
    "match": {
        "id": 456,
        "matched_at": "2025-10-07T12:00:00.000000Z",
        "initiator": {
            "id": 10,
            "name": "John Doe",
            "profile_photo": "https://example.com/photo.jpg",
            "age": 28,
            "city": "New York"
        },
        "receiver": {
            "id": 20,
            "name": "Jane Smith",
            "profile_photo": "https://example.com/photo.jpg",
            "age": 26,
            "city": "Los Angeles"
        }
    },
    "timestamp": "2025-10-07T12:00:00+00:00"
}
```

**Broadcast Name**: `NewMatch`

---

### 8. NewLikeEvent

**Purpose**: Notify user when someone likes their profile (Premium feature)

**Channels**: `private-user.{likedUserId}`

**Event Class**: `App\Events\NewLikeEvent`

---

### 9. UserOnlineStatusChanged

**Purpose**: Broadcast user online/offline status to matches and active chats

**Channels**:
- `presence-chat.{chatId}` (for active chats)
- `presence-online-users` (global)

**Event Class**: `App\Events\UserOnlineStatusChanged`

**Payload:**

```json
{
    "user_id": 10,
    "user_name": "John Doe",
    "user_avatar": "https://example.com/photo.jpg",
    "is_online": true,
    "last_active_at": "2025-10-07T12:00:00+00:00",
    "status_changed_at": "2025-10-07T12:00:00+00:00",
    "status": "online"
}
```

**Broadcast Name**: `user.online.status.changed`

---

### 10. MessageEditedEvent / MessageDeletedEvent

**Purpose**: Real-time message edits and deletions

**Channels**: `private-chat.{chatId}`

**Event Classes**:
- `App\Events\MessageEditedEvent`
- `App\Events\MessageDeletedEvent`

---

## Channel Authentication

All private and presence channels require server-side authorization.

### Authorization Rules

**File**: `routes/channels.php`

#### 1. User Channel Authorization

Only the user themselves can subscribe to their own channel:

```php
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

#### 2. Chat Channel Authorization

User must be a participant in the chat to subscribe:

```php
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    if (!$user) {
        $user = request()->user();
    }

    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    // Check if user is part of this chat
    return $chat->users()->where('user_id', $user->id)->exists();
});
```

#### 3. Presence Chat Channel Authorization

Returns user information for presence tracking:

```php
Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    if (!$user) {
        return false;
    }

    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    // Check if user is part of this chat
    $isChatMember = $chat->users()->where('user_id', $user->id)->exists();
    if (!$isChatMember) {
        return false;
    }

    // Update user's last active timestamp
    $user->updateLastActive();

    // Load profile if not already loaded
    if (!$user->relationLoaded('profile')) {
        $user->load('profile');
    }

    // Return user info for presence
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->getProfilePhotoUrl(),
        'is_online' => true,
        'is_typing' => false,
        'joined_chat_at' => now()->toISOString(),
        'last_active_at' => $user->last_active_at?->toISOString()
    ];
});
```

### Authentication Flow

1. Client attempts to subscribe to a private/presence channel
2. Echo sends POST request to `/broadcasting/auth` with channel name
3. Laravel checks authorization callback in `routes/channels.php`
4. If authorized, returns signature; otherwise returns 403
5. Client receives authorization and completes subscription

---

## Code Examples

### Server-Side: Creating and Broadcasting Events

#### Example 1: Broadcasting a New Message

**In Controller** (`app/Http/Controllers/Api/V1/ChatController.php`):

```php
use App\Events\NewMessageEvent;
use App\Events\ConversationUpdated;
use Illuminate\Support\Facades\DB;

public function sendMessage(Request $request, $chatId)
{
    $request->validate([
        'content' => 'required|string|max:5000',
        'type' => 'nullable|in:text,image,video,audio,file',
    ]);

    DB::beginTransaction();
    try {
        // Create message
        $message = Message::create([
            'chat_id' => $chatId,
            'sender_id' => auth()->id(),
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

        // Load relationships
        $message->load(['sender.profile', 'sender.profilePhoto']);

        // Update chat last activity
        $chat = Chat::find($chatId);
        $chat->updateLastActivity();

        // Broadcast to chat participants
        event(new NewMessageEvent($message));

        // Update conversation lists
        $chatUsers = $chat->users()->pluck('user_id');
        foreach ($chatUsers as $userId) {
            if ($userId != auth()->id()) {
                event(new ConversationUpdated($userId, 'new_message', [
                    'chat_id' => $chatId,
                    'message' => $message,
                    'unread_count' => 1
                ]));
            }
        }

        DB::commit();

        return response()->json([
            'status' => 'success',
            'data' => new MessageResource($message)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### Example 2: Broadcasting Typing Indicator

```php
use App\Events\UserTyping;

public function typing(Request $request, $chatId)
{
    $request->validate([
        'is_typing' => 'required|boolean',
    ]);

    event(new UserTyping(
        auth()->user(),
        $chatId,
        $request->is_typing
    ));

    return response()->json(['status' => 'success']);
}
```

#### Example 3: Creating a Broadcast Event

**File**: `app/Events/NewMessageEvent.php`

```php
<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message;

        // Load relationships for broadcast
        $this->message->load([
            'sender.profile',
            'sender.profilePhoto'
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        $chat = $this->message->chat;
        $userIds = $chat->users()->pluck('user_id')->toArray();

        $channels = [
            new PrivateChannel('chat.' . $this->message->chat_id)
        ];

        // Add user channels for each participant (except sender)
        foreach ($userIds as $userId) {
            if ($userId != $this->message->sender_id) {
                $channels[] = new PrivateChannel('user.' . $userId);
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'NewMessage';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'created_at' => $this->message->created_at,
            ],
            'sender' => [
                'id' => $this->message->sender_id,
                'name' => $this->message->sender->full_name,
                'profile_photo' => $this->message->sender->getProfilePhotoUrl(),
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}
```

---

### Client-Side: Listening to Events

#### Example 1: Web App - Chat Messages (JavaScript)

**File**: `resources/js/messages.js`

```javascript
// Subscribe to user's personal channel
const userId = document.querySelector('meta[name="user-id"]')?.content;

Echo.private(`user.${userId}`)
    .listen('.conversation.updated', (e) => {
        console.log('Conversation updated:', e);

        // Dispatch to Livewire component
        if (window.Livewire) {
            Livewire.dispatch('conversationUpdated', [e]);
        }
    });

// Subscribe to active chat channel
let currentChatChannel = null;

function joinChatChannel(chatId) {
    // Leave previous channel
    if (currentChatChannel) {
        Echo.leave(`chat.${currentChatChannel}`);
    }

    currentChatChannel = chatId;

    // Subscribe to new chat
    Echo.private(`chat.${chatId}`)
        .listen('.message.sent', (e) => {
            console.log('New message:', e);

            // Update UI via Livewire
            Livewire.dispatch('newMessageReceived', [e]);
        })
        .listen('.user.typing', (e) => {
            console.log('User typing:', e);

            // Show/hide typing indicator
            Livewire.dispatch('userTyping', [e]);
        })
        .listen('.messages.read', (e) => {
            console.log('Messages read:', e);

            // Update read receipts
            Livewire.dispatch('messagesRead', [e]);
        });
}

// Listen for chat selection
Livewire.on('chatSelected', (data) => {
    const eventData = Array.isArray(data) ? data[0] : data;
    if (eventData && eventData.chatId) {
        joinChatChannel(eventData.chatId);
    }
});
```

#### Example 2: React Native - Match Notifications

```javascript
import echo from './echo';
import { useEffect } from 'react';

function MatchNotifications({ userId }) {
    useEffect(() => {
        // Subscribe to user channel
        const channel = echo.private(`user.${userId}`);

        channel.listen('NewMatch', (e) => {
            console.log('New match received:', e.match);

            // Show notification
            showMatchNotification({
                title: 'New Match!',
                body: `You matched with ${e.match.receiver.name}`,
                data: e.match
            });

            // Update matches list
            updateMatchesList(e.match);
        });

        // Cleanup on unmount
        return () => {
            echo.leave(`user.${userId}`);
        };
    }, [userId]);

    return null;
}
```

#### Example 3: Presence Tracking (Web)

```javascript
// Join presence channel
Echo.join(`presence-chat.${chatId}`)
    .here((users) => {
        console.log('Users currently online:', users);
        updateOnlineUsersList(users);
    })
    .joining((user) => {
        console.log('User joined:', user.name);
        addUserToOnlineList(user);
        showNotification(`${user.name} is now online`);
    })
    .leaving((user) => {
        console.log('User left:', user.name);
        removeUserFromOnlineList(user);
    })
    .error((error) => {
        console.error('Presence channel error:', error);
    });
```

#### Example 4: Typing Indicator Implementation

**Trigger typing event** (throttled):

```javascript
let typingTimeout = null;

function handleTyping() {
    // Send typing start
    axios.post(`/api/v1/chats/${chatId}/typing`, {
        is_typing: true
    });

    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }

    // Send typing stop after 3 seconds of inactivity
    typingTimeout = setTimeout(() => {
        axios.post(`/api/v1/chats/${chatId}/typing`, {
            is_typing: false
        });
    }, 3000);
}

// Attach to input
document.getElementById('messageInput').addEventListener('input', handleTyping);
```

**Receive typing events:**

```javascript
Echo.private(`chat.${chatId}`)
    .listen('.user.typing', (e) => {
        const typingIndicator = document.getElementById('typingIndicator');

        if (e.is_typing && e.user_id !== currentUserId) {
            typingIndicator.textContent = `${e.user_name} is typing...`;
            typingIndicator.classList.remove('hidden');
        } else {
            typingIndicator.classList.add('hidden');
        }
    });
```

---

## Best Practices

### 1. Connection Management

#### Auto-Reconnection

Echo automatically handles reconnections, but you can monitor the state:

```javascript
const pusher = window.Echo.connector.pusher;

pusher.connection.bind('state_change', (states) => {
    console.log(`Connection state: ${states.previous} -> ${states.current}`);

    if (states.current === 'connected') {
        // Resubscribe to channels if needed
        resubscribeToChannels();
    }
});
```

#### Clean Up Subscriptions

Always leave channels when navigating away:

```javascript
// React/Vue component unmount
useEffect(() => {
    const channel = Echo.private(`chat.${chatId}`);

    return () => {
        Echo.leave(`chat.${chatId}`);
    };
}, [chatId]);

// Page navigation
window.addEventListener('beforeunload', () => {
    Echo.leaveAllChannels();
});
```

### 2. Error Handling

#### Server-Side

Wrap event dispatching in try-catch:

```php
try {
    event(new NewMessageEvent($message));
} catch (\Exception $e) {
    \Log::error('Failed to broadcast message event', [
        'message_id' => $message->id,
        'error' => $e->getMessage()
    ]);

    // Event failed but don't fail the request
}
```

#### Client-Side

Handle subscription errors:

```javascript
Echo.private(`chat.${chatId}`)
    .error((error) => {
        console.error('Channel subscription error:', error);

        // Retry subscription after delay
        setTimeout(() => {
            retryChannelSubscription(chatId);
        }, 5000);
    });
```

### 3. Performance Optimization

#### Minimize Payload Size

Only send necessary data in broadcasts:

```php
public function broadcastWith()
{
    return [
        'id' => $this->message->id,
        'content' => $this->message->content,
        // Don't send entire models
    ];
}
```

#### Use Queued Broadcasting

Events implementing `ShouldBroadcast` are automatically queued. Ensure queue worker is running.

#### Throttle High-Frequency Events

Throttle typing indicators to avoid excessive broadcasts:

```javascript
import { throttle } from 'lodash';

const sendTypingEvent = throttle(() => {
    axios.post(`/api/v1/chats/${chatId}/typing`, { is_typing: true });
}, 1000); // Max once per second
```

### 4. Security Best Practices

#### Always Validate Channel Authorization

```php
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // ALWAYS verify user has access
    $chat = Chat::find($chatId);
    return $chat && $chat->users()->where('user_id', $user->id)->exists();
});
```

#### Don't Broadcast Sensitive Data

```php
// BAD - exposes password hash
public function broadcastWith()
{
    return ['user' => $this->user->toArray()];
}

// GOOD - only necessary data
public function broadcastWith()
{
    return [
        'user' => [
            'id' => $this->user->id,
            'name' => $this->user->full_name,
        ]
    ];
}
```

#### Use HTTPS in Production

```env
REVERB_SCHEME=https
VITE_REVERB_SCHEME=https
```

### 5. Debugging

#### Enable Debug Mode

```bash
php artisan reverb:start --debug
```

#### Check Active Channels

```javascript
window.checkChannels = function() {
    const pusher = window.Echo.connector.pusher;
    const channels = Object.keys(pusher.channels.channels);
    console.log('Active channels:', channels);
    return channels;
};

// Call in console
window.checkChannels();
```

#### Monitor with Laravel Telescope

View broadcasted events in Telescope:
- Navigate to `http://localhost:8000/telescope`
- Click on "Events" tab
- Filter by broadcast events

---

## Troubleshooting

### Problem: WebSocket Connection Failed

**Symptoms:**
- Console error: "WebSocket connection failed"
- No real-time updates

**Solutions:**

1. **Verify Reverb is running:**
   ```bash
   php artisan reverb:start
   ```

2. **Check port availability:**
   ```bash
   lsof -i :8080
   # Kill if port is in use
   kill -9 <PID>
   ```

3. **Verify environment variables:**
   ```bash
   # In .env
   BROADCAST_CONNECTION=reverb
   REVERB_HOST=localhost
   REVERB_PORT=8080

   # Match in Vite vars
   VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
   VITE_REVERB_HOST="${REVERB_HOST}"
   VITE_REVERB_PORT="${REVERB_PORT}"
   ```

4. **Rebuild frontend assets:**
   ```bash
   npm run dev
   # or
   npm run build
   ```

### Problem: Authorization Failed (403)

**Symptoms:**
- Console error: "403 Forbidden" when subscribing to channels
- Private channels not working

**Solutions:**

1. **Check user authentication:**
   ```javascript
   // Ensure user is logged in
   const token = document.querySelector('meta[name="csrf-token"]')?.content;
   console.log('CSRF Token:', token);
   ```

2. **Verify channel authorization logic:**
   ```php
   // routes/channels.php
   Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
       \Log::info('Channel auth attempt', [
           'user_id' => $user->id,
           'chat_id' => $chatId
       ]);

       // Your authorization logic
   });
   ```

3. **Check auth endpoint:**
   ```bash
   # Test broadcasting auth endpoint
   curl -X POST http://localhost:8000/broadcasting/auth \
     -H "X-CSRF-TOKEN: your-token" \
     -H "Cookie: your-session-cookie" \
     -d "channel_name=private-chat.123"
   ```

### Problem: Events Not Broadcasting

**Symptoms:**
- Events dispatched but not received on client
- No errors in logs

**Solutions:**

1. **Verify queue worker is running:**
   ```bash
   php artisan queue:listen --tries=1
   ```

2. **Check event implements ShouldBroadcast:**
   ```php
   class NewMessageEvent implements ShouldBroadcast
   {
       // ...
   }
   ```

3. **Verify broadcast connection:**
   ```bash
   # In .env
   BROADCAST_CONNECTION=reverb  # Not 'null' or 'log'
   ```

4. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

5. **Check failed jobs:**
   ```bash
   php artisan queue:failed
   php artisan queue:retry all
   ```

### Problem: Messages Received Multiple Times

**Symptoms:**
- Duplicate messages appearing
- Events triggered multiple times

**Solutions:**

1. **Ensure channel is only subscribed once:**
   ```javascript
   // Leave channel before re-subscribing
   Echo.leave(`chat.${chatId}`);
   Echo.private(`chat.${chatId}`).listen(...);
   ```

2. **Check for multiple Echo initializations:**
   ```javascript
   // Only initialize Echo once
   if (!window.Echo) {
       window.Echo = new Echo({...});
   }
   ```

3. **Verify event listener cleanup:**
   ```javascript
   // Remove listeners before adding new ones
   Echo.private(`chat.${chatId}`)
       .stopListening('.message.sent')
       .listen('.message.sent', handler);
   ```

### Problem: Typing Indicators Not Working

**Symptoms:**
- Typing events not received
- Indicators not showing

**Solutions:**

1. **Check event broadcasting:**
   ```php
   // Ensure event is broadcast
   event(new UserTyping($user, $chatId, true));
   ```

2. **Verify event listener:**
   ```javascript
   Echo.private(`chat.${chatId}`)
       .listen('.user.typing', (e) => {
           console.log('Typing event:', e);
       });
   ```

3. **Check broadcast name:**
   ```php
   // In UserTyping event
   public function broadcastAs()
   {
       return 'user.typing';  // Must match listener
   }
   ```

### Problem: Presence Channel Issues

**Symptoms:**
- `.here()`, `.joining()`, `.leaving()` not triggering
- User list not updating

**Solutions:**

1. **Verify channel type is presence:**
   ```javascript
   // Use Echo.join() for presence channels
   Echo.join('presence-chat.123')  // Not Echo.private()
   ```

2. **Check authorization returns user data:**
   ```php
   Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
       // Must return user data array
       return [
           'id' => $user->id,
           'name' => $user->full_name,
       ];
   });
   ```

### Problem: High Latency / Slow Updates

**Symptoms:**
- Messages take several seconds to appear
- Real-time features feel laggy

**Solutions:**

1. **Check queue worker performance:**
   ```bash
   # Use Redis for better queue performance
   QUEUE_CONNECTION=redis

   # Start multiple workers
   php artisan queue:work --queue=default,broadcasts --tries=3
   ```

2. **Optimize event payload size:**
   ```php
   public function broadcastWith()
   {
       // Only send essential data
       return [
           'id' => $this->message->id,
           'content' => $this->message->content,
       ];
   }
   ```

3. **Enable Redis scaling for Reverb:**
   ```env
   REVERB_SCALING_ENABLED=true
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   ```

### Problem: Disconnect After Inactivity

**Symptoms:**
- WebSocket disconnects after period of inactivity
- Must refresh to reconnect

**Solutions:**

1. **Configure ping interval:**
   ```env
   REVERB_APP_PING_INTERVAL=60
   REVERB_APP_ACTIVITY_TIMEOUT=30
   ```

2. **Implement auto-reconnect:**
   ```javascript
   const pusher = window.Echo.connector.pusher;

   pusher.connection.bind('disconnected', () => {
       console.log('Disconnected, attempting to reconnect...');
       pusher.connect();
   });
   ```

---

## Performance Metrics

### Expected Performance

- **Message Delivery Latency**: < 100ms
- **Typing Indicator Response**: < 50ms
- **Read Receipts**: Instant (< 50ms)
- **Connection Establishment**: < 2 seconds
- **Memory Usage**: ~20-50 MB per 100 concurrent connections

### Monitoring

Use **Laravel Pulse** to monitor real-time performance:

```bash
# Access Pulse dashboard
http://localhost:8000/pulse
```

Track:
- WebSocket connections
- Broadcast event frequency
- Queue processing time
- Failed broadcasts

---

## Additional Resources

- [Laravel Broadcasting Documentation](https://laravel.com/docs/12.x/broadcasting)
- [Laravel Reverb Documentation](https://laravel.com/docs/12.x/reverb)
- [Laravel Echo Documentation](https://github.com/laravel/echo)
- [Pusher Protocol Documentation](https://pusher.com/docs/channels/library_auth_reference/pusher-websockets-protocol)

---

## Summary

YorYor's WebSocket implementation using Laravel Reverb provides:

- Real-time messaging with read receipts
- Typing indicators
- Online presence tracking
- Video call notifications
- Match notifications
- Optimized performance with minimal latency
- Secure channel authorization
- Scalable architecture

For support or questions, refer to the [main documentation](/documentation) or contact the development team.

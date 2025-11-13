# Chat & Messaging Documentation

## Overview

YorYor's chat and messaging system is built on Laravel Reverb WebSocket technology, providing real-time communication between matched users. The system supports text messages, media sharing, read receipts, typing indicators, and comprehensive chat management features.

---

## Real-Time Messaging Architecture

### WebSocket Infrastructure

**Technology Stack:**
- **Laravel Reverb**: WebSocket server (port 8080)
- **Laravel Broadcasting**: Event broadcasting system
- **Laravel Echo**: Client-side WebSocket listener
- **Pusher Protocol**: Compatible protocol for easy scaling

**Architecture Flow:**
```
User sends message → Laravel API → Stores in database → Broadcasts event →
Reverb Server → Echo Client → Recipient receives message
```

**Configuration:**
```env
# Laravel Reverb
REVERB_APP_ID=yoryor-app
REVERB_APP_KEY=yoryor-key-123456
REVERB_APP_SECRET=yoryor-secret-123456
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite (for Echo client)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Starting Reverb Server:**
```bash
php artisan reverb:start
```

### Broadcasting Events

**NewMessageEvent:**
```php
namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageEvent implements ShouldBroadcast
{
    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->message->chat_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'sender_id' => $this->message->user_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'created_at' => $this->message->created_at->toISOString(),
                'sender' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->first_name,
                    'avatar' => $this->message->user->primary_photo_url,
                ],
            ],
        ];
    }
}
```

**Client-Side Listening (resources/js/messages.js):**
```javascript
import Echo from 'laravel-echo';

// Connect to chat channel
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        // Add message to UI
        addMessageToChat(e.message);

        // Play notification sound
        playNotificationSound();

        // Update unread count
        updateUnreadCount(chatId);
    })
    .listenForWhisper('typing', (e) => {
        showTypingIndicator(e.user);
    });
```

### Channel Authorization

**routes/channels.php:**
```php
use App\Models\Chat;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Ensure user is participant in this chat
    $chat = Chat::find($chatId);
    return $chat && $chat->users->contains($user->id);
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    // User can only listen to their own private channel
    return (int) $user->id === (int) $userId;
});
```

---

## Message Types

### 1. Text Messages

**Standard text message with support for emojis and Unicode**

**Database Schema:**
```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY,
    chat_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    type ENUM('text', 'image', 'video', 'voice', 'location', 'file') DEFAULT 'text',
    content TEXT,  -- Message text or media URL
    metadata JSON,  -- Additional data (file size, duration, etc.)
    replied_to_id BIGINT NULL,  -- Reply to message
    edited_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    INDEX idx_chat_created (chat_id, created_at),
    INDEX idx_user_chat (user_id, chat_id)
);
```

**Sending Text Message:**
```php
public function sendMessage(Request $request, int $chatId): JsonResponse
{
    $validated = $request->validate([
        'content' => 'required|string|max:5000',
        'replied_to_id' => 'nullable|exists:messages,id',
    ]);

    DB::beginTransaction();
    try {
        $message = Message::create([
            'chat_id' => $chatId,
            'user_id' => auth()->id(),
            'type' => 'text',
            'content' => $validated['content'],
            'replied_to_id' => $validated['replied_to_id'] ?? null,
        ]);

        // Update chat's last message
        Chat::where('id', $chatId)->update([
            'last_message_id' => $message->id,
            'updated_at' => now(),
        ]);

        // Broadcast event
        event(new NewMessageEvent($message));

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => MessageResource::make($message),
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages
Content-Type: application/json

{
  "content": "As-salamu alaykum! How are you?",
  "replied_to_id": 123  // Optional
}
```

### 2. Image Messages

**Send photos with inline preview**

**Features:**
- Automatic image optimization (max 1920x1080)
- Thumbnail generation (200x200)
- Upload to Cloudflare R2
- Inline preview in chat
- Full-screen view on click

**Sending Image:**
```php
public function sendImage(Request $request, int $chatId): JsonResponse
{
    $validated = $request->validate([
        'image' => 'required|image|mimes:jpeg,png,webp|max:5120',  // 5MB max
        'caption' => 'nullable|string|max:500',
    ]);

    DB::beginTransaction();
    try {
        // Upload and optimize image
        $upload = app(MediaUploadService::class)->upload(
            $validated['image'],
            'chat-images'
        );

        $message = Message::create([
            'chat_id' => $chatId,
            'user_id' => auth()->id(),
            'type' => 'image',
            'content' => $upload['url'],
            'metadata' => json_encode([
                'thumbnail_url' => $upload['thumbnail_url'],
                'caption' => $validated['caption'] ?? null,
                'width' => $upload['width'],
                'height' => $upload['height'],
                'size' => $upload['size'],
            ]),
        ]);

        Chat::where('id', $chatId)->update([
            'last_message_id' => $message->id,
            'updated_at' => now(),
        ]);

        event(new NewMessageEvent($message));

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => MessageResource::make($message),
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages/image
Content-Type: multipart/form-data

image: (binary file)
caption: "Beautiful sunset at the masjid"
```

### 3. Video Messages

**Share video clips (up to 60 seconds)**

**Constraints:**
- Maximum duration: 60 seconds
- Maximum file size: 50MB
- Formats: MP4, MOV, WEBM
- Automatic compression and optimization

**Metadata Stored:**
```json
{
  "thumbnail_url": "https://r2.../thumbnail.jpg",
  "duration": 45,  // seconds
  "width": 1920,
  "height": 1080,
  "size": 12345678,  // bytes
  "codec": "h264"
}
```

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages/video
```

### 4. Voice Messages

**Record and send voice notes**

**Features:**
- Maximum duration: 2 minutes
- Format: MP3, AAC
- Waveform visualization
- Playback speed control (1x, 1.5x, 2x)

**Metadata:**
```json
{
  "duration": 87,  // seconds
  "waveform": [0.2, 0.5, 0.8, ...],  // Amplitude data for visualization
  "size": 234567
}
```

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages/voice
```

### 5. Location Sharing

**Share current location or custom location**

**Privacy:**
- Approximate location (not exact address)
- User must grant permission
- One-time share (not continuous tracking)

**Metadata:**
```json
{
  "latitude": 40.7128,
  "longitude": -74.0060,
  "address": "New York, NY, USA",  // Approximate
  "place_name": "Central Park"  // Optional
}
```

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages/location
```

### 6. File Attachments

**Share documents (PDFs only for safety)**

**Constraints:**
- Format: PDF only
- Maximum size: 10MB
- Virus scanning before storage

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/messages/file
```

---

## Chat Features

### Read Receipts

**Track when messages are seen**

**Database Schema:**
```sql
CREATE TABLE message_reads (
    id BIGINT PRIMARY KEY,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    chat_id BIGINT NOT NULL,
    read_at TIMESTAMP,
    INDEX idx_message_user (message_id, user_id),
    INDEX idx_chat_user (chat_id, user_id),
    UNIQUE KEY unique_read (message_id, user_id)
);
```

**Marking Messages as Read:**
```php
public function markAsRead(int $chatId): JsonResponse
{
    $userId = auth()->id();

    // Get all unread messages in this chat
    $unreadMessageIds = Message::where('chat_id', $chatId)
        ->where('user_id', '!=', $userId)
        ->whereDoesntHave('reads', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->pluck('id');

    // Mark as read
    foreach ($unreadMessageIds as $messageId) {
        MessageRead::create([
            'message_id' => $messageId,
            'user_id' => $userId,
            'chat_id' => $chatId,
            'read_at' => now(),
        ]);
    }

    // Broadcast read receipt
    event(new MessagesReadEvent($chatId, $userId, $unreadMessageIds));

    return response()->json(['status' => 'success']);
}
```

**Read Receipt Display:**
- Single checkmark: Sent
- Double checkmark: Delivered
- Blue double checkmark: Read (Premium feature)

**Privacy Control:**
Users can disable read receipts in settings (Premium feature)

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/mark-read
```

### Typing Indicators

**Show when user is typing**

**Implementation:**
Uses Laravel Echo whisper events (doesn't store in database):

```javascript
// Sender
let typingTimer;
messageInput.addEventListener('input', () => {
    clearTimeout(typingTimer);

    // Whisper typing event
    Echo.private(`chat.${chatId}`)
        .whisper('typing', {
            user: currentUser,
            typing: true
        });

    // Stop typing after 3 seconds of inactivity
    typingTimer = setTimeout(() => {
        Echo.private(`chat.${chatId}`)
            .whisper('typing', {
                user: currentUser,
                typing: false
            });
    }, 3000);
});

// Receiver
Echo.private(`chat.${chatId}`)
    .listenForWhisper('typing', (e) => {
        if (e.typing) {
            showTypingIndicator(e.user.name);
        } else {
            hideTypingIndicator(e.user.name);
        }
    });
```

**Display:**
- "Fatima is typing..."
- Animated ellipsis (...)
- Disappears after 3 seconds of inactivity

### Online Status & Last Active

**Track user presence**

**Implementation:**
```php
// UpdateLastActive middleware
class UpdateLastActive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            auth()->user()->update([
                'last_active_at' => now(),
            ]);

            // Broadcast online status
            broadcast(new UserOnlineEvent(auth()->user()));
        }

        return $next($request);
    }
}
```

**Display:**
- Green dot: Online (active within 5 minutes)
- "Active 10 minutes ago"
- "Active 2 hours ago"
- "Active yesterday"
- "Active 1 week ago"

**Privacy:**
Users can hide online status and last active in settings

### Message Editing

**Edit sent messages within 15 minutes**

**Constraints:**
- Only text messages can be edited
- Must be within 15 minutes of sending
- Edited messages show "(edited)" indicator
- Edit history not stored for privacy

**Edit Flow:**
```php
public function editMessage(Request $request, int $messageId): JsonResponse
{
    $message = Message::findOrFail($messageId);

    // Authorization
    if ($message->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    // Time limit check
    if ($message->created_at->diffInMinutes(now()) > 15) {
        abort(422, 'Edit time limit exceeded (15 minutes)');
    }

    // Only text messages
    if ($message->type !== 'text') {
        abort(422, 'Only text messages can be edited');
    }

    $validated = $request->validate([
        'content' => 'required|string|max:5000',
    ]);

    $message->update([
        'content' => $validated['content'],
        'edited_at' => now(),
    ]);

    // Broadcast edit
    event(new MessageEditedEvent($message));

    return response()->json([
        'status' => 'success',
        'message' => MessageResource::make($message),
    ]);
}
```

**API Endpoint:**
```http
PUT /api/v1/messages/{messageId}
Content-Type: application/json

{
  "content": "Updated message text"
}
```

### Message Deletion

**Delete messages for both sides**

**Delete Options:**
1. **Delete for me**: Message removed from your view only
2. **Delete for everyone**: Message removed for both users (within 1 hour)

**Soft Delete:**
Messages are soft deleted, not permanently removed (for moderation purposes)

```php
public function deleteMessage(int $messageId, string $deleteType): JsonResponse
{
    $message = Message::findOrFail($messageId);

    // Authorization
    if ($message->user_id !== auth()->id()) {
        abort(403, 'Unauthorized');
    }

    if ($deleteType === 'for_me') {
        // Soft delete for current user only
        $message->deletedFor()->attach(auth()->id());
    } elseif ($deleteType === 'for_everyone') {
        // Check time limit (1 hour)
        if ($message->created_at->diffInHours(now()) > 1) {
            abort(422, 'Delete time limit exceeded (1 hour)');
        }

        // Soft delete for everyone
        $message->update(['deleted_at' => now()]);

        // Broadcast deletion
        event(new MessageDeletedEvent($message));
    }

    return response()->json(['status' => 'success']);
}
```

**API Endpoint:**
```http
DELETE /api/v1/messages/{messageId}?type=for_everyone
```

### Reply to Messages

**Quote and reply to specific messages**

**Implementation:**
```php
// Message model
public function repliedTo()
{
    return $this->belongsTo(Message::class, 'replied_to_id');
}

public function replies()
{
    return $this->hasMany(Message::class, 'replied_to_id');
}
```

**Display:**
Shows quoted message above new message with visual line indicator

**API:**
```http
POST /api/v1/chats/{chatId}/messages
Content-Type: application/json

{
  "content": "I agree!",
  "replied_to_id": 456
}
```

---

## Chat Management

### Conversation List

**Features:**
- Sorted by recent activity (last message timestamp)
- Unread indicator badges
- Last message preview
- Timestamp (smart formatting: "2m ago", "Yesterday", "Sep 25")
- Online status indicator
- Quick actions (archive, delete, mute)
- Pull-to-refresh
- Infinite scroll pagination

**API Endpoint:**
```http
GET /api/v1/chats?page=1&per_page=20
```

**Response:**
```json
{
  "data": [
    {
      "type": "chat",
      "id": "123",
      "attributes": {
        "type": "private",
        "last_message": "As-salamu alaykum!",
        "last_message_at": "2025-10-07T10:30:00Z",
        "unread_count": 3,
        "is_archived": false,
        "is_muted": false
      },
      "relationships": {
        "participant": {
          "data": {"type": "user", "id": "uuid"}
        },
        "last_message": {
          "data": {"type": "message", "id": "789"}
        }
      }
    }
  ],
  "meta": {
    "total": 15,
    "unread_total": 5
  }
}
```

**Livewire Component:**
- `Dashboard/ChatList` - Conversation list interface

### Chat Archiving

**Archive chats to declutter inbox**

**Features:**
- Move chats to archive
- Archived chats still receive messages
- Unarchive automatically when new message received
- Access archived chats from separate tab

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/archive
DELETE /api/v1/chats/{chatId}/archive  (Unarchive)
```

### Mute Notifications

**Mute specific chats**

**Options:**
- Mute for 1 hour
- Mute for 8 hours
- Mute for 1 week
- Mute forever

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/mute
Content-Type: application/json

{
  "duration": "1_week"  // or "1_hour", "8_hours", "forever"
}
```

### Search Within Conversations

**Search message content**

**Features:**
- Full-text search
- Filter by media type (images, videos, files)
- Date range filter
- Jump to message in conversation

**API Endpoint:**
```http
GET /api/v1/chats/{chatId}/search?q=masjid&type=text&date_from=2025-10-01
```

### Pin Conversations

**Pin important chats to top**

**Constraints:**
- Maximum 3 pinned chats (Free)
- Maximum 10 pinned chats (Premium)

**API Endpoint:**
```http
POST /api/v1/chats/{chatId}/pin
DELETE /api/v1/chats/{chatId}/pin  (Unpin)
```

---

## Chat Safety Features

### Content Moderation

**Automated Inappropriate Content Detection**

**Features:**
- Profanity filter (configurable per user)
- Link scanning for phishing/malware
- Image moderation (AI-powered NSFW detection)
- Spam detection (repeated messages)
- Pattern recognition for scams

**Implementation:**
```php
public function moderateContent(string $content, string $type = 'text'): array
{
    $flags = [];

    // Profanity check
    if ($this->containsProfanity($content)) {
        $flags[] = 'profanity';
    }

    // Link safety check
    if ($type === 'text') {
        $links = $this->extractLinks($content);
        foreach ($links as $link) {
            if ($this->isUnsafeLink($link)) {
                $flags[] = 'unsafe_link';
            }
        }
    }

    // Image moderation (for images)
    if ($type === 'image') {
        $nsfwScore = $this->checkImageNSFW($content);
        if ($nsfwScore > 0.7) {
            $flags[] = 'inappropriate_image';
        }
    }

    return [
        'is_safe' => empty($flags),
        'flags' => $flags,
    ];
}
```

**User Controls:**
- Enable/disable profanity filter
- Block images from non-verified users
- Auto-report flagged content

### Screenshot Detection

**Notify when screenshots taken**

**Implementation:**
- Browser API detection (web)
- Native detection (mobile apps)
- Notification sent to message sender
- Event logged for safety tracking

**JavaScript (Web):**
```javascript
// Detect screenshot attempts (visibility change, print screen)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        // Possible screenshot
        axios.post('/api/v1/screenshot-detected', {
            chat_id: chatId,
            context: 'chat'
        });
    }
});

// Detect print screen key
document.addEventListener('keyup', (e) => {
    if (e.key === 'PrintScreen') {
        axios.post('/api/v1/screenshot-detected', {
            chat_id: chatId,
            context: 'chat'
        });
    }
});
```

### Report Messages

**Report inappropriate messages**

**Report Flow:**
1. Long-press message (mobile) or right-click (web)
2. Select "Report"
3. Choose reason (harassment, spam, inappropriate content, etc.)
4. Optionally add description
5. Message automatically sent to moderation queue
6. Sender temporarily restricted if multiple reports

**API Endpoint:**
```http
POST /api/v1/messages/{messageId}/report
Content-Type: application/json

{
  "reason": "harassment",
  "description": "User is being aggressive and disrespectful"
}
```

### Block User from Chat

**Block user immediately from chat**

**Action:**
- Unmatch automatically
- Delete all chat history
- User cannot contact you again
- Profile hidden from blocked user

**API Endpoint:**
```http
POST /api/v1/users/{userId}/block
```

---

## Rate Limiting

### Chat-Specific Rate Limits

**Enforced via ChatRateLimit middleware**

**Limits:**
- **Create Chat**: 50 per hour
- **Send Message**: 500 per hour
- **Mark Read**: 1000 per hour
- **Edit Message**: 100 per hour
- **Delete Message**: 100 per hour

**Implementation:**
```php
// app/Http/Middleware/ChatRateLimit.php
class ChatRateLimit
{
    protected $limits = [
        'create_chat' => [50, 'hour'],
        'send_message' => [500, 'hour'],
        'mark_read' => [1000, 'hour'],
        'edit_message' => [100, 'hour'],
        'delete_message' => [100, 'hour'],
    ];

    public function handle(Request $request, Closure $next, string $action)
    {
        [$limit, $period] = $this->limits[$action];

        $key = "chat_rate_limit:{$action}:" . auth()->id();
        $attempts = Cache::get($key, 0);

        if ($attempts >= $limit) {
            abort(429, "Rate limit exceeded for {$action}");
        }

        Cache::put($key, $attempts + 1, now()->addHour());

        return $next($request);
    }
}
```

**Applied to Routes:**
```php
Route::post('/chats/{chatId}/messages', [ChatController::class, 'sendMessage'])
    ->middleware('chat.rate.limit:send_message');
```

---

## Best Practices

### For Users

**Messaging Etiquette:**
1. Start with Islamic greeting: "As-salamu alaykum"
2. Be respectful and courteous
3. Don't share personal contact info immediately
4. Don't send unsolicited photos
5. Respond within 24 hours if interested
6. Use report feature for inappropriate behavior

**Privacy Tips:**
1. Don't share exact location
2. Don't share financial information
3. Don't share personal documents
4. Be cautious of suspicious links
5. Report scam attempts immediately

### For Developers

**Performance Optimization:**
1. Paginate message loading (load 50 at a time)
2. Implement infinite scroll (load older messages on scroll)
3. Cache conversation list with Redis
4. Use database indexes on chat_id and created_at
5. Lazy load media (images, videos)
6. Compress images before upload

**Real-Time Best Practices:**
1. Implement reconnection logic for WebSocket
2. Queue messages locally if offline
3. Show "sending" state for pending messages
4. Implement retry logic for failed sends
5. Batch mark-as-read requests

**Security:**
1. Always authorize chat access
2. Validate message content server-side
3. Sanitize user input
4. Encrypt media URLs
5. Implement rate limiting
6. Log suspicious activity

---

## API Reference

### Chat Endpoints

```http
GET /api/v1/chats
POST /api/v1/chats
GET /api/v1/chats/{chatId}
DELETE /api/v1/chats/{chatId}
POST /api/v1/chats/{chatId}/archive
POST /api/v1/chats/{chatId}/mute
POST /api/v1/chats/{chatId}/pin
```

### Message Endpoints

```http
GET /api/v1/chats/{chatId}/messages
POST /api/v1/chats/{chatId}/messages
PUT /api/v1/messages/{messageId}
DELETE /api/v1/messages/{messageId}
POST /api/v1/chats/{chatId}/mark-read
GET /api/v1/chats/{chatId}/search
```

### Media Endpoints

```http
POST /api/v1/chats/{chatId}/messages/image
POST /api/v1/chats/{chatId}/messages/video
POST /api/v1/chats/{chatId}/messages/voice
POST /api/v1/chats/{chatId}/messages/location
POST /api/v1/chats/{chatId}/messages/file
```

### Safety Endpoints

```http
POST /api/v1/messages/{messageId}/report
POST /api/v1/screenshot-detected
```

---

*Last Updated: October 2025*

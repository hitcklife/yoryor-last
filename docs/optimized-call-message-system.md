# Optimized Call-Message Integration System

## Overview

This document describes the enhanced call-message integration system that provides optimized linking between video/voice calls and chat messages, with automatic state management and improved performance.

## Key Features

### 1. **Automatic Call Message Creation**
- Call messages are automatically created when calls are initiated
- Messages are automatically updated when call status changes
- No manual message creation required

### 2. **Direct Database Relationship**
- Messages now have a direct `call_id` foreign key to calls table
- Optimized queries using proper relationships
- Eliminates data duplication and inconsistencies

### 3. **Enhanced Call State Management**
- **Initiated**: Call is ringing (shows as "Outgoing Call" for caller, "Incoming Call" for receiver)
- **Ongoing**: Call is active (shows as "Call in progress")
- **Completed**: Call ended successfully (shows duration: "Video Call - 2m 30s")
- **Missed**: Call was not answered (shows as "Missed Video Call")
- **Rejected**: Call was declined (shows as "Video Call declined")

### 4. **Automatic Missed Call Handling**
- Console command automatically marks timed-out calls as missed
- Configurable timeout period (default: 30 seconds)
- Can be scheduled to run every minute

## Database Schema Changes

### New Migration: `add_call_id_to_messages_table`

```sql
ALTER TABLE messages ADD COLUMN call_id BIGINT UNSIGNED NULL;
ALTER TABLE messages ADD FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL;
ALTER TABLE messages ADD INDEX idx_call_sent (call_id, sent_at);
```

## API Endpoints

### Enhanced Video Call Endpoints

#### 1. Initiate Call
```http
POST /api/v1/video-call/initiate
```

**Request:**
```json
{
    "recipient_id": 123,
    "call_type": "video"
}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "call_id": 456,
        "meeting_id": "uuid-meeting-id",
        "token": "jwt-token",
        "type": "video",
        "message_id": 789,
        "caller": { "id": 1, "name": "John Doe" },
        "receiver": { "id": 123, "name": "Jane Smith" }
    }
}
```

#### 2. Join Call
```http
POST /api/v1/video-call/{callId}/join
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "call_id": 456,
        "meeting_id": "uuid-meeting-id",
        "token": "jwt-token",
        "type": "video",
        "message_id": 789
    }
}
```

#### 3. End Call
```http
POST /api/v1/video-call/{callId}/end
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "message": "Call ended successfully",
        "call_id": 456,
        "duration": 150,
        "formatted_duration": "2m 30s",
        "message_id": 789
    }
}
```

#### 4. Handle Missed Call
```http
POST /api/v1/video-call/{callId}/missed
```

#### 5. Get Call History
```http
GET /api/v1/video-call/history?call_type=video&call_status=completed&page=1&per_page=20
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 789,
                "chat_id": 12,
                "sender_id": 1,
                "content": "Video Call - 2m 30s",
                "message_type": "call",
                "media_data": {
                    "call_type": "video",
                    "call_status": "completed",
                    "duration": 150,
                    "call_id": 456
                },
                "call_details": {
                    "call_id": 456,
                    "type": "video",
                    "status": "completed",
                    "duration_seconds": 150,
                    "formatted_duration": "2m 30s"
                }
            }
        ],
        "total": 50
    }
}
```

#### 6. Get Call Analytics
```http
GET /api/v1/video-call/analytics
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "total_calls": 100,
        "completed_calls": 75,
        "missed_calls": 20,
        "success_rate": 75.0,
        "total_duration": 7200,
        "average_duration": 96.0,
        "formatted_total_duration": "2h"
    }
}
```

### Enhanced Chat Endpoints

#### 1. Get Chat with Call Data
```http
GET /api/v1/chats/{id}?include_call_data=true
```

**Response includes enhanced call details:**
```json
{
    "status": "success",
    "data": {
        "chat": {
            "id": 12,
            "type": "private",
            "is_active": true,
            "other_user": {
                "id": 123,
                "name": "Jane Smith"
            }
        },
        "messages": {
            "data": [
                {
                    "id": 789,
                    "message_type": "call",
                    "content": "Video Call - 2m 30s",
                    "media_data": {...},
                    "call_details": {
                        "call_id": 456,
                        "type": "video",
                        "status": "completed",
                        "duration_seconds": 150,
                        "formatted_duration": "2m 30s",
                        "is_active": false,
                        "other_participant": {
                            "id": 123,
                            "name": "Jane Smith"
                        }
                    }
                }
            ]
        }
    }
}
```

#### 2. Get Call Messages Only
```http
GET /api/v1/chats/{id}/call-messages?call_type=video&call_status=completed
```

#### 3. Get Call Statistics for Chat
```http
GET /api/v1/chats/{id}/call-statistics
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "total_calls": 25,
        "completed_calls": 20,
        "missed_calls": 3,
        "video_calls": 15,
        "voice_calls": 10,
        "success_rate": 80.0,
        "total_duration_seconds": 3600,
        "formatted_total_duration": "1h",
        "formatted_average_duration": "3m"
    }
}
```

## Usage Examples

### Frontend Integration

#### 1. Initiating a Call with Automatic Message Creation

```javascript
// Frontend initiates call
const response = await fetch('/api/v1/video-call/initiate', {
    method: 'POST',
    headers: { 'Authorization': 'Bearer ' + token },
    body: JSON.stringify({
        recipient_id: userId,
        call_type: 'video'
    })
});

const callData = await response.json();

// Call message is automatically created and available
console.log('Call message ID:', callData.data.message_id);

// The message will show as "Outgoing Video Call" in chat
```

#### 2. Real-time Call Status Updates

```javascript
// Listen for call status changes
Echo.private(`private-user.${userId}`)
    .listen('CallStatusChangedEvent', (e) => {
        // Call message is automatically updated
        console.log('Call status changed:', e.call.status);
        
        // Refresh chat to show updated message
        // Message content will automatically reflect new status
    });
```

#### 3. Display Call History in Chat

```javascript
// Get chat with enhanced call data
const chatResponse = await fetch(`/api/v1/chats/${chatId}?include_call_data=true`);
const chatData = await chatResponse.json();

chatData.data.messages.data.forEach(message => {
    if (message.message_type === 'call') {
        // Display call message with enhanced data
        console.log('Call duration:', message.call_details.formatted_duration);
        console.log('Call status:', message.call_details.status);
        console.log('Other participant:', message.call_details.other_participant.name);
    }
});
```

### Backend Service Usage

#### 1. Manual Call Message Creation/Update

```php
use App\Services\CallMessageService;

$callMessageService = app(CallMessageService::class);

// Create call message when call is initiated
$message = $callMessageService->createOrUpdateCallMessage($call, 'initiated');

// Update call message when call status changes
$message = $callMessageService->createOrUpdateCallMessage($call, 'ended');
```

#### 2. Handle Missed Calls

```php
// Manually handle missed call
$callMessageService->handleMissedCall($call);

// Or use the console command
php artisan calls:handle-missed --timeout=30
```

#### 3. Get Call Analytics

```php
$analytics = $callMessageService->getCallAnalytics($user);
$callHistory = $callMessageService->getCallHistory($user, [
    'call_type' => 'video',
    'call_status' => 'completed',
    'per_page' => 20
]);
```

## Automated Tasks

### 1. Missed Call Handling

Schedule the missed call command in `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Check for missed calls every minute
    $schedule->command('calls:handle-missed --timeout=30')
             ->everyMinute()
             ->withoutOverlapping();
}
```

### 2. Call Analytics Cleanup

```php
// Clean up old call data (optional)
$schedule->command('calls:cleanup --days=30')
         ->daily();
```

## Performance Optimizations

### 1. Database Indexes

```sql
-- Optimized indexes for call queries
CREATE INDEX idx_calls_user_status ON calls(caller_id, receiver_id, status);
CREATE INDEX idx_calls_status_created ON calls(status, created_at);
CREATE INDEX idx_messages_call_type ON messages(message_type, call_id);
```

### 2. Eager Loading

```php
// Load call data efficiently
$messages = Message::with(['call:id,type,status,started_at,ended_at'])
    ->where('message_type', 'call')
    ->get();
```

### 3. Query Optimization

```php
// Optimized call history query
$callHistory = Message::callMessages()
    ->whereHas('chat', function($query) use ($user) {
        $query->where('user1_id', $user->id)
              ->orWhere('user2_id', $user->id);
    })
    ->with(['call', 'sender', 'chat'])
    ->orderBy('sent_at', 'desc')
    ->paginate(20);
```

## Migration Guide

### 1. Run Migration

```bash
php artisan migrate
```

### 2. Update Existing Call Messages (Optional)

```php
// Link existing call messages to calls if needed
$callMessages = Message::where('message_type', 'call')
    ->whereNull('call_id')
    ->get();

foreach ($callMessages as $message) {
    // Try to find matching call based on media_data
    $callData = $message->media_data;
    if (isset($callData['call_id'])) {
        $message->update(['call_id' => $callData['call_id']]);
    }
}
```

### 3. Schedule Commands

```bash
# Add to crontab for automatic missed call handling
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Best Practices

### 1. **Error Handling**
- Always wrap call operations in try-catch blocks
- Log call failures for monitoring
- Provide fallback mechanisms for failed call message creation

### 2. **Real-time Updates**
- Use broadcasting for immediate call status updates
- Update chat UI when call messages change
- Handle offline scenarios gracefully

### 3. **Performance**
- Use pagination for call history
- Implement caching for frequently accessed call statistics
- Use database indexes for optimized queries

### 4. **User Experience**
- Show clear call status indicators
- Provide retry mechanisms for failed calls
- Display call duration prominently

## Monitoring and Analytics

### 1. **Call Metrics**
- Track call success rates
- Monitor average call durations
- Alert on high missed call rates

### 2. **Performance Metrics**
- Monitor call message creation latency
- Track database query performance
- Monitor storage usage for call data

### 3. **User Engagement**
- Analyze call frequency patterns
- Track user preferences (video vs voice)
- Monitor call quality indicators

This optimized system provides a robust, scalable foundation for managing video/voice calls with integrated chat messaging, automatic state management, and comprehensive analytics. 
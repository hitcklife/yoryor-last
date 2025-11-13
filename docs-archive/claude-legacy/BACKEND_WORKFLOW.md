# YorYor Backend Workflow Documentation

## Request Lifecycle

### 1. Request Entry
```
Mobile App → API Request → Laravel Router → Middleware Pipeline → Controller
```

### 2. Middleware Pipeline
1. **CORS Handling**: Cross-origin requests
2. **Authentication**: Sanctum token validation
3. **Rate Limiting**: Request throttling
4. **Secure Headers**: Security headers injection
5. **Update Presence**: User activity tracking

### 3. Controller Processing
1. Request validation
2. Service layer invocation
3. Database operations
4. Event broadcasting
5. Response formatting

### 4. Response Flow
```
Controller → API Resource → JSON Response → Mobile App
```

## Core Workflows

### User Registration & Authentication

#### Registration Flow
1. **Phone/Email Submission**
   - User submits phone/email
   - System checks existing users
   - OTP code generated and sent

2. **OTP Verification**
   - User enters OTP code
   - System validates code
   - Temporary token issued

3. **Profile Completion**
   - User fills profile details
   - Photos uploaded
   - Preferences set
   - Full authentication token issued

#### Login Flow
1. **Credentials Submission**
   - Phone/email + password
   - Optional 2FA code
   - Device token registration

2. **Token Generation**
   - Sanctum token created
   - User status updated
   - Presence marked online

### Chat System Workflow

#### Message Sending
1. **Client Request**
   ```
   POST /api/v1/chats/{id}/messages
   {
     "message": "Hello",
     "type": "text"
   }
   ```

2. **Server Processing**
   - Rate limit check
   - Message validation
   - Database insertion
   - Media processing (if applicable)

3. **Real-time Broadcasting**
   - Event: `NewMessageEvent`
   - Channel: `chat.{chatId}`
   - Push notification sent

4. **Client Updates**
   - WebSocket receives event
   - UI updates in real-time
   - Read receipt tracking

#### Media Message Flow
1. **Media Upload**
   - File uploaded to temp storage
   - Media processing service invoked
   - Thumbnails generated
   - S3 upload

2. **Message Creation**
   - Media URLs stored
   - Message record created
   - Broadcasting triggered

### Video Calling Workflow

#### Call Initiation
1. **Caller Actions**
   - Request video token
   - Create meeting room
   - Send call invitation

2. **Server Processing**
   ```php
   // VideoCallController
   - Generate Video SDK token
   - Create call record
   - Broadcast CallInitiatedEvent
   - Send push notification
   ```

3. **Receiver Actions**
   - Receive push notification
   - Join or reject call
   - Update call status

#### Call State Management
```
INITIATED → RINGING → ONGOING → COMPLETED/MISSED/REJECTED
```

### Matching System Workflow

#### Like/Dislike Process
1. **User Action**
   - Swipe right (like) or left (dislike)
   - API request sent

2. **Server Logic**
   ```php
   // LikeController
   - Record like/dislike
   - Check for mutual like
   - Create match if mutual
   - Broadcast events
   ```

3. **Match Creation**
   - Chat room auto-created
   - Both users notified
   - Match appears in list

### Push Notification Workflow

#### Notification Pipeline
1. **Event Trigger**
   - New message, like, match, etc.
   - Event listener activated

2. **Notification Service**
   ```php
   // NotificationService
   - Fetch user tokens
   - Check notification preferences
   - Format notification payload
   - Send to Expo service
   ```

3. **Delivery**
   - Expo handles platform-specific delivery
   - Delivery receipts tracked
   - Failed notifications logged

## Background Jobs

### Queue Workers
```bash
php artisan queue:work --queue=default,notifications,media
```

### Scheduled Tasks
- **Hourly**
  - Clean expired OTP codes
  - Update user activity status
  - Process pending media

- **Daily**
  - Send daily summaries
  - Clean old notifications
  - Archive old messages

- **Weekly**
  - Generate user analytics
  - Clean temporary files
  - Database optimization

## Real-time Features

### WebSocket Channels

#### Private Channels
```javascript
// User-specific channel
Echo.private(`user.${userId}`)
    .listen('NewMessageEvent', (e) => {
        // Handle new message
    });
```

#### Presence Channels
```javascript
// Chat presence
Echo.join(`presence-chat.${chatId}`)
    .here((users) => {
        // Users currently in chat
    })
    .joining((user) => {
        // User joined
    })
    .leaving((user) => {
        // User left
    });
```

### Event Broadcasting

#### Key Events
1. **NewMessageEvent**: New chat message
2. **UserOnlineStatusChanged**: Online/offline status
3. **UserTypingStatusChanged**: Typing indicators
4. **CallInitiatedEvent**: Incoming call
5. **NewMatchEvent**: New match created

## API Response Patterns

### Success Response
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe"
        }
    },
    "message": "Profile updated successfully"
}
```

### Error Response
```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid",
        "errors": {
            "email": ["The email field is required"]
        }
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": [...],
    "meta": {
        "current_page": 1,
        "total": 100,
        "per_page": 20,
        "last_page": 5
    }
}
```

## Error Handling

### Exception Types
1. **ValidationException**: Input validation failures
2. **AuthenticationException**: Auth failures
3. **AuthorizationException**: Permission denied
4. **NotFoundException**: Resource not found
5. **ServerErrorException**: Internal errors

### Error Flow
```
Exception Thrown → Exception Handler → Error Response → Client Error Handler
```

## Performance Considerations

### Database Optimization
- Eager loading relationships
- Query optimization
- Index usage
- Cache integration

### API Optimization
- Response caching
- Query result caching
- CDN for media files
- Pagination for lists

### Real-time Optimization
- Channel authorization caching
- Message batching
- Presence debouncing
- Connection pooling
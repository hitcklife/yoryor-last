# YorYor API Reference

## Base URL
```
Production: https://api.yoryor.com/api/v1
Development: http://localhost:8000/api/v1
```

## Authentication
All authenticated endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Response Format
All responses follow this structure:
```json
{
    "success": true|false,
    "data": {...} | null,
    "message": "Success message" | null,
    "error": {
        "code": "ERROR_CODE",
        "message": "Error description",
        "errors": {}
    } | null
}
```

## Endpoints

### Authentication

#### POST /auth/authenticate
Authenticate user with email/phone and password.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 12"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "token": "1|abcdef123456...",
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "profile_completion": 85
        }
    }
}
```

#### POST /auth/logout
Logout and revoke current token.

**Headers:** Requires authentication

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### POST /auth/complete-registration
Complete user registration after OTP verification.

**Request:**
```json
{
    "name": "John Doe",
    "birthdate": "1990-01-15",
    "gender": "male",
    "location": {
        "latitude": 40.7128,
        "longitude": -74.0060,
        "city": "New York",
        "country": "US"
    }
}
```

### User Profile

#### GET /profile/me
Get authenticated user's complete profile.

**Headers:** Requires authentication

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "phone": "+1234567890",
        "birthdate": "1990-01-15",
        "gender": "male",
        "bio": "Adventure seeker...",
        "location": {
            "latitude": 40.7128,
            "longitude": -74.0060,
            "city": "New York",
            "country": "US"
        },
        "photos": [
            {
                "id": 1,
                "url": "https://cdn.yoryor.com/photos/123.jpg",
                "is_primary": true,
                "is_verified": true
            }
        ],
        "preferences": {
            "age_min": 25,
            "age_max": 35,
            "gender": "female",
            "distance_max": 50
        },
        "profile_completion": 85,
        "last_active_at": "2024-01-15T10:30:00Z"
    }
}
```

#### PUT /profile/{profileId}
Update user profile.

**Request:**
```json
{
    "bio": "Updated bio text",
    "occupation": "Software Engineer",
    "education": "Masters in Computer Science",
    "height": 175,
    "interests": ["hiking", "photography", "cooking"]
}
```

### Photos

#### GET /photos
Get user's photos.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "url": "https://cdn.yoryor.com/photos/123.jpg",
            "thumbnail_url": "https://cdn.yoryor.com/photos/123_thumb.jpg",
            "is_primary": true,
            "is_verified": true,
            "uploaded_at": "2024-01-10T15:30:00Z"
        }
    ]
}
```

#### POST /photos/upload
Upload a new photo.

**Request:** Multipart form data
- `image`: Image file (JPEG, PNG)
- `type`: "profile" | "verification"
- `is_primary`: boolean

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "url": "https://cdn.yoryor.com/photos/456.jpg",
        "thumbnail_url": "https://cdn.yoryor.com/photos/456_thumb.jpg"
    }
}
```

### Matching

#### GET /matches/potential
Get potential matches based on preferences.

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 10)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 123,
            "name": "Jane Smith",
            "age": 28,
            "bio": "Love to travel...",
            "distance": 5.2,
            "photos": [...],
            "compatibility_score": 85
        }
    ],
    "meta": {
        "current_page": 1,
        "total": 50,
        "per_page": 10
    }
}
```

#### POST /likes
Like a user.

**Request:**
```json
{
    "liked_user_id": 123,
    "is_super_like": false
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "is_match": true,
        "match_id": 456
    }
}
```

### Chat

#### GET /chats
Get user's chat list.

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "private",
            "last_message": {
                "id": 789,
                "message": "Hey, how are you?",
                "type": "text",
                "created_at": "2024-01-15T20:00:00Z"
            },
            "unread_count": 2,
            "other_user": {
                "id": 123,
                "name": "Jane Smith",
                "photo_url": "https://cdn.yoryor.com/photos/123.jpg",
                "is_online": true
            }
        }
    ]
}
```

#### POST /chats/{chatId}/messages
Send a message.

**Request:**
```json
{
    "message": "Hello!",
    "type": "text",
    "reply_to_message_id": null
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 790,
        "chat_id": 1,
        "user_id": 1,
        "message": "Hello!",
        "type": "text",
        "created_at": "2024-01-15T20:05:00Z",
        "read_by": []
    }
}
```

#### POST /chats/{chatId}/messages (Media)
Send media message.

**Request:** Multipart form data
- `type`: "image" | "video" | "voice" | "document"
- `media`: Media file
- `caption`: Optional caption text
- `duration`: Duration in seconds (for voice/video)

### Video Calling

#### POST /video-call/initiate
Initiate a video call.

**Request:**
```json
{
    "receiver_id": 123,
    "call_type": "video"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "call_id": 456,
        "meeting_id": "abc-def-ghi",
        "token": "eyJ0eXAiOiJKV1...",
        "status": "initiated"
    }
}
```

#### POST /video-call/{callId}/join
Join an existing call.

**Response:**
```json
{
    "success": true,
    "data": {
        "meeting_id": "abc-def-ghi",
        "token": "eyJ0eXAiOiJKV1...",
        "participant_id": "user_123"
    }
}
```

### Presence

#### POST /presence/status
Update online status.

**Request:**
```json
{
    "is_online": true,
    "last_active_at": "2024-01-15T20:00:00Z"
}
```

#### POST /presence/typing
Update typing status.

**Request:**
```json
{
    "chat_id": 1,
    "is_typing": true
}
```

### Push Notifications

#### POST /device-tokens
Register device for push notifications.

**Request:**
```json
{
    "token": "ExponentPushToken[xxxxxx]",
    "platform": "ios",
    "device_id": "unique-device-id"
}
```

### Settings

#### GET /settings
Get all user settings.

**Response:**
```json
{
    "success": true,
    "data": {
        "notifications": {
            "push_enabled": true,
            "new_matches": true,
            "new_messages": true,
            "new_likes": true
        },
        "privacy": {
            "show_online_status": true,
            "show_read_receipts": true,
            "show_distance": true
        },
        "discovery": {
            "discoverable": true,
            "show_recently_active": true
        }
    }
}
```

#### PUT /settings/notifications
Update notification settings.

**Request:**
```json
{
    "push_enabled": true,
    "new_matches": true,
    "new_messages": true,
    "new_likes": false,
    "daily_summary": true
}
```

## Error Codes

| Code | Description |
|------|-------------|
| `UNAUTHORIZED` | Invalid or missing authentication token |
| `VALIDATION_ERROR` | Request validation failed |
| `NOT_FOUND` | Resource not found |
| `FORBIDDEN` | Access denied |
| `RATE_LIMITED` | Too many requests |
| `SERVER_ERROR` | Internal server error |
| `MAINTENANCE` | Service under maintenance |

## Rate Limits

| Endpoint | Limit |
|----------|-------|
| Authentication | 5 requests per minute |
| Message sending | 60 messages per minute |
| Chat creation | 10 chats per minute |
| Photo upload | 10 uploads per hour |
| Like/Dislike | 100 per hour |
| General API | 1000 requests per hour |

## WebSocket Events

### Channels

#### Private User Channel
```javascript
private-user.{userId}
```

Events:
- `NewMessageEvent`
- `NewMatchEvent`
- `NewLikeEvent`
- `CallInitiatedEvent`

#### Chat Channel
```javascript
chat.{chatId}
```

Events:
- `NewMessageEvent`
- `MessageEditedEvent`
- `MessageDeletedEvent`
- `UserTypingStatusChanged`

#### Presence Channel
```javascript
presence-chat.{chatId}
```

Events:
- User joined
- User left
- Typing status

### Event Payloads

#### NewMessageEvent
```json
{
    "message": {
        "id": 123,
        "chat_id": 1,
        "user": {...},
        "message": "Hello",
        "type": "text",
        "created_at": "2024-01-15T20:00:00Z"
    }
}
```

#### UserTypingStatusChanged
```json
{
    "user_id": 123,
    "chat_id": 1,
    "is_typing": true
}
```
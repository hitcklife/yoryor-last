# Yoryor Dating App - API Reference

## Overview
This document provides comprehensive API documentation for the Yoryor Dating App. All API endpoints require authentication using Laravel Sanctum.

## Base URL
```
https://yourdomain.com/api/v1
```

## Authentication
All API endpoints require authentication. Include the Bearer token in the Authorization header:

```http
Authorization: Bearer your_token_here
```

## Response Format
All API responses follow a consistent format:

```json
{
    "status": "success|error",
    "data": {},
    "message": "Optional message",
    "pagination": {
        "total": 100,
        "per_page": 20,
        "current_page": 1,
        "last_page": 5,
        "has_more_pages": true,
        "from": 1,
        "to": 20
    }
}
```

## Error Handling
Errors are returned with appropriate HTTP status codes:

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Too Many Requests
- `500` - Internal Server Error

## Rate Limiting
API endpoints are rate-limited to prevent abuse:
- General API: 1000 requests per hour
- Like actions: 100 requests per hour
- Message actions: 200 requests per hour
- Search actions: 50 requests per hour
- Upload actions: 20 requests per hour

## Endpoints

### User Profile

#### Get User Profile
```http
GET /api/v1/user/profile
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+1234567890",
        "profile": {
            "first_name": "John",
            "last_name": "Doe",
            "age": 25,
            "bio": "Love to travel and meet new people",
            "interests": ["Travel", "Music", "Sports"],
            "photos": []
        },
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

#### Update User Profile
```http
PUT /api/v1/user/profile
```

**Request Body:**
```json
{
    "name": "John Doe",
    "bio": "Updated bio",
    "interests": ["Travel", "Music", "Sports", "Art"]
}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "name": "John Doe",
        "bio": "Updated bio",
        "interests": ["Travel", "Music", "Sports", "Art"],
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

### Matches

#### Get User Matches
```http
GET /api/v1/matches
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
    "status": "success",
    "data": {
        "matches": [
            {
                "id": 1,
                "user": {
                    "id": 2,
                    "name": "Jane Doe",
                    "age": 23,
                    "photos": [
                        {
                            "id": 1,
                            "url": "https://example.com/photo1.jpg",
                            "thumbnail_url": "https://example.com/photo1_thumb.jpg"
                        }
                    ]
                },
                "matched_at": "2024-01-01T00:00:00.000000Z",
                "is_super_like": false
            }
        ],
        "pagination": {
            "total": 50,
            "per_page": 20,
            "current_page": 1,
            "last_page": 3,
            "has_more_pages": true,
            "from": 1,
            "to": 20
        }
    }
}
```

### Messages

#### Get User Conversations
```http
GET /api/v1/messages
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
    "status": "success",
    "data": {
        "conversations": [
            {
                "id": 1,
                "participant": {
                    "id": 2,
                    "name": "Jane Doe",
                    "photos": []
                },
                "last_message": {
                    "id": 1,
                    "content": "Hello! How are you?",
                    "sent_at": "2024-01-01T00:00:00.000000Z",
                    "is_read": false
                },
                "unread_count": 2,
                "updated_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "total": 10,
            "per_page": 20,
            "current_page": 1,
            "last_page": 1,
            "has_more_pages": false,
            "from": 1,
            "to": 10
        }
    }
}
```

#### Get Conversation Messages
```http
GET /api/v1/messages/{conversationId}
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 50)

**Response:**
```json
{
    "status": "success",
    "data": {
        "conversation": {
            "id": 1,
            "participant": {
                "id": 2,
                "name": "Jane Doe"
            }
        },
        "messages": [
            {
                "id": 1,
                "content": "Hello! How are you?",
                "sender_id": 1,
                "sent_at": "2024-01-01T00:00:00.000000Z",
                "is_read": true
            }
        ],
        "pagination": {
            "total": 25,
            "per_page": 50,
            "current_page": 1,
            "last_page": 1,
            "has_more_pages": false,
            "from": 1,
            "to": 25
        }
    }
}
```

#### Send Message
```http
POST /api/v1/messages/{conversationId}
```

**Request Body:**
```json
{
    "content": "Hello! How are you?",
    "type": "text"
}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "id": 1,
        "content": "Hello! How are you?",
        "sender_id": 1,
        "sent_at": "2024-01-01T00:00:00.000000Z",
        "is_read": false
    }
}
```

### Notifications

#### Get User Notifications
```http
GET /api/v1/notifications
```

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)
- `filter` (optional): Filter by type (all, unread, matches, messages, likes, system)

**Response:**
```json
{
    "status": "success",
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "match",
                "title": "New Match!",
                "message": "You have a new match with Jane Doe",
                "data": {
                    "user_id": 2,
                    "user_name": "Jane Doe"
                },
                "read_at": null,
                "created_at": "2024-01-01T00:00:00.000000Z"
            }
        ],
        "pagination": {
            "total": 15,
            "per_page": 20,
            "current_page": 1,
            "last_page": 1,
            "has_more_pages": false,
            "from": 1,
            "to": 15
        }
    }
}
```

#### Mark Notification as Read
```http
POST /api/v1/notifications/{notificationId}/mark-read
```

**Response:**
```json
{
    "status": "success",
    "message": "Notification marked as read"
}
```

#### Mark All Notifications as Read
```http
POST /api/v1/notifications/mark-all-read
```

**Response:**
```json
{
    "status": "success",
    "message": "All notifications marked as read"
}
```

#### Delete Notification
```http
DELETE /api/v1/notifications/{notificationId}
```

**Response:**
```json
{
    "status": "success",
    "message": "Notification deleted"
}
```

### Search

#### Search Users
```http
GET /api/v1/search
```

**Query Parameters:**
- `q` (optional): Search query
- `age_min` (optional): Minimum age
- `age_max` (optional): Maximum age
- `distance` (optional): Maximum distance in km
- `gender` (optional): Gender filter
- `interests` (optional): Comma-separated interests
- `education` (optional): Education level
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 20)

**Response:**
```json
{
    "status": "success",
    "data": {
        "results": [
            {
                "id": 2,
                "name": "Jane Doe",
                "age": 23,
                "distance": 5.2,
                "photos": [],
                "bio": "Love to travel and meet new people",
                "interests": ["Travel", "Music"]
            }
        ],
        "pagination": {
            "total": 25,
            "per_page": 20,
            "current_page": 1,
            "last_page": 2,
            "has_more_pages": true,
            "from": 1,
            "to": 20
        }
    }
}
```

### Subscription

#### Get Subscription Status
```http
GET /api/v1/subscription
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "current_plan": {
            "id": "premium",
            "name": "Premium",
            "price": 19.99,
            "currency": "USD",
            "interval": "month",
            "status": "active",
            "renewal_date": "2024-02-01T00:00:00.000000Z",
            "auto_renew": true
        },
        "usage_stats": {
            "likes_used": 15,
            "likes_limit": -1,
            "super_likes_used": 2,
            "super_likes_limit": 5,
            "boosts_used": 0,
            "boosts_limit": 1
        },
        "billing_history": [
            {
                "id": 1,
                "date": "2024-01-01T00:00:00.000000Z",
                "amount": 19.99,
                "currency": "USD",
                "description": "Premium Monthly",
                "status": "completed"
            }
        ]
    }
}
```

### Verification

#### Get Verification Status
```http
GET /api/v1/verification/status
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "photo_verified": true,
        "id_verified": false,
        "phone_verified": true,
        "email_verified": true,
        "overall_score": 75
    }
}
```

#### Submit Photo Verification
```http
POST /api/v1/verification/photo
```

**Request Body:**
```json
{
    "photo": "base64_encoded_image_data"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Photo verification submitted successfully"
}
```

### Safety & Emergency

#### Get Emergency Contacts
```http
GET /api/v1/safety/emergency-contacts
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "emergency_contacts": [
            {
                "id": 1,
                "name": "Emergency Services",
                "phone": "911",
                "type": "emergency",
                "is_primary": true
            }
        ]
    }
}
```

#### Send Panic Alert
```http
POST /api/v1/safety/panic
```

**Response:**
```json
{
    "status": "success",
    "message": "Emergency alert sent"
}
```

#### Send Safety Check
```http
POST /api/v1/safety/safety-check
```

**Response:**
```json
{
    "status": "success",
    "message": "Safety check completed"
}
```

### Analytics

#### Get User Analytics
```http
GET /api/v1/analytics
```

**Query Parameters:**
- `date_range` (optional): Date range (7, 30, 90, 365 days)

**Response:**
```json
{
    "status": "success",
    "data": {
        "profile_views": {
            "total": 1247,
            "unique": 892,
            "today": 23,
            "this_week": 156,
            "this_month": 634
        },
        "matches": {
            "total": 47,
            "this_week": 8,
            "this_month": 23,
            "match_rate": 3.8
        },
        "messages": {
            "sent": 342,
            "received": 298,
            "response_rate": 0.68
        },
        "success_score": 78
    }
}
```

### User Actions

#### Like User
```http
POST /api/user/like/{userId}
```

**Response:**
```json
{
    "success": true,
    "message": "User liked successfully"
}
```

#### Unlike User
```http
POST /api/user/unlike/{userId}
```

**Response:**
```json
{
    "success": true,
    "message": "User unliked successfully"
}
```

#### Pass User
```http
POST /api/user/pass/{userId}
```

**Response:**
```json
{
    "success": true,
    "message": "User passed successfully"
}
```

#### Block User
```http
POST /api/user/block/{userId}
```

**Response:**
```json
{
    "success": true,
    "message": "User blocked successfully"
}
```

#### Unblock User
```http
DELETE /api/user/unblock/{userId}
```

**Response:**
```json
{
    "success": true,
    "message": "User unblocked successfully"
}
```

#### Report User
```http
POST /api/user/report/{userId}
```

**Request Body:**
```json
{
    "reason": "inappropriate_behavior",
    "description": "User was being inappropriate"
}
```

**Response:**
```json
{
    "success": true,
    "message": "User reported successfully"
}
```

## Webhooks

### Stripe Webhooks
```http
POST /api/webhooks/stripe
```

Handle Stripe payment events for subscription management.

### Push Notification Webhooks
```http
POST /api/webhooks/push
```

Handle push notification delivery status updates.

## SDK Examples

### JavaScript/Node.js
```javascript
const api = axios.create({
    baseURL: 'https://yourdomain.com/api/v1',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});

// Get user profile
const profile = await api.get('/user/profile');

// Search users
const searchResults = await api.get('/search', {
    params: { q: 'travel', age_min: 25, age_max: 35 }
});

// Send message
const message = await api.post('/messages/1', {
    content: 'Hello!',
    type: 'text'
});
```

### PHP
```php
$client = new GuzzleHttp\Client([
    'base_uri' => 'https://yourdomain.com/api/v1',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json'
    ]
]);

// Get user profile
$response = $client->get('/user/profile');
$profile = json_decode($response->getBody(), true);

// Search users
$response = $client->get('/search', [
    'query' => ['q' => 'travel', 'age_min' => 25, 'age_max' => 35]
]);
$searchResults = json_decode($response->getBody(), true);
```

## Error Codes

| Code | Description |
|------|-------------|
| `VALIDATION_ERROR` | Request validation failed |
| `UNAUTHORIZED` | Authentication required |
| `FORBIDDEN` | Access denied |
| `NOT_FOUND` | Resource not found |
| `RATE_LIMITED` | Too many requests |
| `SERVER_ERROR` | Internal server error |

## Changelog

### Version 1.0.0
- Initial API release
- User profile management
- Matching system
- Messaging system
- Notifications
- Search functionality
- Subscription management
- Verification system
- Safety features
- Analytics dashboard

---

For more information, please contact our support team at api-support@yoryor.com

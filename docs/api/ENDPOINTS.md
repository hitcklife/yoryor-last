# YorYor API Endpoints Documentation

## Table of Contents
- [Overview](#overview)
- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Authentication Endpoints](#authentication-endpoints)
- [Profile Management](#profile-management)
- [User Discovery & Matching](#user-discovery--matching)
- [Chat & Messaging](#chat--messaging)
- [Video Calling](#video-calling)
- [Stories](#stories)
- [Settings & Account](#settings--account)
- [Safety & Emergency](#safety--emergency)
- [Matchmaker System](#matchmaker-system)
- [Verification System](#verification-system)
- [Support & Feedback](#support--feedback)
- [Notifications](#notifications)
- [Analytics](#analytics)
- [Response Formats](#response-formats)
- [WebSocket Events](#websocket-events)
- [Best Practices](#best-practices)

---

## Overview

**Base URL:** `https://api.yoryor.com/api/v1`

**Development:** `http://localhost:8000/api/v1`

All API endpoints are RESTful and return JSON responses. The API uses Laravel Sanctum for authentication via bearer tokens.

### Technology Stack
- **Backend:** Laravel 12, PHP 8.2+
- **WebSocket:** Laravel Reverb (port 8080)
- **Storage:** Cloudflare R2 (S3-compatible)
- **Video Calling:** VideoSDK.live (primary), Agora RTC (backup)
- **Push Notifications:** Expo Push Service
- **Authentication:** Laravel Sanctum

### Required Headers

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
X-CSRF-TOKEN: {csrf_token} (for web requests)
```

---

## Authentication

YorYor uses Laravel Sanctum for API authentication. After successful login, clients receive a bearer token to authenticate subsequent requests.

### Token Usage
```http
Authorization: Bearer {your-token-here}
```

### Token Lifecycle
- Tokens are issued upon successful authentication
- Tokens remain valid until explicitly revoked via logout
- Store tokens securely (never in localStorage for web apps)
- Refresh tokens on app launch if expired

---

## Rate Limiting

The API implements multiple rate limiting strategies based on action types:

| Action Type | Limit | Description |
|------------|-------|-------------|
| `auth_action` | 10/minute | Login, register, check email |
| `like_action` | 100/hour | Likes, dislikes, matches |
| `message_action` | 500/hour | Send messages |
| `call_action` | 50/hour | Initiate, join, end calls |
| `panic_activation` | 5/day | Emergency panic button |
| `profile_update` | 30/hour | Profile modifications |
| `block_action` | 20/hour | Block/unblock users |
| `report_action` | 10/hour | Report users |
| `verification_submit` | 3/day | Verification requests |
| `password_change` | 5/hour | Password changes |
| `email_change` | 3/day | Email changes |
| `account_deletion` | 1/day | Account deletion |
| `data_export` | 2/week | Data export requests |
| `location_update` | 100/hour | Location updates |
| `story_action` | 20/day | Create/delete stories |

### Chat-Specific Rate Limits
| Action | Limit |
|--------|-------|
| `create_chat` | 50/hour |
| `send_message` | 500/hour |
| `mark_read` | 1000/hour |
| `edit_message` | 100/hour |
| `delete_message` | 100/hour |

### Rate Limit Headers
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1640995200
```

---

## Authentication Endpoints

### Check Email
Check if an email is registered and get authentication method.

**Endpoint:** `POST /v1/auth/check-email`

**Rate Limit:** `auth_action` (10/minute)

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "exists": true,
  "requires_password": true,
  "can_use_otp": true,
  "message": "Account found"
}
```

---

### Authenticate
Login or register using email/password or OTP.

**Endpoint:** `POST /v1/auth/authenticate`

**Rate Limit:** `auth_action` (10/minute)

**Request (Password):**
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123",
  "device_name": "iPhone 12"
}
```

**Request (OTP):**
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "device_name": "iPhone 12"
}
```

**Response:**
```json
{
  "status": "success",
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "email": "user@example.com",
    "name": "John Doe",
    "is_new_user": false,
    "profile_completed": true,
    "profile_completion": 85
  }
}
```

---

### Complete Registration
Complete new user registration with profile information.

**Endpoint:** `POST /v1/auth/complete-registration`

**Auth Required:** Yes

**Request:**
```json
{
  "name": "John Doe",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "country_id": 1,
  "location": {
    "latitude": 40.7128,
    "longitude": -74.0060,
    "city": "New York",
    "country": "US"
  }
}
```

**Response:**
```json
{
  "status": "success",
  "user": {
    "id": 1,
    "uuid": "550e8400-e29b-41d4-a716-446655440000",
    "name": "John Doe",
    "email": "user@example.com",
    "is_new_user": false
  },
  "profile": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "age": 35,
    "gender": "male",
    "country_id": 1
  }
}
```

---

### Logout
Revoke current authentication token.

**Endpoint:** `POST /v1/auth/logout`

**Auth Required:** Yes

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

### Two-Factor Authentication

#### Enable 2FA
**Endpoint:** `POST /v1/auth/2fa/enable`

**Auth Required:** Yes

**Response:**
```json
{
  "qr_code": "data:image/svg+xml;base64,...",
  "secret": "JBSWY3DPEHPK3PXP",
  "backup_codes": [
    "12345678",
    "87654321",
    "11223344"
  ]
}
```

---

#### Disable 2FA
**Endpoint:** `POST /v1/auth/2fa/disable`

**Auth Required:** Yes

**Request:**
```json
{
  "password": "CurrentPassword123"
}
```

**Response:**
```json
{
  "message": "Two-factor authentication disabled successfully"
}
```

---

#### Verify 2FA Code
**Endpoint:** `POST /v1/auth/2fa/verify`

**Auth Required:** Yes

**Request:**
```json
{
  "code": "123456"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Code verified successfully"
}
```

---

### Get Home Stats
**Endpoint:** `GET /v1/auth/home-stats`

**Auth Required:** Yes

**Response:**
```json
{
  "new_likes": 5,
  "new_matches": 2,
  "unread_messages": 10,
  "profile_views": 50,
  "online_matches": 3
}
```

---

## Profile Management

### Get My Profile
Retrieve authenticated user's complete profile.

**Endpoint:** `GET /v1/profile/me`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "+1234567890",
  "bio": "Looking for meaningful connection",
  "date_of_birth": "1990-01-15",
  "age": 35,
  "gender": "male",
  "country": {
    "id": 1,
    "name": "United States",
    "code": "US",
    "phone_code": "+1"
  },
  "location": {
    "latitude": 40.7128,
    "longitude": -74.0060,
    "city": "New York",
    "country": "US"
  },
  "photos": [
    {
      "id": 1,
      "url": "https://r2.yoryor.com/photos/123.jpg",
      "thumbnail_url": "https://r2.yoryor.com/photos/123_thumb.jpg",
      "is_primary": true,
      "is_verified": true,
      "order": 1
    }
  ],
  "preferences": {
    "age_min": 25,
    "age_max": 35,
    "gender": "female",
    "distance_max": 50
  },
  "profile_completion": 85,
  "last_active_at": "2025-09-28T10:30:00Z"
}
```

---

### Get Profile Completion Status
**Endpoint:** `GET /v1/profile/completion-status`

**Auth Required:** Yes

**Response:**
```json
{
  "completion_percentage": 85,
  "completed_sections": [
    "basic_info",
    "photos",
    "preferences",
    "cultural_profile"
  ],
  "incomplete_sections": [
    "career_profile",
    "physical_profile"
  ],
  "required_actions": [
    "Add at least 2 more photos",
    "Complete career information"
  ]
}
```

---

### Update Profile
**Endpoint:** `PUT /v1/profile/{profile_id}`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "bio": "Updated bio text",
  "looking_for": "serious_relationship",
  "education": "bachelor",
  "occupation": "Software Engineer",
  "height": 175,
  "interests": ["hiking", "photography", "cooking"]
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "bio": "Updated bio text",
    "occupation": "Software Engineer",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Get User Profile (View Others)
**Endpoint:** `GET /v1/users/{userId}/profile`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 123,
  "uuid": "550e8400-e29b-41d4-a716-446655440001",
  "name": "Jane Smith",
  "age": 28,
  "bio": "Love to travel...",
  "distance": 5.2,
  "photos": [
    {
      "id": 2,
      "url": "https://r2.yoryor.com/photos/456.jpg",
      "is_primary": true
    }
  ],
  "interests": ["Travel", "Music"],
  "education": "Masters in Computer Science",
  "occupation": "Designer",
  "compatibility_score": 85,
  "is_online": true,
  "last_active_at": "2025-09-28T11:45:00Z"
}
```

---

### Cultural Profile

#### Get Cultural Profile
**Endpoint:** `GET /v1/profile/cultural`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "religion": "muslim",
  "religiosity": "moderate",
  "sect": "sunni",
  "prayer_frequency": "five_times_daily",
  "dietary_preferences": "halal_only",
  "languages": ["English", "Arabic"],
  "ethnicity": "Arab",
  "cultural_values": ["family_oriented", "traditional"]
}
```

---

#### Update Cultural Profile
**Endpoint:** `PUT /v1/profile/cultural`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "religion": "muslim",
  "religiosity": "moderate",
  "sect": "sunni",
  "prayer_frequency": "five_times_daily",
  "dietary_preferences": "halal_only",
  "languages": ["English", "Arabic"],
  "ethnicity": "Arab",
  "cultural_values": ["family_oriented", "traditional"]
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "religion": "muslim",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Family Preferences

#### Get Family Preferences
**Endpoint:** `GET /v1/profile/family-preferences`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "marital_status": "never_married",
  "wants_children": true,
  "number_of_children": 0,
  "living_situation": "own_place",
  "family_involvement": "involved",
  "family_values": "traditional",
  "parenting_style": null
}
```

---

#### Update Family Preferences
**Endpoint:** `PUT /v1/profile/family-preferences`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "marital_status": "never_married",
  "wants_children": true,
  "number_of_children": 0,
  "living_situation": "own_place",
  "family_involvement": "involved",
  "family_values": "traditional"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "marital_status": "never_married",
    "wants_children": true,
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Location Preferences

#### Get Location Preferences
**Endpoint:** `GET /v1/profile/location-preferences`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "current_city": "New York",
  "current_country": "United States",
  "willing_to_relocate": true,
  "preferred_countries": [1, 2, 3],
  "distance_preference": 50
}
```

---

#### Update Location Preferences
**Endpoint:** `PUT /v1/profile/location-preferences`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "current_city": "New York",
  "willing_to_relocate": true,
  "preferred_countries": [1, 2, 3],
  "distance_preference": 50
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "current_city": "New York",
    "willing_to_relocate": true,
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Career Profile

#### Get Career Profile
**Endpoint:** `GET /v1/profile/career`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "education_level": "masters",
  "field_of_study": "Computer Science",
  "occupation": "Software Engineer",
  "company": "Tech Corp",
  "income_level": "comfortable",
  "career_goals": "Startup founder",
  "work_schedule": "flexible"
}
```

---

#### Update Career Profile
**Endpoint:** `PUT /v1/profile/career`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "education_level": "masters",
  "field_of_study": "Computer Science",
  "occupation": "Software Engineer",
  "company": "Tech Corp",
  "income_level": "comfortable",
  "career_goals": "Startup founder",
  "work_schedule": "flexible"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "education_level": "masters",
    "occupation": "Software Engineer",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Physical Profile

#### Get Physical Profile
**Endpoint:** `GET /v1/profile/physical`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "user_id": 1,
  "height": 175,
  "body_type": "athletic",
  "ethnicity": "Asian",
  "hair_color": "black",
  "eye_color": "brown",
  "build": "average"
}
```

---

#### Update Physical Profile
**Endpoint:** `PUT /v1/profile/physical`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

**Request:**
```json
{
  "height": 175,
  "body_type": "athletic",
  "ethnicity": "Asian",
  "hair_color": "black",
  "eye_color": "brown",
  "build": "average"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "height": 175,
    "body_type": "athletic",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Comprehensive Profile

#### Get All Profile Data
**Endpoint:** `GET /v1/profile/comprehensive`

**Auth Required:** Yes

Returns all profile sections in one response (basic info, cultural, family, location, career, physical, photos, preferences).

**Response:**
```json
{
  "basic": { /* basic profile data */ },
  "cultural": { /* cultural profile data */ },
  "family": { /* family preferences */ },
  "location": { /* location preferences */ },
  "career": { /* career profile */ },
  "physical": { /* physical profile */ },
  "photos": [ /* array of photos */ ],
  "preferences": { /* user preferences */ }
}
```

---

#### Update All Profile Data
**Endpoint:** `PUT /v1/profile/comprehensive`

**Auth Required:** Yes

**Rate Limit:** `profile_update` (30/hour)

Update multiple profile sections at once. Submit only the sections you want to update.

**Request:**
```json
{
  "basic": {
    "bio": "Updated bio"
  },
  "cultural": {
    "religiosity": "moderate"
  },
  "career": {
    "occupation": "Software Engineer"
  }
}
```

---

### Photos

#### Get Photos
**Endpoint:** `GET /v1/photos`

**Auth Required:** Yes

**Response:**
```json
{
  "photos": [
    {
      "id": 1,
      "url": "https://r2.yoryor.com/photos/123.jpg",
      "thumbnail_url": "https://r2.yoryor.com/photos/123_thumb.jpg",
      "is_primary": true,
      "is_verified": true,
      "order": 1,
      "uploaded_at": "2025-09-27T15:30:00Z"
    }
  ]
}
```

---

#### Upload Photo
**Endpoint:** `POST /v1/photos/upload`

**Auth Required:** Yes

**Request:** multipart/form-data
```
photo: (file - JPEG, PNG, max 10MB)
is_primary: true|false
order: 1
type: "profile"|"verification"
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 2,
    "url": "https://r2.yoryor.com/photos/456.jpg",
    "thumbnail_url": "https://r2.yoryor.com/photos/456_thumb.jpg",
    "is_primary": false,
    "order": 2
  }
}
```

---

#### Update Photo
**Endpoint:** `PUT /v1/photos/{id}`

**Auth Required:** Yes

**Request:**
```json
{
  "is_primary": true,
  "order": 1
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "is_primary": true,
    "order": 1,
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Delete Photo
**Endpoint:** `DELETE /v1/photos/{id}`

**Auth Required:** Yes

**Response:**
```json
{
  "message": "Photo deleted successfully"
}
```

---

## User Discovery & Matching

### Get Discovery Profiles
Get profiles for the discovery/swipe interface.

**Endpoint:** `POST /v1/discovery-profiles`

**Auth Required:** Yes

**Request:**
```json
{
  "limit": 10,
  "filters": {
    "age_min": 25,
    "age_max": 35,
    "distance": 50,
    "gender": "female",
    "religion": "muslim",
    "education_level": "bachelor"
  }
}
```

**Response:**
```json
{
  "profiles": [
    {
      "id": 123,
      "uuid": "550e8400-e29b-41d4-a716-446655440001",
      "name": "Jane",
      "age": 28,
      "photos": [
        {
          "id": 2,
          "url": "https://r2.yoryor.com/photos/456.jpg",
          "is_primary": true
        }
      ],
      "bio": "Love to travel...",
      "distance": 15.5,
      "compatibility_score": 85,
      "education": "Masters",
      "occupation": "Designer",
      "religion": "muslim"
    }
  ],
  "has_more": true
}
```

---

### Get Potential Matches
**Endpoint:** `GET /v1/matches/potential`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number (default: 1)
- `limit`: Results per page (default: 20, max: 100)

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "name": "Jane Smith",
      "age": 28,
      "bio": "Love to travel...",
      "distance": 5.2,
      "photos": [ /* array of photos */ ],
      "compatibility_score": 85
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3,
    "has_more_pages": true
  }
}
```

---

### Get Matches
Get list of mutual matches.

**Endpoint:** `GET /v1/matches`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number
- `limit`: Results per page

**Response:**
```json
{
  "matches": [
    {
      "id": 1,
      "match_id": 456,
      "user": {
        "id": 123,
        "name": "Jane Smith",
        "age": 28,
        "photos": [ /* array */ ],
        "is_online": true
      },
      "matched_at": "2025-09-28T10:30:00Z",
      "has_chat": true,
      "last_message": {
        "content": "Hi there!",
        "sent_at": "2025-09-28T11:00:00Z"
      },
      "unread_count": 2
    }
  ]
}
```

---

### Like User
**Endpoint:** `POST /v1/likes`

**Auth Required:** Yes

**Rate Limit:** `like_action` (100/hour)

**Request:**
```json
{
  "user_id": 123,
  "is_super_like": false
}
```

**Response:**
```json
{
  "status": "success",
  "matched": true,
  "match_id": 456,
  "message": "It's a match!"
}
```

**Response (No Match):**
```json
{
  "status": "success",
  "matched": false,
  "message": "Like sent successfully"
}
```

---

### Pass on User
**Endpoint:** `POST /v1/profiles/{user}/pass`

**Auth Required:** Yes

**Rate Limit:** `like_action` (100/hour)

**Response:**
```json
{
  "status": "success",
  "message": "User passed"
}
```

---

### Get Received Likes
**Endpoint:** `GET /v1/likes/received`

**Auth Required:** Yes

**Response:**
```json
{
  "likes": [
    {
      "id": 1,
      "user": {
        "id": 123,
        "name": "Jane Smith",
        "age": 28,
        "photos": [ /* array */ ]
      },
      "liked_at": "2025-09-28T10:00:00Z",
      "is_super_like": false
    }
  ],
  "total_count": 15
}
```

---

### Get Sent Likes
**Endpoint:** `GET /v1/likes/sent`

**Auth Required:** Yes

**Response:**
```json
{
  "likes": [
    {
      "id": 1,
      "user": {
        "id": 124,
        "name": "Sarah Johnson",
        "age": 26,
        "photos": [ /* array */ ]
      },
      "liked_at": "2025-09-28T09:00:00Z",
      "is_super_like": false
    }
  ]
}
```

---

### Delete Match
**Endpoint:** `DELETE /v1/matches/{id}`

**Auth Required:** Yes

**Rate Limit:** `like_action` (100/hour)

**Response:**
```json
{
  "message": "Match deleted successfully"
}
```

---

### Block User
**Endpoint:** `POST /v1/users/{userId}/block`

**Auth Required:** Yes

**Rate Limit:** `block_action` (20/hour)

**Response:**
```json
{
  "status": "success",
  "message": "User blocked successfully"
}
```

---

### Unblock User
**Endpoint:** `DELETE /v1/users/{userId}/unblock`

**Auth Required:** Yes

**Rate Limit:** `block_action` (20/hour)

**Response:**
```json
{
  "status": "success",
  "message": "User unblocked successfully"
}
```

---

### Report User
**Endpoint:** `POST /v1/users/{userId}/report`

**Auth Required:** Yes

**Rate Limit:** `report_action` (10/hour)

**Request:**
```json
{
  "reason": "inappropriate_behavior",
  "category": "harassment",
  "details": "Description of the issue",
  "evidence": ["screenshot1.jpg", "screenshot2.jpg"]
}
```

**Response:**
```json
{
  "status": "success",
  "report_id": 789,
  "message": "User reported successfully. Our team will review this shortly."
}
```

---

### Get Report Reasons
**Endpoint:** `GET /v1/report-reasons`

**Auth Required:** Yes

**Response:**
```json
{
  "categories": [
    {
      "id": "inappropriate_behavior",
      "label": "Inappropriate Behavior",
      "reasons": [
        "harassment",
        "offensive_language",
        "sexual_content"
      ]
    },
    {
      "id": "fake_profile",
      "label": "Fake Profile",
      "reasons": [
        "fake_photos",
        "impersonation",
        "scam"
      ]
    }
  ]
}
```

---

## Chat & Messaging

### Get Chats
Retrieve all chat conversations.

**Endpoint:** `GET /v1/chats`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number
- `per_page`: Results per page (default: 20)

**Response:**
```json
{
  "chats": [
    {
      "id": 1,
      "match_id": 456,
      "type": "private",
      "user": {
        "id": 123,
        "name": "Jane Smith",
        "photo_url": "https://r2.yoryor.com/photos/123.jpg",
        "is_online": true,
        "last_active_at": "2025-09-28T11:55:00Z"
      },
      "last_message": {
        "id": 789,
        "content": "Hello!",
        "type": "text",
        "sent_at": "2025-09-28T12:00:00Z",
        "read": false
      },
      "unread_count": 3,
      "updated_at": "2025-09-28T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 15,
    "per_page": 20
  }
}
```

---

### Get Unread Count
**Endpoint:** `GET /v1/chats/unread-count`

**Auth Required:** Yes

**Response:**
```json
{
  "unread_count": 5,
  "unread_chats": 3
}
```

---

### Get Single Chat
**Endpoint:** `GET /v1/chats/{id}`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number for messages
- `per_page`: Messages per page (default: 50)

**Response:**
```json
{
  "id": 1,
  "match_id": 456,
  "users": [
    {
      "id": 1,
      "name": "John Doe"
    },
    {
      "id": 123,
      "name": "Jane Smith",
      "is_online": true
    }
  ],
  "messages": [
    {
      "id": 1,
      "user_id": 123,
      "content": "Hi!",
      "type": "text",
      "sent_at": "2025-09-28T12:00:00Z",
      "read": true,
      "read_at": "2025-09-28T12:01:00Z",
      "edited": false,
      "deleted": false
    },
    {
      "id": 2,
      "user_id": 1,
      "content": "Hello! How are you?",
      "type": "text",
      "sent_at": "2025-09-28T12:02:00Z",
      "read": false,
      "read_at": null
    }
  ],
  "meta": {
    "current_page": 1,
    "total_messages": 45,
    "per_page": 50
  }
}
```

---

### Create or Get Chat
**Endpoint:** `POST /v1/chats/create`

**Auth Required:** Yes

**Rate Limit:** `create_chat` (50/hour)

**Request:**
```json
{
  "match_id": 456
}
```

**Response:**
```json
{
  "status": "success",
  "chat": {
    "id": 1,
    "match_id": 456,
    "created_at": "2025-09-28T12:00:00Z"
  },
  "is_new": true
}
```

---

### Send Message
**Endpoint:** `POST /v1/chats/{id}/messages`

**Auth Required:** Yes

**Rate Limit:** `send_message` (500/hour)

**Request (Text):**
```json
{
  "content": "Hello there!",
  "type": "text",
  "reply_to_message_id": null
}
```

**Request (Media):**
```json
{
  "type": "image",
  "media_url": "https://r2.yoryor.com/media/123.jpg",
  "content": "Check this out",
  "caption": "Beautiful sunset"
}
```

**Response:**
```json
{
  "id": 790,
  "chat_id": 1,
  "user_id": 1,
  "content": "Hello there!",
  "type": "text",
  "sent_at": "2025-09-28T12:05:00Z",
  "read": false,
  "read_at": null
}
```

---

### Send Media Message
**Endpoint:** `POST /v1/chats/{chatId}/messages`

**Auth Required:** Yes

**Rate Limit:** `send_message` (500/hour)

**Request:** Multipart form data
- `type`: "image" | "video" | "voice" | "document"
- `media`: Media file
- `caption`: Optional caption text
- `duration`: Duration in seconds (for voice/video)

**Response:**
```json
{
  "id": 791,
  "chat_id": 1,
  "user_id": 1,
  "type": "image",
  "media_url": "https://r2.yoryor.com/media/456.jpg",
  "caption": "Check this out",
  "sent_at": "2025-09-28T12:06:00Z"
}
```

---

### Mark Messages as Read
**Endpoint:** `POST /v1/chats/{id}/read`

**Auth Required:** Yes

**Rate Limit:** `mark_read` (1000/hour)

**Request:**
```json
{
  "message_ids": [1, 2, 3]
}
```

**Response:**
```json
{
  "status": "success",
  "marked_read": 3
}
```

---

### Edit Message
**Endpoint:** `PUT /v1/chats/{chat_id}/messages/{message_id}`

**Auth Required:** Yes

**Rate Limit:** `edit_message` (100/hour)

**Request:**
```json
{
  "content": "Updated message content"
}
```

**Response:**
```json
{
  "status": "success",
  "message": {
    "id": 789,
    "content": "Updated message content",
    "edited": true,
    "edited_at": "2025-09-28T12:10:00Z"
  }
}
```

---

### Delete Message
**Endpoint:** `DELETE /v1/chats/{chat_id}/messages/{message_id}`

**Auth Required:** Yes

**Rate Limit:** `delete_message` (100/hour)

**Response:**
```json
{
  "message": "Message deleted successfully"
}
```

---

### Delete Chat
**Endpoint:** `DELETE /v1/chats/{id}`

**Auth Required:** Yes

**Response:**
```json
{
  "message": "Chat deleted successfully"
}
```

---

### Get Call Messages
**Endpoint:** `GET /v1/chats/{id}/call-messages`

**Auth Required:** Yes

**Response:**
```json
{
  "call_messages": [
    {
      "id": 1,
      "chat_id": 1,
      "call_id": 789,
      "type": "call",
      "call_type": "video",
      "duration": 300,
      "status": "completed",
      "created_at": "2025-09-28T10:00:00Z"
    }
  ]
}
```

---

### Get Call Statistics
**Endpoint:** `GET /v1/chats/{id}/call-statistics`

**Auth Required:** Yes

**Response:**
```json
{
  "total_calls": 15,
  "total_duration": 4500,
  "video_calls": 10,
  "audio_calls": 5,
  "average_duration": 300,
  "last_call": "2025-09-28T10:00:00Z"
}
```

---

## Video Calling

### VideoSDK (Primary Provider)

#### Get Token
**Endpoint:** `POST /v1/video-call/token`

**Auth Required:** Yes

**Rate Limit:** `call_action` (50/hour)

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

---

#### Create Meeting
**Endpoint:** `POST /v1/video-call/create-meeting`

**Auth Required:** Yes

**Rate Limit:** `call_action` (50/hour)

**Response:**
```json
{
  "meeting_id": "abc-def-ghi",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "status": "created"
}
```

---

#### Validate Meeting
**Endpoint:** `GET /v1/video-call/validate-meeting/{meetingId}`

**Auth Required:** Yes

**Response:**
```json
{
  "valid": true,
  "meeting_id": "abc-def-ghi",
  "status": "active"
}
```

---

#### Initiate Call
**Endpoint:** `POST /v1/video-call/initiate`

**Auth Required:** Yes

**Rate Limit:** `call_action` (50/hour)

**Request:**
```json
{
  "receiver_id": 456,
  "type": "video",
  "meeting_id": "abc-def-ghi"
}
```

**Response:**
```json
{
  "call_id": 789,
  "meeting_id": "abc-def-ghi",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "status": "initiated",
  "caller": {
    "id": 1,
    "name": "John Doe"
  },
  "receiver": {
    "id": 456,
    "name": "Jane Smith"
  }
}
```

---

#### Join Call
**Endpoint:** `POST /v1/video-call/{callId}/join`

**Auth Required:** Yes

**Response:**
```json
{
  "call_id": 789,
  "meeting_id": "abc-def-ghi",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "participant_id": "user_123",
  "status": "joined"
}
```

---

#### End Call
**Endpoint:** `POST /v1/video-call/{callId}/end`

**Auth Required:** Yes

**Request:**
```json
{
  "duration": 300,
  "end_reason": "completed"
}
```

**Response:**
```json
{
  "call_id": 789,
  "duration": 300,
  "status": "ended",
  "ended_at": "2025-09-28T12:15:00Z"
}
```

---

#### Reject Call
**Endpoint:** `POST /v1/video-call/{callId}/reject`

**Auth Required:** Yes

**Response:**
```json
{
  "call_id": 789,
  "status": "rejected",
  "rejected_at": "2025-09-28T12:00:00Z"
}
```

---

#### Handle Missed Call
**Endpoint:** `POST /v1/video-call/{callId}/missed`

**Auth Required:** Yes

**Response:**
```json
{
  "call_id": 789,
  "status": "missed",
  "missed_at": "2025-09-28T12:00:00Z"
}
```

---

#### Get Call History
**Endpoint:** `GET /v1/video-call/history`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number
- `type`: Filter by call type (video/audio)
- `status`: Filter by status (completed/missed/rejected)

**Response:**
```json
{
  "calls": [
    {
      "id": 789,
      "type": "video",
      "status": "completed",
      "duration": 300,
      "caller": {
        "id": 1,
        "name": "John Doe"
      },
      "receiver": {
        "id": 456,
        "name": "Jane Smith"
      },
      "started_at": "2025-09-28T12:00:00Z",
      "ended_at": "2025-09-28T12:05:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 20
  }
}
```

---

#### Get Call Analytics
**Endpoint:** `GET /v1/video-call/analytics`

**Auth Required:** Yes

**Response:**
```json
{
  "total_calls": 50,
  "total_duration": 15000,
  "video_calls": 35,
  "audio_calls": 15,
  "average_duration": 300,
  "call_success_rate": 0.85,
  "this_week": {
    "total_calls": 5,
    "total_duration": 1500
  }
}
```

---

### Agora (Backup Provider)

#### Generate Token
**Endpoint:** `POST /v1/agora/token`

**Auth Required:** Yes

**Rate Limit:** `call_action` (50/hour)

**Request:**
```json
{
  "channel_name": "call_123",
  "uid": 12345
}
```

**Response:**
```json
{
  "token": "006abc123def456...",
  "channel_name": "call_123",
  "uid": 12345
}
```

---

#### Initiate Call
**Endpoint:** `POST /v1/agora/initiate`

**Auth Required:** Yes

**Rate Limit:** `call_action` (50/hour)

**Request:**
```json
{
  "receiver_id": 456,
  "type": "video"
}
```

**Response:**
```json
{
  "call_id": 789,
  "channel_name": "call_789",
  "token": "006abc123def456...",
  "uid": 12345,
  "status": "initiated"
}
```

---

#### Join Call
**Endpoint:** `POST /v1/agora/{callId}/join`

**Auth Required:** Yes

**Response:**
```json
{
  "call_id": 789,
  "channel_name": "call_789",
  "token": "006abc123def456...",
  "uid": 67890,
  "status": "joined"
}
```

---

#### End Call
**Endpoint:** `POST /v1/agora/{callId}/end`

**Auth Required:** Yes

**Request:**
```json
{
  "duration": 300
}
```

**Response:**
```json
{
  "call_id": 789,
  "duration": 300,
  "status": "ended"
}
```

---

#### Reject Call
**Endpoint:** `POST /v1/agora/{callId}/reject`

**Auth Required:** Yes

**Response:**
```json
{
  "call_id": 789,
  "status": "rejected"
}
```

---

#### Get Call History
**Endpoint:** `GET /v1/agora/history`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number
- `type`: Filter by call type

**Response:**
```json
{
  "calls": [
    {
      "id": 789,
      "type": "video",
      "status": "completed",
      "duration": 300,
      "started_at": "2025-09-28T12:00:00Z",
      "ended_at": "2025-09-28T12:05:00Z"
    }
  ]
}
```

---

## Stories

### Get User Stories
Get stories posted by authenticated user.

**Endpoint:** `GET /v1/stories`

**Auth Required:** Yes

**Response:**
```json
{
  "stories": [
    {
      "id": 1,
      "user_id": 1,
      "media_url": "https://r2.yoryor.com/stories/123.jpg",
      "media_type": "image",
      "created_at": "2025-09-28T10:00:00Z",
      "expires_at": "2025-09-29T10:00:00Z",
      "views_count": 15,
      "is_expired": false
    }
  ]
}
```

---

### Get Matched Users Stories
Get stories from matched users.

**Endpoint:** `GET /v1/stories/matches`

**Auth Required:** Yes

**Response:**
```json
{
  "users": [
    {
      "id": 123,
      "name": "Jane Smith",
      "photo": "https://r2.yoryor.com/photos/456.jpg",
      "stories": [
        {
          "id": 1,
          "media_url": "https://r2.yoryor.com/stories/789.jpg",
          "media_type": "image",
          "created_at": "2025-09-28T10:00:00Z",
          "expires_at": "2025-09-29T10:00:00Z",
          "viewed": false
        },
        {
          "id": 2,
          "media_url": "https://r2.yoryor.com/stories/790.mp4",
          "media_type": "video",
          "created_at": "2025-09-28T11:00:00Z",
          "expires_at": "2025-09-29T11:00:00Z",
          "viewed": true
        }
      ],
      "unviewed_count": 1,
      "total_stories": 2
    }
  ]
}
```

---

### Create Story
**Endpoint:** `POST /v1/stories`

**Auth Required:** Yes

**Rate Limit:** `story_action` (20/day)

**Request:** multipart/form-data
```
media: (file - JPEG, PNG, MP4, max 50MB)
type: image|video
duration: (seconds, for video only)
```

**Response:**
```json
{
  "id": 1,
  "media_url": "https://r2.yoryor.com/stories/123.jpg",
  "media_type": "image",
  "expires_at": "2025-09-29T10:00:00Z",
  "created_at": "2025-09-28T10:00:00Z"
}
```

---

### Delete Story
**Endpoint:** `DELETE /v1/stories/{id}`

**Auth Required:** Yes

**Rate Limit:** `story_action` (20/day)

**Response:**
```json
{
  "message": "Story deleted successfully"
}
```

---

### View Story
**Endpoint:** `POST /v1/stories/{id}/view`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "views_count": 16
}
```

---

## Settings & Account

### Get All Settings
**Endpoint:** `GET /v1/settings`

**Auth Required:** Yes

**Response:**
```json
{
  "notifications": {
    "push_enabled": true,
    "email_notifications": false,
    "new_matches": true,
    "messages": true,
    "likes": false,
    "daily_summary": true
  },
  "privacy": {
    "show_online_status": true,
    "show_distance": true,
    "show_last_active": false,
    "show_read_receipts": true,
    "profile_visibility": "everyone"
  },
  "discovery": {
    "age_min": 25,
    "age_max": 35,
    "distance": 50,
    "show_me": "everyone",
    "discoverable": true,
    "show_recently_active": true
  },
  "security": {
    "two_factor_enabled": false,
    "login_alerts": true
  }
}
```

---

### Update Settings
**Endpoint:** `PUT /v1/settings`

**Auth Required:** Yes

**Request:**
```json
{
  "notifications": {
    "new_matches": true,
    "messages": true
  },
  "privacy": {
    "show_online_status": false
  }
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Settings updated successfully"
}
```

---

### Notification Settings

#### Get Notification Settings
**Endpoint:** `GET /v1/settings/notifications`

**Auth Required:** Yes

**Response:**
```json
{
  "push_enabled": true,
  "email_notifications": false,
  "new_matches": true,
  "messages": true,
  "likes": false,
  "daily_summary": true,
  "marketing": false
}
```

---

#### Update Notification Settings
**Endpoint:** `PUT /v1/settings/notifications`

**Auth Required:** Yes

**Request:**
```json
{
  "push_enabled": true,
  "email_notifications": false,
  "new_matches": true,
  "messages": true,
  "likes": false,
  "daily_summary": true
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "push_enabled": true,
    "new_matches": true,
    "messages": true
  }
}
```

---

### Privacy Settings

#### Get Privacy Settings
**Endpoint:** `GET /v1/settings/privacy`

**Auth Required:** Yes

**Response:**
```json
{
  "show_online_status": true,
  "show_distance": true,
  "show_last_active": false,
  "show_read_receipts": true,
  "profile_visibility": "everyone"
}
```

---

#### Update Privacy Settings
**Endpoint:** `PUT /v1/settings/privacy`

**Auth Required:** Yes

**Request:**
```json
{
  "show_online_status": true,
  "show_distance": true,
  "show_last_active": false,
  "profile_visibility": "matches_only"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "show_online_status": true,
    "profile_visibility": "matches_only"
  }
}
```

---

### Discovery Settings

#### Get Discovery Settings
**Endpoint:** `GET /v1/settings/discovery`

**Auth Required:** Yes

**Response:**
```json
{
  "age_min": 25,
  "age_max": 35,
  "distance": 50,
  "show_me": "everyone",
  "discoverable": true
}
```

---

#### Update Discovery Settings
**Endpoint:** `PUT /v1/settings/discovery`

**Auth Required:** Yes

**Request:**
```json
{
  "age_min": 25,
  "age_max": 35,
  "distance": 50,
  "show_me": "everyone"
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "age_min": 25,
    "age_max": 35,
    "distance": 50
  }
}
```

---

### Security Settings

#### Get Security Settings
**Endpoint:** `GET /v1/settings/security`

**Auth Required:** Yes

**Response:**
```json
{
  "two_factor_enabled": false,
  "login_alerts": true,
  "session_timeout": 30
}
```

---

#### Update Security Settings
**Endpoint:** `PUT /v1/settings/security`

**Auth Required:** Yes

**Request:**
```json
{
  "login_alerts": true,
  "session_timeout": 30
}
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "login_alerts": true,
    "session_timeout": 30
  }
}
```

---

### Account Management

#### Change Password
**Endpoint:** `PUT /v1/account/password`

**Auth Required:** Yes

**Rate Limit:** `password_change` (5/hour)

**Request:**
```json
{
  "current_password": "OldPassword123",
  "new_password": "NewPassword123",
  "new_password_confirmation": "NewPassword123"
}
```

**Response:**
```json
{
  "message": "Password changed successfully"
}
```

---

#### Change Email
**Endpoint:** `PUT /v1/account/email`

**Auth Required:** Yes

**Rate Limit:** `email_change` (3/day)

**Request:**
```json
{
  "email": "newemail@example.com",
  "password": "CurrentPassword123"
}
```

**Response:**
```json
{
  "message": "Email updated successfully. Please verify your new email address."
}
```

---

#### Delete Account
**Endpoint:** `DELETE /v1/account`

**Auth Required:** Yes

**Rate Limit:** `account_deletion` (1/day)

**Request:**
```json
{
  "password": "CurrentPassword123",
  "reason": "Found someone",
  "feedback": "Optional feedback about the app"
}
```

**Response:**
```json
{
  "message": "Account deleted successfully. You have 30 days to recover your account."
}
```

---

#### Request Data Export
**Endpoint:** `POST /v1/account/export-data`

**Auth Required:** Yes

**Rate Limit:** `data_export` (2/week)

**Response:**
```json
{
  "message": "Export request received",
  "request_id": 123,
  "estimated_completion": "2025-09-30T12:00:00Z",
  "status": "pending"
}
```

---

### Blocked Users

#### Get Blocked Users
**Endpoint:** `GET /v1/blocked-users`

**Auth Required:** Yes

**Response:**
```json
{
  "blocked_users": [
    {
      "id": 123,
      "name": "Blocked User",
      "photo": "https://r2.yoryor.com/photos/123.jpg",
      "blocked_at": "2025-09-28T10:00:00Z"
    }
  ],
  "total_count": 5
}
```

---

#### Block User
**Endpoint:** `POST /v1/blocked-users`

**Auth Required:** Yes

**Rate Limit:** `block_action` (20/hour)

**Request:**
```json
{
  "user_id": 123,
  "reason": "inappropriate_behavior"
}
```

**Response:**
```json
{
  "status": "success",
  "message": "User blocked successfully"
}
```

---

#### Unblock User
**Endpoint:** `DELETE /v1/blocked-users/{userId}`

**Auth Required:** Yes

**Rate Limit:** `block_action` (20/hour)

**Response:**
```json
{
  "message": "User unblocked successfully"
}
```

---

## Safety & Emergency

### Panic Button

#### Activate Panic
**Endpoint:** `POST /v1/safety/panic/activate`

**Auth Required:** Yes

**Rate Limit:** `panic_activation` (5/day)

**Request:**
```json
{
  "location": {
    "latitude": 40.7128,
    "longitude": -74.0060,
    "accuracy": 10
  },
  "notes": "Emergency details"
}
```

**Response:**
```json
{
  "panic_id": 123,
  "status": "active",
  "contacts_notified": 3,
  "admin_notified": true,
  "activated_at": "2025-09-28T12:00:00Z"
}
```

---

#### Cancel Panic
**Endpoint:** `POST /v1/safety/panic/cancel`

**Auth Required:** Yes

**Request:**
```json
{
  "panic_id": 123,
  "reason": "False alarm"
}
```

**Response:**
```json
{
  "status": "cancelled",
  "cancelled_at": "2025-09-28T12:05:00Z"
}
```

---

#### Get Panic Status
**Endpoint:** `GET /v1/safety/panic/status`

**Auth Required:** Yes

**Response:**
```json
{
  "active_panic": {
    "id": 123,
    "status": "active",
    "activated_at": "2025-09-28T12:00:00Z",
    "location": {
      "latitude": 40.7128,
      "longitude": -74.0060
    }
  }
}
```

---

#### Get Panic History
**Endpoint:** `GET /v1/safety/panic/history`

**Auth Required:** Yes

**Response:**
```json
{
  "history": [
    {
      "id": 123,
      "status": "resolved",
      "activated_at": "2025-09-28T12:00:00Z",
      "resolved_at": "2025-09-28T12:15:00Z",
      "contacts_notified": 3
    }
  ]
}
```

---

### Safety Setup

#### Setup Safety Features
**Endpoint:** `POST /v1/safety/setup`

**Auth Required:** Yes

**Request:**
```json
{
  "emergency_contacts": [
    {
      "name": "Mom",
      "phone": "+1234567890",
      "email": "mom@example.com",
      "relationship": "parent"
    }
  ],
  "enable_panic_button": true,
  "share_location": true
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Safety features configured successfully"
}
```

---

#### Test Emergency System
**Endpoint:** `POST /v1/safety/test`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "test_notifications_sent": 3,
  "message": "Test alerts sent successfully"
}
```

---

### Emergency Contacts

#### Get Emergency Contacts
**Endpoint:** `GET /v1/safety/emergency-contacts`

**Auth Required:** Yes

**Response:**
```json
{
  "contacts": [
    {
      "id": 1,
      "name": "Mom",
      "phone": "+1234567890",
      "email": "mom@example.com",
      "relationship": "parent",
      "verified": true,
      "is_primary": true
    },
    {
      "id": 2,
      "name": "Emergency Services",
      "phone": "911",
      "type": "emergency",
      "is_primary": false
    }
  ]
}
```

---

#### Add Emergency Contact
**Endpoint:** `POST /v1/safety/emergency-contacts`

**Auth Required:** Yes

**Request:**
```json
{
  "name": "Mom",
  "phone": "+1234567890",
  "email": "mom@example.com",
  "relationship": "parent",
  "is_primary": true
}
```

**Response:**
```json
{
  "status": "success",
  "contact": {
    "id": 1,
    "name": "Mom",
    "phone": "+1234567890",
    "verified": false
  },
  "verification_code_sent": true
}
```

---

#### Update Emergency Contact
**Endpoint:** `PUT /v1/safety/emergency-contacts/{contact}`

**Auth Required:** Yes

**Request:**
```json
{
  "name": "Mother",
  "phone": "+1234567890",
  "is_primary": true
}
```

**Response:**
```json
{
  "status": "success",
  "contact": {
    "id": 1,
    "name": "Mother",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Delete Emergency Contact
**Endpoint:** `DELETE /v1/safety/emergency-contacts/{contact}`

**Auth Required:** Yes

**Response:**
```json
{
  "message": "Emergency contact deleted successfully"
}
```

---

#### Verify Emergency Contact
**Endpoint:** `POST /v1/safety/emergency-contacts/{contact}/verify`

**Auth Required:** Yes

**Request:**
```json
{
  "verification_code": "123456"
}
```

**Response:**
```json
{
  "status": "success",
  "verified": true,
  "verified_at": "2025-09-28T12:00:00Z"
}
```

---

#### Resend Verification Code
**Endpoint:** `POST /v1/safety/emergency-contacts/{contact}/resend-code`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Verification code sent"
}
```

---

### Safety Tips
**Endpoint:** `GET /v1/safety/tips`

**Auth Required:** No

**Response:**
```json
{
  "tips": [
    {
      "id": 1,
      "category": "first_date",
      "title": "Meet in Public",
      "description": "Always meet in a public place for first dates"
    },
    {
      "id": 2,
      "category": "communication",
      "title": "Keep Communication In-App",
      "description": "Keep conversations within the app until you're comfortable"
    }
  ]
}
```

---

### Admin Safety Routes

#### Get All Panics
**Endpoint:** `GET /v1/safety/admin/panics`

**Auth Required:** Admin role

**Query Parameters:**
- `status`: Filter by status (active/resolved/cancelled)
- `page`: Page number

**Response:**
```json
{
  "panics": [
    {
      "id": 123,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "status": "active",
      "location": {
        "latitude": 40.7128,
        "longitude": -74.0060
      },
      "activated_at": "2025-09-28T12:00:00Z",
      "contacts_notified": 3
    }
  ]
}
```

---

#### Resolve Panic
**Endpoint:** `POST /v1/safety/admin/panics/{panic}/resolve`

**Auth Required:** Admin role

**Request:**
```json
{
  "resolution_notes": "Contacted user, confirmed safety",
  "action_taken": "followed_up"
}
```

**Response:**
```json
{
  "status": "resolved",
  "resolved_at": "2025-09-28T12:15:00Z"
}
```

---

## Matchmaker System

### Browse Matchmakers
**Endpoint:** `GET /v1/matchmakers`

**Auth Required:** Yes

**Query Parameters:**
- `specialty`: Filter by specialty (muslim_matchmaking, etc.)
- `min_rating`: Minimum rating (1-5)
- `page`: Page number

**Response:**
```json
{
  "matchmakers": [
    {
      "id": 1,
      "name": "Sarah Smith",
      "bio": "Professional matchmaker with 10 years experience",
      "specialty": "muslim_matchmaking",
      "rating": 4.8,
      "reviews_count": 150,
      "successful_matches": 150,
      "hourly_rate": 100,
      "certifications": ["ICC Certified"],
      "years_experience": 10,
      "photo": "https://r2.yoryor.com/photos/matchmaker1.jpg"
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 25,
    "per_page": 20
  }
}
```

---

### Get Matchmaker Details
**Endpoint:** `GET /v1/matchmakers/{matchmaker}`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 1,
  "name": "Sarah Smith",
  "bio": "Professional matchmaker...",
  "specialty": "muslim_matchmaking",
  "rating": 4.8,
  "reviews_count": 150,
  "successful_matches": 150,
  "hourly_rate": 100,
  "certifications": ["ICC Certified"],
  "years_experience": 10,
  "services": [
    {
      "id": 1,
      "name": "Initial Consultation",
      "description": "60-minute consultation",
      "price": 100,
      "duration": 60
    }
  ],
  "reviews": [
    {
      "id": 1,
      "rating": 5,
      "comment": "Excellent service!",
      "client_name": "Anonymous",
      "created_at": "2025-09-27T10:00:00Z"
    }
  ]
}
```

---

### Hire Matchmaker
**Endpoint:** `POST /v1/matchmakers/{matchmaker}/hire`

**Auth Required:** Yes

**Request:**
```json
{
  "service_id": 1,
  "message": "I'm interested in your services",
  "preferred_date": "2025-09-30T14:00:00Z"
}
```

**Response:**
```json
{
  "status": "success",
  "consultation": {
    "id": 1,
    "matchmaker_id": 1,
    "service_id": 1,
    "status": "pending",
    "scheduled_at": "2025-09-30T14:00:00Z"
  }
}
```

---

### Leave Review
**Endpoint:** `POST /v1/matchmakers/{matchmaker}/review`

**Auth Required:** Yes

**Request:**
```json
{
  "rating": 5,
  "comment": "Excellent service! Very professional and found me a great match."
}
```

**Response:**
```json
{
  "status": "success",
  "review": {
    "id": 1,
    "rating": 5,
    "comment": "Excellent service!",
    "created_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Register as Matchmaker
**Endpoint:** `POST /v1/matchmakers/register`

**Auth Required:** Yes

**Request:**
```json
{
  "bio": "Professional matchmaker with 10 years of experience...",
  "specialty": "muslim_matchmaking",
  "certifications": ["ICC Certified"],
  "years_experience": 10,
  "hourly_rate": 100,
  "services": [
    {
      "name": "Initial Consultation",
      "description": "60-minute consultation",
      "price": 100,
      "duration": 60
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "matchmaker": {
    "id": 1,
    "status": "pending_approval",
    "submitted_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### My Interactions
**Endpoint:** `GET /v1/matchmakers/my/interactions`

**Auth Required:** Yes

**Response:**
```json
{
  "consultations": [
    {
      "id": 1,
      "matchmaker": {
        "id": 1,
        "name": "Sarah Smith"
      },
      "service": {
        "id": 1,
        "name": "Initial Consultation"
      },
      "status": "confirmed",
      "scheduled_at": "2025-09-30T14:00:00Z"
    }
  ],
  "introductions": [
    {
      "id": 1,
      "matchmaker": {
        "id": 1,
        "name": "Sarah Smith"
      },
      "suggested_user": {
        "id": 456,
        "name": "Jane Doe"
      },
      "status": "pending",
      "created_at": "2025-09-28T10:00:00Z"
    }
  ]
}
```

---

### Respond to Introduction
**Endpoint:** `POST /v1/matchmakers/introductions/{introduction}/respond`

**Auth Required:** Yes

**Request:**
```json
{
  "response": "accept",
  "message": "Looking forward to connecting"
}
```

**Response:**
```json
{
  "status": "success",
  "introduction": {
    "id": 1,
    "status": "accepted",
    "responded_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Matchmaker Dashboard (Matchmakers Only)

#### Get Dashboard
**Endpoint:** `GET /v1/matchmakers/dashboard`

**Auth Required:** Matchmaker role

**Response:**
```json
{
  "stats": {
    "total_clients": 25,
    "active_consultations": 5,
    "pending_introductions": 3,
    "successful_matches": 150,
    "revenue_this_month": 5000
  },
  "upcoming_consultations": [
    {
      "id": 1,
      "client": {
        "id": 123,
        "name": "John Doe"
      },
      "service": {
        "id": 1,
        "name": "Initial Consultation"
      },
      "scheduled_at": "2025-09-30T14:00:00Z"
    }
  ]
}
```

---

#### Get Clients
**Endpoint:** `GET /v1/matchmakers/clients`

**Auth Required:** Matchmaker role

**Response:**
```json
{
  "clients": [
    {
      "id": 123,
      "name": "John Doe",
      "status": "active",
      "consultations_count": 3,
      "introductions_sent": 5,
      "joined_at": "2025-09-01T10:00:00Z"
    }
  ]
}
```

---

#### Create Introduction
**Endpoint:** `POST /v1/matchmakers/introductions`

**Auth Required:** Matchmaker role

**Request:**
```json
{
  "client_a_id": 123,
  "client_b_id": 456,
  "introduction_message": "I think you two would be great together because...",
  "compatibility_notes": "Both share similar values and interests"
}
```

**Response:**
```json
{
  "status": "success",
  "introduction": {
    "id": 1,
    "status": "pending",
    "created_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Update Matchmaker Profile
**Endpoint:** `PUT /v1/matchmakers/profile`

**Auth Required:** Matchmaker role

**Request:**
```json
{
  "bio": "Updated bio",
  "hourly_rate": 120,
  "availability": {
    "monday": ["09:00-17:00"],
    "tuesday": ["09:00-17:00"]
  }
}
```

**Response:**
```json
{
  "status": "success",
  "profile": {
    "id": 1,
    "bio": "Updated bio",
    "hourly_rate": 120,
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

## Verification System

### Get Verification Status
**Endpoint:** `GET /v1/verification/status`

**Auth Required:** Yes

**Response:**
```json
{
  "verified": true,
  "verification_types": {
    "identity": {
      "verified": true,
      "verified_at": "2025-09-28T10:00:00Z"
    },
    "photo": {
      "verified": true,
      "verified_at": "2025-09-27T15:00:00Z"
    },
    "employment": {
      "verified": false,
      "status": "pending"
    },
    "education": {
      "verified": false,
      "status": "not_submitted"
    },
    "income": {
      "verified": false,
      "status": "not_submitted"
    }
  },
  "badges": ["identity_verified", "photo_verified"],
  "overall_score": 75
}
```

---

### Get Verification Requirements
**Endpoint:** `GET /v1/verification/requirements/{type}`

**Auth Required:** Yes

**Types:** `identity`, `photo`, `income`, `education`, `employment`

**Response:**
```json
{
  "type": "identity",
  "requirements": [
    "Government-issued ID (passport, driver's license, or national ID)",
    "Clear selfie holding ID",
    "Proof of address (utility bill or bank statement)"
  ],
  "document_types_accepted": [
    "passport",
    "drivers_license",
    "national_id"
  ],
  "file_formats": ["JPEG", "PNG", "PDF"],
  "max_file_size": "10MB",
  "estimated_review_time": "24-48 hours",
  "notes": "Ensure all text is clearly visible and not blurred"
}
```

---

### Submit Verification Request
**Endpoint:** `POST /v1/verification/submit`

**Auth Required:** Yes

**Rate Limit:** `verification_submit` (3/day)

**Request:** multipart/form-data
```
type: identity|photo|income|education|employment
documents: (files - JPEG, PNG, PDF)
notes: "Additional information"
document_type: "passport"|"drivers_license"|"national_id" (for identity)
```

**Response:**
```json
{
  "request_id": 123,
  "type": "identity",
  "status": "pending",
  "submitted_at": "2025-09-28T12:00:00Z",
  "estimated_completion": "2025-09-30T12:00:00Z"
}
```

---

### Get Verification Requests
**Endpoint:** `GET /v1/verification/requests`

**Auth Required:** Yes

**Response:**
```json
{
  "requests": [
    {
      "id": 123,
      "type": "identity",
      "status": "pending",
      "submitted_at": "2025-09-28T12:00:00Z",
      "reviewed_at": null
    },
    {
      "id": 122,
      "type": "photo",
      "status": "approved",
      "submitted_at": "2025-09-27T10:00:00Z",
      "reviewed_at": "2025-09-27T15:00:00Z"
    }
  ]
}
```

---

### Get Verification Request
**Endpoint:** `GET /v1/verification/requests/{verificationRequest}`

**Auth Required:** Yes

**Response:**
```json
{
  "id": 123,
  "type": "identity",
  "status": "pending",
  "submitted_at": "2025-09-28T12:00:00Z",
  "documents": [
    {
      "id": 1,
      "type": "passport",
      "url": "https://r2.yoryor.com/verifications/123.jpg"
    }
  ],
  "notes": "Additional information provided",
  "admin_notes": null,
  "reviewed_at": null
}
```

---

### Admin Verification Routes

#### Get Pending Requests
**Endpoint:** `GET /v1/verification/admin/pending`

**Auth Required:** Admin role

**Query Parameters:**
- `type`: Filter by verification type
- `page`: Page number

**Response:**
```json
{
  "requests": [
    {
      "id": 123,
      "user": {
        "id": 1,
        "name": "John Doe"
      },
      "type": "identity",
      "status": "pending",
      "submitted_at": "2025-09-28T12:00:00Z",
      "documents_count": 3
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 45,
    "per_page": 20
  }
}
```

---

#### Approve Request
**Endpoint:** `POST /v1/verification/admin/{verificationRequest}/approve`

**Auth Required:** Admin role

**Request:**
```json
{
  "notes": "Documents verified successfully. All information matches."
}
```

**Response:**
```json
{
  "status": "approved",
  "approved_at": "2025-09-28T13:00:00Z",
  "request": {
    "id": 123,
    "type": "identity",
    "user_id": 1
  }
}
```

---

#### Reject Request
**Endpoint:** `POST /v1/verification/admin/{verificationRequest}/reject`

**Auth Required:** Admin role

**Request:**
```json
{
  "reason": "Documents unclear",
  "notes": "Please resubmit with clearer photos. ID text is not legible."
}
```

**Response:**
```json
{
  "status": "rejected",
  "rejected_at": "2025-09-28T13:00:00Z",
  "reason": "Documents unclear",
  "notes": "Please resubmit with clearer photos"
}
```

---

## Support & Feedback

### Submit Feedback
**Endpoint:** `POST /v1/support/feedback`

**Auth Required:** Yes

**Request:**
```json
{
  "type": "feature_request",
  "subject": "Add video profile feature",
  "message": "It would be great to have video profiles...",
  "rating": 5,
  "category": "feature_request"
}
```

**Response:**
```json
{
  "status": "success",
  "feedback_id": 123,
  "message": "Thank you for your feedback!"
}
```

---

### Report User
**Endpoint:** `POST /v1/support/report`

**Auth Required:** Yes

**Rate Limit:** `report_action` (10/hour)

**Request:**
```json
{
  "reported_user_id": 123,
  "category": "inappropriate_behavior",
  "reason": "harassment",
  "description": "User sent inappropriate messages",
  "evidence_urls": ["https://r2.yoryor.com/evidence/123.jpg"]
}
```

**Response:**
```json
{
  "status": "success",
  "report_id": 789,
  "message": "Report submitted successfully. Our team will review this within 24 hours."
}
```

---

### Get FAQ
**Endpoint:** `GET /v1/support/faq`

**Auth Required:** No

**Query Parameters:**
- `category`: Filter by category

**Response:**
```json
{
  "faqs": [
    {
      "id": 1,
      "category": "account",
      "question": "How do I delete my account?",
      "answer": "Go to Settings > Account > Delete Account...",
      "order": 1
    },
    {
      "id": 2,
      "category": "matching",
      "question": "How does the matching algorithm work?",
      "answer": "Our algorithm considers multiple factors...",
      "order": 2
    }
  ]
}
```

---

## Notifications

### Get User Notifications
**Endpoint:** `GET /v1/notifications`

**Auth Required:** Yes

**Query Parameters:**
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20)
- `filter`: Filter by type (all, unread, matches, messages, likes, system)

**Response:**
```json
{
  "notifications": [
    {
      "id": 1,
      "type": "match",
      "title": "New Match!",
      "message": "You have a new match with Jane Doe",
      "data": {
        "user_id": 123,
        "user_name": "Jane Doe",
        "match_id": 456
      },
      "read_at": null,
      "created_at": "2025-09-28T12:00:00Z"
    },
    {
      "id": 2,
      "type": "message",
      "title": "New Message",
      "message": "Jane sent you a message",
      "data": {
        "chat_id": 1,
        "message_id": 789
      },
      "read_at": "2025-09-28T12:05:00Z",
      "created_at": "2025-09-28T12:00:00Z"
    }
  ],
  "unread_count": 5,
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 20
  }
}
```

---

### Mark Notification as Read
**Endpoint:** `POST /v1/notifications/{notificationId}/mark-read`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Notification marked as read"
}
```

---

### Mark All Notifications as Read
**Endpoint:** `POST /v1/notifications/mark-all-read`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "marked_count": 15,
  "message": "All notifications marked as read"
}
```

---

### Delete Notification
**Endpoint:** `DELETE /v1/notifications/{notificationId}`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Notification deleted"
}
```

---

## Analytics

### Get User Analytics
**Endpoint:** `GET /v1/analytics`

**Auth Required:** Yes

**Query Parameters:**
- `date_range`: Date range (7, 30, 90, 365 days)

**Response:**
```json
{
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
    "response_rate": 0.68,
    "average_response_time": 45
  },
  "likes": {
    "sent": 125,
    "received": 89,
    "like_back_rate": 0.42
  },
  "success_score": 78,
  "activity_chart": [
    {
      "date": "2025-09-28",
      "profile_views": 23,
      "likes": 5,
      "matches": 2
    }
  ]
}
```

---

## Additional Endpoints

### Get Countries
**Endpoint:** `GET /v1/countries`

**Auth Required:** No

**Response:**
```json
{
  "countries": [
    {
      "id": 1,
      "name": "United States",
      "code": "US",
      "phone_code": "+1",
      "flag_emoji": "🇺🇸"
    },
    {
      "id": 2,
      "name": "United Kingdom",
      "code": "GB",
      "phone_code": "+44",
      "flag_emoji": "🇬🇧"
    }
  ]
}
```

---

### Update Location
**Endpoint:** `POST /v1/location/update`

**Auth Required:** Yes

**Rate Limit:** `location_update` (100/hour)

**Request:**
```json
{
  "latitude": 40.7128,
  "longitude": -74.0060,
  "accuracy": 10,
  "city": "New York",
  "country": "US"
}
```

**Response:**
```json
{
  "status": "success",
  "location": {
    "latitude": 40.7128,
    "longitude": -74.0060,
    "city": "New York",
    "updated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

### Device Tokens (Push Notifications)

#### Register Device Token
**Endpoint:** `POST /v1/device-tokens`

**Auth Required:** Yes

**Request:**
```json
{
  "token": "ExponentPushToken[xxxxxx]",
  "device_type": "ios",
  "device_name": "iPhone 12",
  "device_id": "unique-device-id",
  "platform": "ios"
}
```

**Response:**
```json
{
  "status": "success",
  "device_token": {
    "id": 1,
    "token": "ExponentPushToken[xxxxxx]",
    "device_type": "ios",
    "registered_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Delete Device Token
**Endpoint:** `DELETE /v1/device-tokens`

**Auth Required:** Yes

**Request:**
```json
{
  "token": "ExponentPushToken[xxxxxx]"
}
```

**Response:**
```json
{
  "message": "Device token removed successfully"
}
```

---

### Presence & Online Status

#### Get Online Status
**Endpoint:** `GET /v1/presence/status`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "online",
  "last_active_at": "2025-09-28T12:00:00Z"
}
```

---

#### Update Online Status
**Endpoint:** `POST /v1/presence/status`

**Auth Required:** Yes

**Request:**
```json
{
  "status": "online",
  "last_active_at": "2025-09-28T12:00:00Z"
}
```

**Response:**
```json
{
  "status": "success",
  "current_status": "online"
}
```

---

#### Get Online Users
**Endpoint:** `GET /v1/presence/online-users`

**Auth Required:** Yes

**Response:**
```json
{
  "online_users": [
    {
      "id": 123,
      "name": "Jane Smith",
      "is_match": true,
      "last_active_at": "2025-09-28T11:58:00Z"
    }
  ],
  "total_count": 45
}
```

---

#### Get Online Matches
**Endpoint:** `GET /v1/presence/online-matches`

**Auth Required:** Yes

**Response:**
```json
{
  "online_matches": [
    {
      "id": 123,
      "name": "Jane Smith",
      "photo": "https://r2.yoryor.com/photos/123.jpg",
      "last_active_at": "2025-09-28T11:58:00Z"
    }
  ],
  "count": 3
}
```

---

#### Get Online Users in Chat
**Endpoint:** `GET /v1/presence/chats/{chatId}/online-users`

**Auth Required:** Yes

**Response:**
```json
{
  "online_users": [
    {
      "id": 123,
      "name": "Jane Smith",
      "status": "online"
    }
  ]
}
```

---

#### Update Typing Status
**Endpoint:** `POST /v1/presence/typing`

**Auth Required:** Yes

**Request:**
```json
{
  "chat_id": 1,
  "typing": true
}
```

**Response:**
```json
{
  "status": "success"
}
```

---

#### Get Typing Users
**Endpoint:** `GET /v1/presence/chats/{chatId}/typing-users`

**Auth Required:** Yes

**Response:**
```json
{
  "typing_users": [
    {
      "id": 123,
      "name": "Jane Smith"
    }
  ]
}
```

---

#### Heartbeat
**Endpoint:** `POST /v1/presence/heartbeat`

**Auth Required:** Yes

Keep-alive endpoint to maintain online status. Should be called every 30-60 seconds.

**Response:**
```json
{
  "status": "success",
  "next_heartbeat_in": 60
}
```

---

### Broadcasting Authentication
**Endpoint:** `POST /v1/broadcasting/auth`

**Auth Required:** Yes

Authenticate WebSocket connections for Laravel Reverb.

**Request:**
```json
{
  "socket_id": "123.456",
  "channel_name": "private-chat.1"
}
```

**Response:**
```json
{
  "auth": "authentication_signature",
  "channel_data": {
    "user_id": 1,
    "user_info": {
      "id": 1,
      "name": "John Doe"
    }
  }
}
```

---

### Search Users
**Endpoint:** `GET /v1/search`

**Auth Required:** Yes

**Query Parameters:**
- `q`: Search query (optional)
- `age_min`: Minimum age (optional)
- `age_max`: Maximum age (optional)
- `distance`: Maximum distance in km (optional)
- `gender`: Gender filter (optional)
- `interests`: Comma-separated interests (optional)
- `education`: Education level (optional)
- `religion`: Religion filter (optional)
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 20)

**Response:**
```json
{
  "results": [
    {
      "id": 123,
      "name": "Jane Smith",
      "age": 28,
      "distance": 5.2,
      "photos": [
        {
          "id": 1,
          "url": "https://r2.yoryor.com/photos/456.jpg",
          "is_primary": true
        }
      ],
      "bio": "Love to travel and meet new people",
      "interests": ["Travel", "Music"],
      "education": "Masters",
      "compatibility_score": 85
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50,
    "per_page": 20,
    "last_page": 3
  }
}
```

---

### Subscription Status
**Endpoint:** `GET /v1/subscription`

**Auth Required:** Yes

**Response:**
```json
{
  "current_plan": {
    "id": "premium",
    "name": "Premium",
    "price": 19.99,
    "currency": "USD",
    "interval": "month",
    "status": "active",
    "renewal_date": "2025-10-28T00:00:00Z",
    "auto_renew": true,
    "started_at": "2025-09-28T00:00:00Z"
  },
  "usage_stats": {
    "likes_used": 15,
    "likes_limit": -1,
    "super_likes_used": 2,
    "super_likes_limit": 5,
    "messages_sent": 125,
    "messages_limit": -1,
    "boosts_used": 0,
    "boosts_limit": 1
  },
  "available_plans": [
    {
      "id": "free",
      "name": "Free",
      "price": 0,
      "features": [
        "50 likes per month",
        "Limited messages",
        "Basic search"
      ]
    },
    {
      "id": "premium",
      "name": "Premium",
      "price": 19.99,
      "features": [
        "Unlimited likes",
        "Unlimited messages",
        "See who liked you",
        "Advanced filters"
      ]
    }
  ]
}
```

---

## Response Formats

### Success Response
```json
{
  "status": "success",
  "data": { /* response data */ },
  "message": "Operation completed successfully"
}
```

---

### Error Response
```json
{
  "status": "error",
  "message": "Error description",
  "errors": {
    "field": ["Validation error message"]
  },
  "code": "ERROR_CODE"
}
```

---

### Pagination
```json
{
  "data": [ /* array of items */ ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200,
    "from": 1,
    "to": 20,
    "has_more_pages": true
  },
  "links": {
    "first": "https://api.yoryor.com/v1/endpoint?page=1",
    "last": "https://api.yoryor.com/v1/endpoint?page=10",
    "prev": null,
    "next": "https://api.yoryor.com/v1/endpoint?page=2"
  }
}
```

---

### HTTP Status Codes

| Code | Description |
|------|-------------|
| `200` | OK - Successful request |
| `201` | Created - Resource created successfully |
| `204` | No Content - Successful request with no content |
| `400` | Bad Request - Invalid request data |
| `401` | Unauthorized - Authentication required |
| `403` | Forbidden - Insufficient permissions |
| `404` | Not Found - Resource not found |
| `422` | Unprocessable Entity - Validation failed |
| `429` | Too Many Requests - Rate limit exceeded |
| `500` | Internal Server Error - Server error |
| `503` | Service Unavailable - Service under maintenance |

---

### Error Codes

| Code | Description |
|------|-------------|
| `UNAUTHORIZED` | Invalid or missing authentication token |
| `VALIDATION_ERROR` | Request validation failed |
| `NOT_FOUND` | Resource not found |
| `FORBIDDEN` | Access denied |
| `RATE_LIMITED` | Too many requests |
| `SERVER_ERROR` | Internal server error |
| `MAINTENANCE` | Service under maintenance |
| `INVALID_CREDENTIALS` | Invalid email or password |
| `ACCOUNT_SUSPENDED` | Account has been suspended |
| `PAYMENT_REQUIRED` | Payment required for this action |

---

## WebSocket Events

YorYor uses Laravel Reverb for real-time WebSocket communication.

**Reverb Server:** `ws://localhost:8080` (development) / `wss://api.yoryor.com:8080` (production)

### Channel Types

#### Private Channels
```javascript
private-chat.{chat_id}
private-user.{user_id}
```

#### Presence Channels
```javascript
presence-online
presence-chat.{chat_id}
```

---

### Events

#### New Message
**Channel:** `private-chat.{chat_id}`

**Event:** `NewMessageEvent`

```json
{
  "message": {
    "id": 789,
    "chat_id": 1,
    "user_id": 123,
    "user": {
      "id": 123,
      "name": "Jane Smith"
    },
    "content": "Hello!",
    "type": "text",
    "sent_at": "2025-09-28T12:00:00Z",
    "read": false
  }
}
```

---

#### User Typing
**Channel:** `private-chat.{chat_id}`

**Event:** `UserTyping`

```json
{
  "user_id": 123,
  "user_name": "Jane Smith",
  "typing": true,
  "chat_id": 1
}
```

---

#### Match Created
**Channel:** `private-user.{user_id}`

**Event:** `MatchCreated`

```json
{
  "match": {
    "id": 456,
    "user": {
      "id": 123,
      "name": "Jane Smith",
      "age": 28,
      "photo": "https://r2.yoryor.com/photos/123.jpg"
    },
    "matched_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Call Initiated
**Channel:** `private-user.{user_id}`

**Event:** `CallInitiated`

```json
{
  "call": {
    "id": 789,
    "type": "video",
    "caller": {
      "id": 1,
      "name": "John Doe",
      "photo": "https://r2.yoryor.com/photos/1.jpg"
    },
    "meeting_id": "abc-def-ghi",
    "channel_name": "call_789",
    "initiated_at": "2025-09-28T12:00:00Z"
  }
}
```

---

#### Message Edited
**Channel:** `private-chat.{chat_id}`

**Event:** `MessageEdited`

```json
{
  "message_id": 789,
  "chat_id": 1,
  "content": "Updated message",
  "edited_at": "2025-09-28T12:05:00Z"
}
```

---

#### Message Deleted
**Channel:** `private-chat.{chat_id}`

**Event:** `MessageDeleted`

```json
{
  "message_id": 789,
  "chat_id": 1,
  "deleted_at": "2025-09-28T12:05:00Z"
}
```

---

#### Message Read
**Channel:** `private-chat.{chat_id}`

**Event:** `MessageRead`

```json
{
  "message_ids": [789, 790],
  "chat_id": 1,
  "read_by_user_id": 123,
  "read_at": "2025-09-28T12:05:00Z"
}
```

---

### Connecting to WebSocket

**JavaScript Example:**
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.VITE_REVERB_APP_KEY,
    wsHost: process.env.VITE_REVERB_HOST,
    wsPort: process.env.VITE_REVERB_PORT,
    wssPort: process.env.VITE_REVERB_PORT,
    forceTLS: process.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/api/v1/broadcasting/auth',
    auth: {
        headers: {
            Authorization: `Bearer ${token}`
        }
    }
});

// Listen to private chat channel
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        console.log('New message:', e.message);
    })
    .listen('UserTyping', (e) => {
        console.log('User typing:', e.user_name);
    });

// Listen to user channel
Echo.private(`user.${userId}`)
    .listen('MatchCreated', (e) => {
        console.log('New match:', e.match);
    })
    .listen('CallInitiated', (e) => {
        console.log('Incoming call:', e.call);
    });
```

---

## Best Practices

### Authentication
1. **Always include the Authorization header** for protected endpoints
2. **Store tokens securely** - use secure storage (Keychain/KeyStore), never localStorage
3. **Refresh tokens** on app launch if expired
4. **Handle 401 errors** by redirecting to login

### Rate Limiting
1. **Respect rate limits** to avoid temporary bans
2. **Implement exponential backoff** for retries
3. **Cache responses** where appropriate to reduce API calls
4. **Monitor rate limit headers** (X-RateLimit-Remaining)

### Error Handling
1. **Handle errors gracefully** and display user-friendly messages
2. **Log errors** for debugging purposes
3. **Retry failed requests** with exponential backoff
4. **Validate data** before sending requests

### Performance
1. **Use pagination** for list endpoints
2. **Implement lazy loading** for images and media
3. **Cache static data** (countries, FAQ, etc.)
4. **Compress images** before uploading
5. **Use WebSockets** for real-time features instead of polling

### Security
1. **Use HTTPS** in production
2. **Validate all user input** on the client side
3. **Never log sensitive data** (passwords, tokens)
4. **Implement certificate pinning** for mobile apps
5. **Use secure storage** for tokens and sensitive data

### Media Uploads
1. **Compress images** before uploading (max 10MB)
2. **Show upload progress** to users
3. **Validate file types** before uploading
4. **Handle upload failures** with retry logic
5. **Use thumbnails** for image lists

### WebSocket Connections
1. **Subscribe to channels** only when needed
2. **Unsubscribe** when leaving screens
3. **Handle disconnections** gracefully with reconnection logic
4. **Authenticate connections** properly
5. **Use heartbeat** endpoint to maintain presence

### Mobile-Specific
1. **Handle network changes** (WiFi to cellular)
2. **Implement offline mode** where possible
3. **Optimize for battery life** (reduce polling frequency)
4. **Handle background/foreground** state changes
5. **Request permissions** appropriately (location, notifications)

---

## SDK Examples

### JavaScript/Node.js
```javascript
import axios from 'axios';

const api = axios.create({
    baseURL: 'https://api.yoryor.com/api/v1',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Get user profile
const getProfile = async () => {
    try {
        const response = await api.get('/profile/me');
        return response.data;
    } catch (error) {
        console.error('Error fetching profile:', error);
        throw error;
    }
};

// Send message
const sendMessage = async (chatId, content) => {
    try {
        const response = await api.post(`/chats/${chatId}/messages`, {
            content,
            type: 'text'
        });
        return response.data;
    } catch (error) {
        console.error('Error sending message:', error);
        throw error;
    }
};

// Upload photo
const uploadPhoto = async (file) => {
    const formData = new FormData();
    formData.append('photo', file);
    formData.append('is_primary', false);

    try {
        const response = await api.post('/photos/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        return response.data;
    } catch (error) {
        console.error('Error uploading photo:', error);
        throw error;
    }
};
```

---

### PHP (Laravel)
```php
use Illuminate\Support\Facades\Http;

$token = 'your-bearer-token';
$baseUrl = 'https://api.yoryor.com/api/v1';

// Get user profile
$response = Http::withToken($token)
    ->get("{$baseUrl}/profile/me");

if ($response->successful()) {
    $profile = $response->json();
}

// Send message
$response = Http::withToken($token)
    ->post("{$baseUrl}/chats/1/messages", [
        'content' => 'Hello!',
        'type' => 'text'
    ]);

// Upload photo
$response = Http::withToken($token)
    ->attach('photo', file_get_contents($filePath), 'photo.jpg')
    ->post("{$baseUrl}/photos/upload", [
        'is_primary' => false
    ]);
```

---

### React Native (Expo)
```javascript
import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

const API_BASE_URL = 'https://api.yoryor.com/api/v1';

// Create API instance
const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
});

// Add auth token to requests
api.interceptors.request.use(async (config) => {
    const token = await SecureStore.getItemAsync('auth_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

// Handle errors
api.interceptors.response.use(
    response => response,
    async error => {
        if (error.response?.status === 401) {
            // Token expired, redirect to login
            await SecureStore.deleteItemAsync('auth_token');
            // Navigate to login screen
        }
        return Promise.reject(error);
    }
);

// API functions
export const profileAPI = {
    getProfile: () => api.get('/profile/me'),
    updateProfile: (data) => api.put('/profile/1', data),
    uploadPhoto: (file) => {
        const formData = new FormData();
        formData.append('photo', {
            uri: file.uri,
            type: 'image/jpeg',
            name: 'photo.jpg'
        });
        return api.post('/photos/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    }
};

export const chatAPI = {
    getChats: () => api.get('/chats'),
    getChat: (chatId) => api.get(`/chats/${chatId}`),
    sendMessage: (chatId, content) =>
        api.post(`/chats/${chatId}/messages`, {
            content,
            type: 'text'
        }),
    markAsRead: (chatId, messageIds) =>
        api.post(`/chats/${chatId}/read`, { message_ids: messageIds })
};
```

---

## Changelog

### Version 1.0.0 (September 2025)
- Initial API release
- User authentication with Laravel Sanctum
- Profile management (basic, cultural, family, career, physical)
- Discovery and matching system
- Real-time chat and messaging with Laravel Reverb
- Video calling (VideoSDK.live and Agora RTC)
- Stories (24-hour ephemeral content)
- Safety features (panic button, emergency contacts)
- Verification system (5 verification types)
- Matchmaker system
- Comprehensive rate limiting
- Push notifications via Expo
- Analytics dashboard
- Admin endpoints

---

## Support

For API support and questions:
- **Email:** api-support@yoryor.com
- **Documentation:** https://docs.yoryor.com
- **Status Page:** https://status.yoryor.com

**API Version:** 1.0.0

**Last Updated:** October 2025

**Technology Stack:**
- Laravel 12
- PHP 8.2+
- Laravel Reverb (WebSocket)
- Cloudflare R2 (Storage)
- VideoSDK.live / Agora RTC (Video Calling)
- Expo Push Notifications

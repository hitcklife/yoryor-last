# YorYor API Documentation

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
- [Response Formats](#response-formats)

## Overview

Base URL: `https://api.yoryor.com/api/v1`

All API endpoints are RESTful and return JSON responses. The API uses Laravel Sanctum for authentication via bearer tokens.

### Headers

```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
X-CSRF-TOKEN: {csrf_token} (for web requests)
```

## Authentication

YorYor uses Laravel Sanctum for API authentication. After successful login, clients receive a bearer token to authenticate subsequent requests.

### Token Usage
```http
Authorization: Bearer {your-token-here}
```

## Rate Limiting

The API implements multiple rate limiting strategies:

- **Authentication**: 5 attempts per minute
- **Profile Updates**: 10 requests per minute
- **Like/Match Actions**: 20 requests per minute
- **Chat Messages**: 60 messages per minute
- **Video Calls**: 10 initiations per hour
- **Block/Report**: 10 actions per hour
- **Standard**: 60 requests per minute

Rate limit headers are included in responses:
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
```

---

## Authentication Endpoints

### Check Email
Check if an email is registered and get authentication method.

**Endpoint:** `POST /v1/auth/check-email`

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

### Authenticate
Login or register using email/password or OTP.

**Endpoint:** `POST /v1/auth/authenticate`

**Rate Limit:** 5 attempts/minute

**Request (Password):**
```json
{
  "email": "user@example.com",
  "password": "SecurePassword123"
}
```

**Request (OTP):**
```json
{
  "email": "user@example.com",
  "otp": "123456"
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
    "profile_completed": true
  }
}
```

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
  "country_id": 1
}
```

**Response:**
```json
{
  "status": "success",
  "user": { /* user data */ },
  "profile": { /* profile data */ }
}
```

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

### Two-Factor Authentication

#### Enable 2FA
**Endpoint:** `POST /v1/auth/2fa/enable`

**Response:**
```json
{
  "qr_code": "data:image/svg+xml;base64,...",
  "secret": "JBSWY3DPEHPK3PXP",
  "backup_codes": ["code1", "code2", ...]
}
```

#### Disable 2FA
**Endpoint:** `POST /v1/auth/2fa/disable`

#### Verify 2FA Code
**Endpoint:** `POST /v1/auth/2fa/verify`

**Request:**
```json
{
  "code": "123456"
}
```

---

## Profile Management

### Get My Profile
Retrieve authenticated user's profile.

**Endpoint:** `GET /v1/profile/me`

**Response:**
```json
{
  "id": 1,
  "uuid": "550e8400-e29b-41d4-a716-446655440000",
  "name": "John Doe",
  "bio": "Looking for meaningful connection",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "country": {
    "id": 1,
    "name": "United States"
  },
  "photos": [
    {
      "id": 1,
      "url": "https://...",
      "is_primary": true,
      "order": 1
    }
  ],
  "profile_completion": 85
}
```

### Get Profile Completion Status
**Endpoint:** `GET /v1/profile/completion-status`

**Response:**
```json
{
  "completion_percentage": 85,
  "completed_sections": ["basic_info", "photos", "preferences"],
  "incomplete_sections": ["cultural_profile", "career_profile"]
}
```

### Update Profile
**Endpoint:** `PUT /v1/profile/{profile_id}`

**Request:**
```json
{
  "bio": "Updated bio",
  "looking_for": "serious_relationship",
  "education": "bachelor",
  "occupation": "Software Engineer"
}
```

### Get User Profile (View Others)
**Endpoint:** `GET /v1/users/{userId}/profile`

**Response:** Returns public profile information

### Cultural Profile

#### Get Cultural Profile
**Endpoint:** `GET /v1/profile/cultural`

#### Update Cultural Profile
**Endpoint:** `PUT /v1/profile/cultural`

**Request:**
```json
{
  "religion": "muslim",
  "religiosity": "moderate",
  "sect": "sunni",
  "prayer_frequency": "five_times_daily",
  "dietary_preferences": "halal_only",
  "languages": ["English", "Arabic"]
}
```

### Family Preferences

#### Get Family Preferences
**Endpoint:** `GET /v1/profile/family-preferences`

#### Update Family Preferences
**Endpoint:** `PUT /v1/profile/family-preferences`

**Request:**
```json
{
  "marital_status": "never_married",
  "wants_children": true,
  "number_of_children": 0,
  "living_situation": "own_place",
  "family_involvement": "involved"
}
```

### Location Preferences

#### Get Location Preferences
**Endpoint:** `GET /v1/profile/location-preferences`

#### Update Location Preferences
**Endpoint:** `PUT /v1/profile/location-preferences`

**Request:**
```json
{
  "current_city": "New York",
  "willing_to_relocate": true,
  "preferred_countries": [1, 2, 3],
  "distance_preference": 50
}
```

### Career Profile

#### Get Career Profile
**Endpoint:** `GET /v1/profile/career`

#### Update Career Profile
**Endpoint:** `PUT /v1/profile/career`

**Request:**
```json
{
  "education_level": "masters",
  "field_of_study": "Computer Science",
  "occupation": "Software Engineer",
  "income_level": "comfortable",
  "career_goals": "Startup founder"
}
```

### Physical Profile

#### Get Physical Profile
**Endpoint:** `GET /v1/profile/physical`

#### Update Physical Profile
**Endpoint:** `PUT /v1/profile/physical`

**Request:**
```json
{
  "height": 175,
  "body_type": "athletic",
  "ethnicity": "Asian",
  "hair_color": "black",
  "eye_color": "brown"
}
```

### Comprehensive Profile

#### Get All Profile Data
**Endpoint:** `GET /v1/profile/comprehensive`

Returns all profile sections in one response.

#### Update All Profile Data
**Endpoint:** `PUT /v1/profile/comprehensive`

**Rate Limit:** 10 requests/minute

Update multiple profile sections at once.

### Photos

#### Get Photos
**Endpoint:** `GET /v1/photos`

#### Upload Photo
**Endpoint:** `POST /v1/photos/upload`

**Request:** multipart/form-data
```
photo: (file)
is_primary: true
order: 1
```

#### Update Photo
**Endpoint:** `PUT /v1/photos/{id}`

#### Delete Photo
**Endpoint:** `DELETE /v1/photos/{id}`

### Block User
**Endpoint:** `POST /v1/users/{userId}/block`

**Rate Limit:** 10 actions/hour

### Report User
**Endpoint:** `POST /v1/users/{userId}/report`

**Rate Limit:** 10 actions/hour

**Request:**
```json
{
  "reason": "inappropriate_behavior",
  "details": "Description of the issue",
  "evidence": ["screenshot1.jpg"]
}
```

### Get Report Reasons
**Endpoint:** `GET /v1/report-reasons`

---

## User Discovery & Matching

### Get Discovery Profiles
Get profiles for the discovery/swipe interface.

**Endpoint:** `POST /v1/discovery-profiles`

**Request:**
```json
{
  "limit": 10,
  "filters": {
    "age_min": 25,
    "age_max": 35,
    "distance": 50
  }
}
```

**Response:**
```json
{
  "profiles": [
    {
      "id": 123,
      "uuid": "...",
      "name": "Jane",
      "age": 28,
      "photos": [...],
      "distance": 15.5,
      "compatibility_score": 85
    }
  ]
}
```

### Get Potential Matches
**Endpoint:** `GET /v1/matches/potential`

**Rate Limit:** Match discovery limit

**Query Parameters:**
- `page`: Page number
- `limit`: Results per page (default: 20)

### Get Matches
Get list of mutual matches.

**Endpoint:** `GET /v1/matches`

**Response:**
```json
{
  "matches": [
    {
      "id": 1,
      "user": { /* user data */ },
      "matched_at": "2025-09-28T10:30:00Z",
      "has_chat": true,
      "last_message": "Hi there!"
    }
  ]
}
```

### Like User
**Endpoint:** `POST /v1/likes`

**Rate Limit:** 20 actions/minute

**Request:**
```json
{
  "user_id": 123
}
```

**Response:**
```json
{
  "status": "success",
  "matched": true,
  "match_id": 456
}
```

### Pass on User
**Endpoint:** `POST /v1/profiles/{user}/pass`

**Rate Limit:** 20 actions/minute

### Get Received Likes
**Endpoint:** `GET /v1/likes/received`

### Get Sent Likes
**Endpoint:** `GET /v1/likes/sent`

### Delete Match
**Endpoint:** `DELETE /v1/matches/{id}`

**Rate Limit:** 20 actions/minute

---

## Chat & Messaging

### Get Chats
Retrieve all chat conversations.

**Endpoint:** `GET /v1/chats`

**Query Parameters:**
- `page`: Page number
- `per_page`: Results per page

**Response:**
```json
{
  "chats": [
    {
      "id": 1,
      "match_id": 456,
      "user": { /* other user data */ },
      "last_message": {
        "id": 789,
        "content": "Hello!",
        "sent_at": "2025-09-28T12:00:00Z",
        "read": false
      },
      "unread_count": 3
    }
  ]
}
```

### Get Unread Count
**Endpoint:** `GET /v1/chats/unread-count`

**Response:**
```json
{
  "unread_count": 5
}
```

### Get Single Chat
**Endpoint:** `GET /v1/chats/{id}`

**Response:**
```json
{
  "id": 1,
  "match_id": 456,
  "users": [...],
  "messages": [
    {
      "id": 1,
      "user_id": 123,
      "content": "Hi!",
      "type": "text",
      "sent_at": "2025-09-28T12:00:00Z",
      "read": true,
      "read_at": "2025-09-28T12:01:00Z"
    }
  ]
}
```

### Create or Get Chat
**Endpoint:** `POST /v1/chats/create`

**Rate Limit:** Create chat limit

**Request:**
```json
{
  "match_id": 456
}
```

### Send Message
**Endpoint:** `POST /v1/chats/{id}/messages`

**Rate Limit:** 60 messages/minute

**Request:**
```json
{
  "content": "Hello there!",
  "type": "text"
}
```

**For media messages:**
```json
{
  "type": "image",
  "media_url": "https://...",
  "content": "Check this out"
}
```

**Response:**
```json
{
  "id": 789,
  "chat_id": 1,
  "user_id": 123,
  "content": "Hello there!",
  "type": "text",
  "sent_at": "2025-09-28T12:00:00Z"
}
```

### Mark Messages as Read
**Endpoint:** `POST /v1/chats/{id}/read`

**Rate Limit:** Mark read limit

**Request:**
```json
{
  "message_ids": [1, 2, 3]
}
```

### Edit Message
**Endpoint:** `PUT /v1/chats/{chat_id}/messages/{message_id}`

**Rate Limit:** Edit message limit

**Request:**
```json
{
  "content": "Updated message"
}
```

### Delete Message
**Endpoint:** `DELETE /v1/chats/{chat_id}/messages/{message_id}`

**Rate Limit:** Delete message limit

### Delete Chat
**Endpoint:** `DELETE /v1/chats/{id}`

### Get Call Messages
**Endpoint:** `GET /v1/chats/{id}/call-messages`

### Get Call Statistics
**Endpoint:** `GET /v1/chats/{id}/call-statistics`

---

## Video Calling

### Agora (Alternative Provider)

#### Generate Token
**Endpoint:** `POST /v1/agora/token`

**Rate Limit:** 10 calls/hour

**Request:**
```json
{
  "channel_name": "call_123",
  "uid": 12345
}
```

#### Initiate Call
**Endpoint:** `POST /v1/agora/initiate`

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
  "token": "agora_token_here"
}
```

#### Join Call
**Endpoint:** `POST /v1/agora/{callId}/join`

#### End Call
**Endpoint:** `POST /v1/agora/{callId}/end`

#### Reject Call
**Endpoint:** `POST /v1/agora/{callId}/reject`

#### Get Call History
**Endpoint:** `GET /v1/agora/history`

### VideoSDK (Primary Provider)

#### Get Token
**Endpoint:** `POST /v1/video-call/token`

**Rate Limit:** 10 calls/hour

**Response:**
```json
{
  "token": "videosdk_token_here"
}
```

#### Create Meeting
**Endpoint:** `POST /v1/video-call/create-meeting`

**Response:**
```json
{
  "meeting_id": "abc-def-ghi",
  "token": "meeting_token_here"
}
```

#### Validate Meeting
**Endpoint:** `GET /v1/video-call/validate-meeting/{meetingId}`

#### Initiate Call
**Endpoint:** `POST /v1/video-call/initiate`

**Request:**
```json
{
  "receiver_id": 456,
  "type": "video",
  "meeting_id": "abc-def-ghi"
}
```

#### Join Call
**Endpoint:** `POST /v1/video-call/{callId}/join`

#### End Call
**Endpoint:** `POST /v1/video-call/{callId}/end`

**Request:**
```json
{
  "duration": 300,
  "end_reason": "completed"
}
```

#### Reject Call
**Endpoint:** `POST /v1/video-call/{callId}/reject`

#### Handle Missed Call
**Endpoint:** `POST /v1/video-call/{callId}/missed`

#### Get Call History
**Endpoint:** `GET /v1/video-call/history`

**Query Parameters:**
- `page`: Page number
- `type`: Filter by call type (video/audio)

#### Get Call Analytics
**Endpoint:** `GET /v1/video-call/analytics`

---

## Stories

### Get User Stories
Get stories posted by authenticated user.

**Endpoint:** `GET /v1/stories`

**Response:**
```json
{
  "stories": [
    {
      "id": 1,
      "media_url": "https://...",
      "media_type": "image",
      "created_at": "2025-09-28T10:00:00Z",
      "expires_at": "2025-09-29T10:00:00Z",
      "views_count": 15
    }
  ]
}
```

### Get Matched Users Stories
Get stories from matched users.

**Endpoint:** `GET /v1/stories/matches`

**Response:**
```json
{
  "users": [
    {
      "id": 123,
      "name": "Jane",
      "photo": "https://...",
      "stories": [
        {
          "id": 1,
          "media_url": "https://...",
          "created_at": "2025-09-28T10:00:00Z",
          "viewed": false
        }
      ],
      "unviewed_count": 2
    }
  ]
}
```

### Create Story
**Endpoint:** `POST /v1/stories`

**Rate Limit:** Story action limit

**Request:** multipart/form-data
```
media: (file)
type: image|video
```

**Response:**
```json
{
  "id": 1,
  "media_url": "https://...",
  "expires_at": "2025-09-29T10:00:00Z"
}
```

### Delete Story
**Endpoint:** `DELETE /v1/stories/{id}`

**Rate Limit:** Story action limit

---

## Settings & Account

### Get All Settings
**Endpoint:** `GET /v1/settings`

**Response:**
```json
{
  "notifications": { /* notification settings */ },
  "privacy": { /* privacy settings */ },
  "discovery": { /* discovery settings */ },
  "security": { /* security settings */ }
}
```

### Update Settings
**Endpoint:** `PUT /v1/settings`

### Notification Settings

#### Get Notification Settings
**Endpoint:** `GET /v1/settings/notifications`

#### Update Notification Settings
**Endpoint:** `PUT /v1/settings/notifications`

**Request:**
```json
{
  "push_notifications": true,
  "email_notifications": false,
  "new_matches": true,
  "messages": true,
  "likes": false
}
```

### Privacy Settings

#### Get Privacy Settings
**Endpoint:** `GET /v1/settings/privacy`

#### Update Privacy Settings
**Endpoint:** `PUT /v1/settings/privacy`

**Request:**
```json
{
  "show_online_status": true,
  "show_distance": true,
  "show_last_active": false,
  "profile_visibility": "everyone"
}
```

### Discovery Settings

#### Get Discovery Settings
**Endpoint:** `GET /v1/settings/discovery`

#### Update Discovery Settings
**Endpoint:** `PUT /v1/settings/discovery`

**Request:**
```json
{
  "age_min": 25,
  "age_max": 35,
  "distance": 50,
  "show_me": "everyone"
}
```

### Security Settings

#### Get Security Settings
**Endpoint:** `GET /v1/settings/security`

#### Update Security Settings
**Endpoint:** `PUT /v1/settings/security`

### Account Management

#### Change Password
**Endpoint:** `PUT /v1/account/password`

**Rate Limit:** Password change limit

**Request:**
```json
{
  "current_password": "OldPassword123",
  "new_password": "NewPassword123",
  "new_password_confirmation": "NewPassword123"
}
```

#### Change Email
**Endpoint:** `PUT /v1/account/email`

**Rate Limit:** Email change limit

**Request:**
```json
{
  "email": "newemail@example.com",
  "password": "CurrentPassword123"
}
```

#### Delete Account
**Endpoint:** `DELETE /v1/account`

**Rate Limit:** Account deletion limit

**Request:**
```json
{
  "password": "CurrentPassword123",
  "reason": "Found someone"
}
```

#### Request Data Export
**Endpoint:** `POST /v1/account/export-data`

**Rate Limit:** Data export limit

**Response:**
```json
{
  "message": "Export request received",
  "estimated_completion": "2025-09-30T12:00:00Z"
}
```

### Blocked Users

#### Get Blocked Users
**Endpoint:** `GET /v1/blocked-users`

#### Block User
**Endpoint:** `POST /v1/blocked-users`

**Rate Limit:** 10 actions/hour

**Request:**
```json
{
  "user_id": 123
}
```

#### Unblock User
**Endpoint:** `DELETE /v1/blocked-users/{userId}`

**Rate Limit:** 10 actions/hour

---

## Safety & Emergency

### Panic Button

#### Activate Panic
**Endpoint:** `POST /v1/safety/panic/activate`

**Rate Limit:** Panic activation limit

**Request:**
```json
{
  "location": {
    "latitude": 40.7128,
    "longitude": -74.0060
  },
  "notes": "Emergency details"
}
```

**Response:**
```json
{
  "panic_id": 123,
  "status": "active",
  "contacts_notified": 3
}
```

#### Cancel Panic
**Endpoint:** `POST /v1/safety/panic/cancel`

**Request:**
```json
{
  "panic_id": 123
}
```

#### Get Panic Status
**Endpoint:** `GET /v1/safety/panic/status`

#### Get Panic History
**Endpoint:** `GET /v1/safety/panic/history`

### Safety Setup

#### Setup Safety Features
**Endpoint:** `POST /v1/safety/setup`

**Request:**
```json
{
  "emergency_contacts": [
    {
      "name": "Mom",
      "phone": "+1234567890",
      "relationship": "parent"
    }
  ]
}
```

#### Test Emergency System
**Endpoint:** `POST /v1/safety/test`

### Emergency Contacts

#### Get Emergency Contacts
**Endpoint:** `GET /v1/safety/emergency-contacts`

**Response:**
```json
{
  "contacts": [
    {
      "id": 1,
      "name": "Mom",
      "phone": "+1234567890",
      "relationship": "parent",
      "verified": true
    }
  ]
}
```

#### Add Emergency Contact
**Endpoint:** `POST /v1/safety/emergency-contacts`

**Rate Limit:** Sensitive action limit

**Request:**
```json
{
  "name": "Mom",
  "phone": "+1234567890",
  "email": "mom@example.com",
  "relationship": "parent"
}
```

#### Update Emergency Contact
**Endpoint:** `PUT /v1/safety/emergency-contacts/{contact}`

#### Delete Emergency Contact
**Endpoint:** `DELETE /v1/safety/emergency-contacts/{contact}`

#### Verify Emergency Contact
**Endpoint:** `POST /v1/safety/emergency-contacts/{contact}/verify`

**Request:**
```json
{
  "verification_code": "123456"
}
```

#### Resend Verification Code
**Endpoint:** `POST /v1/safety/emergency-contacts/{contact}/resend-code`

### Safety Tips
**Endpoint:** `GET /v1/safety/tips`

### Admin Safety Routes

#### Get All Panics
**Endpoint:** `GET /v1/safety/admin/panics`

**Auth Required:** Admin role

#### Resolve Panic
**Endpoint:** `POST /v1/safety/admin/panics/{panic}/resolve`

**Auth Required:** Admin role

---

## Matchmaker System

### Browse Matchmakers
**Endpoint:** `GET /v1/matchmakers`

**Query Parameters:**
- `specialty`: Filter by specialty
- `min_rating`: Minimum rating
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
      "successful_matches": 150,
      "hourly_rate": 100
    }
  ]
}
```

### Get Matchmaker Details
**Endpoint:** `GET /v1/matchmakers/{matchmaker}`

### Hire Matchmaker
**Endpoint:** `POST /v1/matchmakers/{matchmaker}/hire`

**Request:**
```json
{
  "service_id": 1,
  "message": "I'm interested in your services"
}
```

### Leave Review
**Endpoint:** `POST /v1/matchmakers/{matchmaker}/review`

**Request:**
```json
{
  "rating": 5,
  "comment": "Excellent service!"
}
```

### Register as Matchmaker
**Endpoint:** `POST /v1/matchmakers/register`

**Request:**
```json
{
  "bio": "Professional matchmaker...",
  "specialty": "muslim_matchmaking",
  "certifications": ["ICC Certified"],
  "years_experience": 10,
  "hourly_rate": 100
}
```

### My Interactions
**Endpoint:** `GET /v1/matchmakers/my/interactions`

### Respond to Introduction
**Endpoint:** `POST /v1/matchmakers/introductions/{introduction}/respond`

**Request:**
```json
{
  "response": "accept|decline",
  "message": "Looking forward to connecting"
}
```

### Matchmaker Dashboard (Matchmakers Only)

#### Get Dashboard
**Endpoint:** `GET /v1/matchmakers/dashboard`

#### Get Clients
**Endpoint:** `GET /v1/matchmakers/clients`

#### Create Introduction
**Endpoint:** `POST /v1/matchmakers/introductions`

**Request:**
```json
{
  "client_a_id": 123,
  "client_b_id": 456,
  "introduction_message": "I think you two would be great together"
}
```

#### Update Matchmaker Profile
**Endpoint:** `PUT /v1/matchmakers/profile`

---

## Verification System

### Get Verification Status
**Endpoint:** `GET /v1/verification/status`

**Response:**
```json
{
  "verified": true,
  "verification_type": "identity",
  "verified_at": "2025-09-28T10:00:00Z",
  "badges": ["identity_verified", "photo_verified"]
}
```

### Get Verification Requirements
**Endpoint:** `GET /v1/verification/requirements/{type}`

**Types:** `identity`, `photo`, `income`, `education`, `profession`

**Response:**
```json
{
  "type": "identity",
  "requirements": [
    "Government-issued ID",
    "Clear selfie",
    "Proof of address"
  ],
  "estimated_time": "24-48 hours"
}
```

### Submit Verification Request
**Endpoint:** `POST /v1/verification/submit`

**Rate Limit:** Verification submit limit

**Request:** multipart/form-data
```
type: identity
documents: (files)
notes: Additional information
```

**Response:**
```json
{
  "request_id": 123,
  "status": "pending",
  "submitted_at": "2025-09-28T10:00:00Z"
}
```

### Get Verification Requests
**Endpoint:** `GET /v1/verification/requests`

### Get Verification Request
**Endpoint:** `GET /v1/verification/requests/{verificationRequest}`

### Admin Verification Routes

#### Get Pending Requests
**Endpoint:** `GET /v1/verification/admin/pending`

**Auth Required:** Admin role

#### Approve Request
**Endpoint:** `POST /v1/verification/admin/{verificationRequest}/approve`

**Auth Required:** Admin role

**Request:**
```json
{
  "notes": "Documents verified successfully"
}
```

#### Reject Request
**Endpoint:** `POST /v1/verification/admin/{verificationRequest}/reject`

**Auth Required:** Admin role

**Request:**
```json
{
  "reason": "Documents unclear",
  "notes": "Please resubmit with clearer photos"
}
```

---

## Support & Feedback

### Submit Feedback
**Endpoint:** `POST /v1/support/feedback`

**Request:**
```json
{
  "type": "feature_request|bug|general",
  "subject": "Feedback subject",
  "message": "Detailed feedback",
  "rating": 5
}
```

### Report User
**Endpoint:** `POST /v1/support/report`

**Request:**
```json
{
  "reported_user_id": 123,
  "category": "inappropriate_behavior",
  "description": "Details of the issue",
  "evidence_urls": ["https://..."]
}
```

### Get FAQ
**Endpoint:** `GET /v1/support/faq`

---

## Additional Endpoints

### Get Countries
**Endpoint:** `GET /v1/countries`

**Response:**
```json
{
  "countries": [
    {
      "id": 1,
      "name": "United States",
      "code": "US",
      "phone_code": "+1"
    }
  ]
}
```

### Update Location
**Endpoint:** `POST /v1/location/update`

**Rate Limit:** Location update limit

**Request:**
```json
{
  "latitude": 40.7128,
  "longitude": -74.0060
}
```

### Device Tokens (Push Notifications)

#### Register Device Token
**Endpoint:** `POST /v1/device-tokens`

**Request:**
```json
{
  "token": "expo_push_token_here",
  "device_type": "ios|android",
  "device_name": "iPhone 12"
}
```

#### Delete Device Token
**Endpoint:** `DELETE /v1/device-tokens`

**Request:**
```json
{
  "token": "expo_push_token_here"
}
```

### Presence & Online Status

#### Get Online Status
**Endpoint:** `GET /v1/presence/status`

#### Update Online Status
**Endpoint:** `POST /v1/presence/status`

**Request:**
```json
{
  "status": "online|away|offline"
}
```

#### Get Online Users
**Endpoint:** `GET /v1/presence/online-users`

#### Get Online Matches
**Endpoint:** `GET /v1/presence/online-matches`

#### Get Online Users in Chat
**Endpoint:** `GET /v1/presence/chats/{chatId}/online-users`

#### Update Typing Status
**Endpoint:** `POST /v1/presence/typing`

**Request:**
```json
{
  "chat_id": 1,
  "typing": true
}
```

#### Get Typing Users
**Endpoint:** `GET /v1/presence/chats/{chatId}/typing-users`

#### Heartbeat
**Endpoint:** `POST /v1/presence/heartbeat`

Keep-alive endpoint to maintain online status.

### Broadcasting Authentication
**Endpoint:** `POST /v1/broadcasting/auth`

**Auth Required:** Yes

Authenticate WebSocket connections.

### Home Stats
**Endpoint:** `GET /v1/auth/home-stats`

**Auth Required:** Yes

**Response:**
```json
{
  "new_likes": 5,
  "new_matches": 2,
  "unread_messages": 10,
  "profile_views": 50
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

### Pagination
```json
{
  "data": [ /* array of items */ ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 20,
    "total": 200
  },
  "links": {
    "first": "https://api.yoryor.com/v1/endpoint?page=1",
    "last": "https://api.yoryor.com/v1/endpoint?page=10",
    "prev": null,
    "next": "https://api.yoryor.com/v1/endpoint?page=2"
  }
}
```

### HTTP Status Codes

- `200 OK`: Successful request
- `201 Created`: Resource created successfully
- `204 No Content`: Successful request with no content
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation failed
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

---

## WebSocket Events

YorYor uses Laravel Reverb for real-time WebSocket communication.

### Channel Types

**Private Channels:** `private-chat.{chat_id}`
**Presence Channels:** `presence-online`
**User Channels:** `private-user.{user_id}`

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
    "content": "Hello!",
    "type": "text",
    "sent_at": "2025-09-28T12:00:00Z"
  }
}
```

#### User Typing
**Channel:** `private-chat.{chat_id}`
**Event:** `UserTyping`

```json
{
  "user_id": 123,
  "typing": true
}
```

#### Match Created
**Channel:** `private-user.{user_id}`
**Event:** `MatchCreated`

```json
{
  "match": {
    "id": 456,
    "user": { /* matched user data */ }
  }
}
```

#### Call Initiated
**Channel:** `private-user.{user_id}`
**Event:** `CallInitiated`

```json
{
  "call": {
    "id": 789,
    "caller": { /* caller data */ },
    "type": "video",
    "channel_name": "call_789"
  }
}
```

---

## Best Practices

1. **Always include the Authorization header** for protected endpoints
2. **Respect rate limits** to avoid temporary bans
3. **Handle errors gracefully** and display user-friendly messages
4. **Use pagination** for list endpoints
5. **Cache responses** where appropriate
6. **Validate data** before sending requests
7. **Use HTTPS** in production
8. **Store tokens securely** (never in localStorage for web apps)
9. **Implement exponential backoff** for retries
10. **Subscribe to WebSocket events** for real-time updates

---

## Support

For API support, contact: api-support@yoryor.com

API Version: 1.0
Last Updated: September 2025
# YorYor API Routes Documentation

This document provides a comprehensive list of all API endpoints available in the YorYor dating application.

## Authentication

### Endpoints

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| POST | `/api/v1/auth/authenticate` | Send OTP and/or verify OTP for authentication | No |
| POST | `/api/v1/auth/logout` | Logout the authenticated user | Yes |
| POST | `/api/v1/auth/complete-registration` | Complete registration for OTP users | Yes |

### Simplified Authentication Flow

The `/api/v1/auth/authenticate` endpoint provides a simplified, password-less authentication flow for mobile applications:

1. **Sending OTP**: When called with only a phone number, it generates and sends an OTP to the user.
2. **Verifying OTP**: When called with both phone number and OTP, it verifies the OTP and authenticates the user.
3. **Registration Status**: The response includes a `registration_completed` flag indicating whether the user needs to complete their profile.
4. **User Data**: If registration is complete, the response includes user information, profile details, photos, and likes.

This single endpoint handles both login and registration through phone number and OTP verification, eliminating the need for passwords entirely. The mobile app can determine whether the user needs to register or log in based on the `registration_completed` flag in the response.

Example request for sending OTP:
```json
{
  "phone": "+1234567890"
}
```

Example request for verifying OTP:
```json
{
  "phone": "+1234567890",
  "otp": "123456"
}
```

### Two-Factor Authentication

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| POST | `/api/v1/auth/2fa/enable` | Enable two-factor authentication | Yes |
| POST | `/api/v1/auth/2fa/disable` | Disable two-factor authentication | Yes |
| POST | `/api/v1/auth/2fa/verify` | Verify a two-factor authentication code | Yes |

## Profile Management

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/profile/me` | Get the authenticated user's profile | Yes |
| PUT | `/api/v1/profile/{profile}` | Update a profile | Yes |

## Photo Management

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/photos` | Get all photos for the authenticated user | Yes |
| POST | `/api/v1/photos/upload` | Upload a new photo | Yes |
| DELETE | `/api/v1/photos/{id}` | Delete a photo | Yes |

## Matches

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/matches/potential` | Get potential matches based on preferences | Yes |
| GET | `/api/v1/matches` | Get all matches for the authenticated user | Yes |
| POST | `/api/v1/matches` | Create a match with another user | Yes |
| DELETE | `/api/v1/matches/{id}` | Delete a match | Yes |

## Likes and Dislikes

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| POST | `/api/v1/likes` | Like a user | Yes |
| POST | `/api/v1/dislikes` | Dislike a user | Yes |
| GET | `/api/v1/likes/received` | Get users who liked the authenticated user | Yes |
| GET | `/api/v1/likes/sent` | Get users that the authenticated user has liked | Yes |

## Chat

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/chats` | Get all chats for the authenticated user | Yes |
| GET | `/api/v1/chats/{id}` | Get a specific chat with its messages | Yes |
| POST | `/api/v1/chats/{id}/messages` | Send a message in a chat | Yes |
| POST | `/api/v1/chats/{id}/read` | Mark all messages in a chat as read | Yes |

## Request and Response Formats

All API endpoints accept and return JSON data. The general format for successful responses is:

```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": {
    "key1": "value1",
    "key2": "value2"
  }
}
```

The `data` object contains response data specific to each endpoint.

For error responses:

```json
{
  "status": "error",
  "message": "Error message describing what went wrong",
  "errors": {
    "field1": ["Error message for field1"],
    "field2": ["Error message for field2"]
  }
}
```

The `errors` object contains validation errors or other specific error details.

## Authentication

Most endpoints require authentication using Laravel Sanctum. To authenticate, include the token in the Authorization header:

```
Authorization: Bearer {your_token}
```

Tokens are obtained by calling the login or register endpoints.

## Pagination

Endpoints that return lists of items (matches, likes, chats, messages) support pagination with the following query parameters:

- `page`: The page number (default: 1)
- `per_page`: The number of items per page (default varies by endpoint)

Paginated responses include pagination metadata:

```json
{
  "status": "success",
  "data": {
    "items": [
      {
        "id": 1,
        "name": "Example Item 1"
      },
      {
        "id": 2,
        "name": "Example Item 2"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
}
```

The `items` array contains the list of items returned by the endpoint.

## Rate Limiting

Some endpoints, such as login and OTP requests, are rate-limited to prevent abuse. When rate limits are exceeded, the API will return a 429 Too Many Requests response.

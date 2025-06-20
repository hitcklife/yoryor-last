# YorYor API Routes Documentation

This document provides a comprehensive list of all API endpoints available in the YorYor dating application.

## Public Endpoints

### Countries

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/countries` | Get all countries from the database | No |

#### Example Response
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "United States",
      "code": "US",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Canada",
      "code": "CA",
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  ]
}
```

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

#### Example Request for Sending OTP
```json
{
  "phone": "+1234567890"
}
```

#### Example Response for Sending OTP
```json
{
  "status": "success",
  "message": "OTP sent successfully",
  "data": {
    "otp_sent": true,
    "authenticated": false,
    "registration_completed": false,
    "phone": "+1234567890",
    "expires_in": 300
  }
}
```

#### Example Request for Verifying OTP
```json
{
  "phone": "+1234567890",
  "otp": "1234"
}
```

#### Example Response for Verifying OTP (New User)
```json
{
  "status": "success",
  "message": "Authentication successful",
  "data": {
    "otp_sent": false,
    "authenticated": true,
    "registration_completed": false,
    "user": {
      "id": 1,
      "phone": "+1234567890",
      "email": null,
      "registration_completed": false,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

#### Example Response for Verifying OTP (Registered User)
```json
{
  "status": "success",
  "message": "Authentication successful",
  "data": {
    "otp_sent": false,
    "authenticated": true,
    "registration_completed": true,
    "user": {
      "id": 1,
      "phone": "+1234567890",
      "email": "user@example.com",
      "registration_completed": true,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "profile": {
        "id": 1,
        "user_id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "date_of_birth": "1990-01-01",
        "gender": "male",
        "bio": "Hello, I'm John!",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      },
      "photos": [
        {
          "id": 1,
          "user_id": 1,
          "photo_url": "https://example.com/photos/1.jpg",
          "is_profile_photo": true,
          "order": 0,
          "is_private": false,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        }
      ],
      "likes": []
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

#### Example Request for Logout
```json
{}
```

#### Example Response for Logout
```json
{
  "status": "success",
  "message": "Successfully logged out"
}
```

#### Example Request for Complete Registration
```json
{
  "email": "user@example.com",
  "firstName": "John",
  "lastName": "Doe",
  "dateOfBirth": "1990-01-01",
  "gender": "male",
  "bio": "Hello, I'm John!",
  "interests": ["music", "sports", "travel"],
  "country": "United States",
  "countryCode": "US",
  "state": "California",
  "city": "Los Angeles"
}
```

#### Example Response for Complete Registration
```json
{
  "status": "success",
  "message": "Registration completed successfully",
  "data": {
    "user": {
      "id": 1,
      "phone": "+1234567890",
      "email": "user@example.com",
      "registration_completed": true,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "photos": []
    }
  }
}
```

### Two-Factor Authentication

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| POST | `/api/v1/auth/2fa/enable` | Enable two-factor authentication | Yes |
| POST | `/api/v1/auth/2fa/disable` | Disable two-factor authentication | Yes |
| POST | `/api/v1/auth/2fa/verify` | Verify a two-factor authentication code | Yes |

#### Example Request for Enable 2FA
```json
{}
```

#### Example Response for Enable 2FA
```json
{
  "status": "success",
  "message": "Two-factor authentication enabled successfully",
  "data": {
    "secret_key": "ABCDEFGHIJKLMNOP",
    "qr_code_url": "otpauth://totp/YorYor:user@example.com?secret=ABCDEFGHIJKLMNOP&issuer=YorYor",
    "recovery_codes": ["code1", "code2", "code3", "code4", "code5"]
  }
}
```

#### Example Request for Disable 2FA
```json
{}
```

#### Example Response for Disable 2FA
```json
{
  "status": "success",
  "message": "Two-factor authentication disabled successfully"
}
```

#### Example Request for Verify 2FA Code
```json
{
  "code": "123456"
}
```

#### Example Response for Verify 2FA Code
```json
{
  "status": "success",
  "message": "Two-factor authentication code verified successfully"
}
```

## Profile Management

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/profile/me` | Get the authenticated user's profile | Yes |
| PUT | `/api/v1/profile/{profile}` | Update a profile | Yes |

#### Example Response for Get My Profile
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "bio": "Hello, I'm John!",
    "location": "Los Angeles, CA",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Example Request for Update Profile
```json
{
  "first_name": "John",
  "last_name": "Smith",
  "bio": "Updated bio information",
  "location": "New York, NY"
}
```

#### Example Response for Update Profile
```json
{
  "status": "success",
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "first_name": "John",
    "last_name": "Smith",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "bio": "Updated bio information",
    "location": "New York, NY",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

## Photo Management

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/photos` | Get all photos for the authenticated user | Yes |
| POST | `/api/v1/photos/upload` | Upload a new photo | Yes |
| DELETE | `/api/v1/photos/{id}` | Delete a photo | Yes |

#### Example Response for Get Photos
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "photo_url": "https://example.com/photos/1.jpg",
      "is_profile_photo": true,
      "order": 0,
      "is_private": false,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    },
    {
      "id": 2,
      "user_id": 1,
      "photo_url": "https://example.com/photos/2.jpg",
      "is_profile_photo": false,
      "order": 1,
      "is_private": false,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z"
    }
  ]
}
```

#### Example Request for Upload Photo
Form data:
- `photo`: [File upload]
- `is_profile_photo`: true/false
- `order`: 0
- `is_private`: true/false

#### Example Response for Upload Photo
```json
{
  "status": "success",
  "message": "Photo uploaded successfully",
  "data": {
    "id": 3,
    "user_id": 1,
    "photo_url": "https://example.com/photos/3.jpg",
    "is_profile_photo": true,
    "order": 0,
    "is_private": false,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  }
}
```

#### Example Response for Delete Photo
```json
{
  "status": "success",
  "message": "Photo deleted successfully"
}
```

## Matches

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/matches/potential` | Get potential matches based on preferences | Yes |
| GET | `/api/v1/matches` | Get all matches for the authenticated user | Yes |
| POST | `/api/v1/matches` | Create a match with another user | Yes |
| DELETE | `/api/v1/matches/{id}` | Delete a match | Yes |

#### Example Response for Get Potential Matches
```json
{
  "data": [
    {
      "id": 2,
      "email": "jane@example.com",
      "phone": "+1987654321",
      "registration_completed": true,
      "created_at": "2023-01-01T00:00:00.000000Z",
      "updated_at": "2023-01-01T00:00:00.000000Z",
      "profile": {
        "id": 2,
        "user_id": 2,
        "first_name": "Jane",
        "last_name": "Doe",
        "date_of_birth": "1992-05-15",
        "gender": "female",
        "bio": "Hello, I'm Jane!",
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      },
      "photos": [
        {
          "id": 3,
          "user_id": 2,
          "photo_url": "https://example.com/photos/jane1.jpg",
          "is_profile_photo": true,
          "order": 0,
          "is_private": false,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        }
      ],
      "profile_photo": {
        "id": 3,
        "user_id": 2,
        "photo_url": "https://example.com/photos/jane1.jpg",
        "is_profile_photo": true,
        "order": 0,
        "is_private": false,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z"
      }
    }
  ],
  "links": {
    "first": "http://example.com/api/v1/matches/potential?page=1",
    "last": "http://example.com/api/v1/matches/potential?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://example.com/api/v1/matches/potential?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "path": "http://example.com/api/v1/matches/potential",
    "per_page": 10,
    "to": 1,
    "total": 1
  },
  "status": "success"
}
```

#### Example Request for Create Match
```json
{
  "user_id": 2
}
```

#### Example Response for Create Match
```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "matched_user_id": 2,
    "matched_at": "2023-01-01T12:00:00.000000Z",
    "created_at": "2023-01-01T12:00:00.000000Z",
    "updated_at": "2023-01-01T12:00:00.000000Z",
    "is_mutual": false
  },
  "status": "success",
  "message": "Match created successfully"
}
```

#### Example Response for Get Matches
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "matched_user_id": 2,
      "matched_at": "2023-01-01T12:00:00.000000Z",
      "created_at": "2023-01-01T12:00:00.000000Z",
      "updated_at": "2023-01-01T12:00:00.000000Z",
      "is_mutual": true,
      "matched_user": {
        "id": 2,
        "email": "jane@example.com",
        "phone": "+1987654321",
        "registration_completed": true,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z",
        "profile": {
          "id": 2,
          "user_id": 2,
          "first_name": "Jane",
          "last_name": "Doe",
          "date_of_birth": "1992-05-15",
          "gender": "female",
          "bio": "Hello, I'm Jane!",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        },
        "profile_photo": {
          "id": 3,
          "user_id": 2,
          "photo_url": "https://example.com/photos/jane1.jpg",
          "is_profile_photo": true,
          "order": 0,
          "is_private": false,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        }
      }
    }
  ],
  "links": {
    "first": "http://example.com/api/v1/matches?page=1",
    "last": "http://example.com/api/v1/matches?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "links": [
      {
        "url": null,
        "label": "&laquo; Previous",
        "active": false
      },
      {
        "url": "http://example.com/api/v1/matches?page=1",
        "label": "1",
        "active": true
      },
      {
        "url": null,
        "label": "Next &raquo;",
        "active": false
      }
    ],
    "path": "http://example.com/api/v1/matches",
    "per_page": 10,
    "to": 1,
    "total": 1
  }
}
```

#### Example Response for Delete Match
```json
{
  "status": "success",
  "message": "Match deleted successfully"
}
```

## Likes and Dislikes

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| POST | `/api/v1/likes` | Like a user | Yes |
| POST | `/api/v1/dislikes` | Dislike a user | Yes |
| GET | `/api/v1/likes/received` | Get users who liked the authenticated user | Yes |
| GET | `/api/v1/likes/sent` | Get users that the authenticated user has liked | Yes |

#### Example Request for Like User
```json
{
  "user_id": 2
}
```

#### Example Response for Like User
```json
{
  "status": "success",
  "message": "User liked successfully",
  "data": {
    "like": {
      "id": 1,
      "user_id": 1,
      "liked_user_id": 2,
      "liked_at": "2023-01-01T12:00:00.000000Z",
      "created_at": "2023-01-01T12:00:00.000000Z",
      "updated_at": "2023-01-01T12:00:00.000000Z"
    },
    "is_match": false
  }
}
```

#### Example Request for Dislike User
```json
{
  "user_id": 3
}
```

#### Example Response for Dislike User
```json
{
  "status": "success",
  "message": "User disliked successfully",
  "data": {
    "dislike": {
      "id": 1,
      "user_id": 1,
      "disliked_user_id": 3,
      "created_at": "2023-01-01T12:00:00.000000Z",
      "updated_at": "2023-01-01T12:00:00.000000Z"
    }
  }
}
```

#### Example Response for Get Received Likes
```json
{
  "status": "success",
  "data": {
    "likes": [
      {
        "id": 2,
        "user_id": 2,
        "liked_user_id": 1,
        "liked_at": "2023-01-01T12:00:00.000000Z",
        "created_at": "2023-01-01T12:00:00.000000Z",
        "updated_at": "2023-01-01T12:00:00.000000Z",
        "user": {
          "id": 2,
          "email": "jane@example.com",
          "phone": "+1987654321",
          "registration_completed": true,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "profile": {
            "id": 2,
            "user_id": 2,
            "first_name": "Jane",
            "last_name": "Doe",
            "date_of_birth": "1992-05-15",
            "gender": "female",
            "bio": "Hello, I'm Jane!",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          },
          "profile_photo": {
            "id": 3,
            "user_id": 2,
            "photo_url": "https://example.com/photos/jane1.jpg",
            "is_profile_photo": true,
            "order": 0,
            "is_private": false,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          }
        }
      }
    ],
    "pagination": {
      "total": 1,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Example Response for Get Sent Likes
```json
{
  "status": "success",
  "data": {
    "likes": [
      {
        "id": 1,
        "user_id": 1,
        "liked_user_id": 2,
        "liked_at": "2023-01-01T12:00:00.000000Z",
        "created_at": "2023-01-01T12:00:00.000000Z",
        "updated_at": "2023-01-01T12:00:00.000000Z",
        "liked_user": {
          "id": 2,
          "email": "jane@example.com",
          "phone": "+1987654321",
          "registration_completed": true,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "profile": {
            "id": 2,
            "user_id": 2,
            "first_name": "Jane",
            "last_name": "Doe",
            "date_of_birth": "1992-05-15",
            "gender": "female",
            "bio": "Hello, I'm Jane!",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          },
          "profile_photo": {
            "id": 3,
            "user_id": 2,
            "photo_url": "https://example.com/photos/jane1.jpg",
            "is_profile_photo": true,
            "order": 0,
            "is_private": false,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          }
        }
      }
    ],
    "pagination": {
      "total": 1,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

## Chat

| Method | Endpoint | Description | Authentication Required |
|--------|----------|-------------|------------------------|
| GET | `/api/v1/chats` | Get all chats for the authenticated user | Yes |
| GET | `/api/v1/chats/{id}` | Get a specific chat with its messages | Yes |
| POST | `/api/v1/chats/{id}/messages` | Send a message in a chat | Yes |
| POST | `/api/v1/chats/{id}/read` | Mark all messages in a chat as read | Yes |

#### Example Response for Get Chats
```json
{
  "status": "success",
  "data": {
    "chats": [
      {
        "id": 1,
        "user_id_1": 1,
        "user_id_2": 2,
        "created_at": "2023-01-01T12:00:00.000000Z",
        "updated_at": "2023-01-01T12:00:00.000000Z",
        "last_message": {
          "id": 3,
          "chat_id": 1,
          "sender_id": 2,
          "content": "How are you?",
          "media_url": null,
          "read": false,
          "created_at": "2023-01-01T12:05:00.000000Z",
          "updated_at": "2023-01-01T12:05:00.000000Z"
        },
        "unread_count": 1,
        "other_user": {
          "id": 2,
          "email": "jane@example.com",
          "phone": "+1987654321",
          "registration_completed": true,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z",
          "profile": {
            "id": 2,
            "user_id": 2,
            "first_name": "Jane",
            "last_name": "Doe",
            "date_of_birth": "1992-05-15",
            "gender": "female",
            "bio": "Hello, I'm Jane!",
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          },
          "profile_photo": {
            "id": 3,
            "user_id": 2,
            "photo_url": "https://example.com/photos/jane1.jpg",
            "is_profile_photo": true,
            "order": 0,
            "is_private": false,
            "created_at": "2023-01-01T00:00:00.000000Z",
            "updated_at": "2023-01-01T00:00:00.000000Z"
          }
        }
      }
    ],
    "pagination": {
      "total": 1,
      "per_page": 10,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Example Response for Get Chat
```json
{
  "status": "success",
  "data": {
    "chat": {
      "id": 1,
      "user_id_1": 1,
      "user_id_2": 2,
      "created_at": "2023-01-01T12:00:00.000000Z",
      "updated_at": "2023-01-01T12:00:00.000000Z",
      "other_user": {
        "id": 2,
        "email": "jane@example.com",
        "phone": "+1987654321",
        "registration_completed": true,
        "created_at": "2023-01-01T00:00:00.000000Z",
        "updated_at": "2023-01-01T00:00:00.000000Z",
        "profile": {
          "id": 2,
          "user_id": 2,
          "first_name": "Jane",
          "last_name": "Doe",
          "date_of_birth": "1992-05-15",
          "gender": "female",
          "bio": "Hello, I'm Jane!",
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        },
        "profile_photo": {
          "id": 3,
          "user_id": 2,
          "photo_url": "https://example.com/photos/jane1.jpg",
          "is_profile_photo": true,
          "order": 0,
          "is_private": false,
          "created_at": "2023-01-01T00:00:00.000000Z",
          "updated_at": "2023-01-01T00:00:00.000000Z"
        }
      }
    },
    "messages": [
      {
        "id": 3,
        "chat_id": 1,
        "sender_id": 2,
        "content": "How are you?",
        "media_url": null,
        "read": true,
        "created_at": "2023-01-01T12:05:00.000000Z",
        "updated_at": "2023-01-01T12:05:00.000000Z",
        "sender": {
          "id": 2,
          "email": "jane@example.com"
        },
        "is_mine": false
      },
      {
        "id": 2,
        "chat_id": 1,
        "sender_id": 1,
        "content": "Hi Jane!",
        "media_url": null,
        "read": true,
        "created_at": "2023-01-01T12:02:00.000000Z",
        "updated_at": "2023-01-01T12:02:00.000000Z",
        "sender": {
          "id": 1,
          "email": "user@example.com"
        },
        "is_mine": true
      },
      {
        "id": 1,
        "chat_id": 1,
        "sender_id": 2,
        "content": "Hello!",
        "media_url": null,
        "read": true,
        "created_at": "2023-01-01T12:00:00.000000Z",
        "updated_at": "2023-01-01T12:00:00.000000Z",
        "sender": {
          "id": 2,
          "email": "jane@example.com"
        },
        "is_mine": false
      }
    ],
    "pagination": {
      "total": 3,
      "per_page": 20,
      "current_page": 1,
      "last_page": 1
    }
  }
}
```

#### Example Request for Send Message
```json
{
  "content": "Hello, how are you?"
}
```

#### Example Response for Send Message
```json
{
  "status": "success",
  "message": "Message sent successfully",
  "data": {
    "message": {
      "id": 4,
      "chat_id": 1,
      "sender_id": 1,
      "content": "Hello, how are you?",
      "media_url": null,
      "read": false,
      "created_at": "2023-01-01T12:10:00.000000Z",
      "updated_at": "2023-01-01T12:10:00.000000Z",
      "is_mine": true
    }
  }
}
```

#### Example Response for Mark Messages as Read
```json
{
  "status": "success",
  "message": "Messages marked as read successfully",
  "data": {
    "count": 1
  }
}
```

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

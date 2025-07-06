# Video SDK Live Backend Setup Guide

This guide explains how to set up the backend to support Video SDK Live integration for your React Native dating app.

## Overview

Video SDK Live requires a backend implementation to:
1. Generate authentication tokens
2. Create meeting/room IDs
3. Manage meeting participants
4. Handle meeting events

## 1. Installation

The Video SDK backend integration has been implemented in this project. To use it, you need to:

1. Install the required dependencies:
   ```bash
   composer install
   ```

2. Set up your environment variables in the `.env` file:
   ```
   VIDEOSDK_API_KEY=your_api_key_here
   VIDEOSDK_SECRET_KEY=your_secret_key_here
   VIDEOSDK_API_ENDPOINT=https://api.videosdk.live/v2
   ```

## 2. Getting Video SDK API Keys

To get your API keys:

1. Go to [Video SDK Dashboard](https://app.videosdk.live/)
2. Create an account or sign in
3. Create a new project
4. Get your API Key and Secret Key from the dashboard

## 3. API Endpoints

The following API endpoints are available for Video SDK integration:

### Token Generation

```
POST /api/v1/video-call/token
```

**Response:**
```json
{
  "token": "your_jwt_token",
  "success": true
}
```

### Create Meeting

```
POST /api/v1/video-call/create-meeting
```

**Request Body (optional):**
```json
{
  "customRoomId": "custom-room-id"
}
```

**Response:**
```json
{
  "meetingId": "meeting-id",
  "token": "your_jwt_token",
  "success": true
}
```

### Validate Meeting

```
GET /api/v1/video-call/validate-meeting/{meetingId}
```

**Response:**
```json
{
  "valid": true,
  "meetingId": "meeting-id",
  "success": true
}
```

### Initiate Call

```
POST /api/v1/video-call/initiate
```

**Request Body:**
```json
{
  "receiver_id": 123,
  "type": "video"
}
```

The `type` field can be either `"video"` for video calls or `"voice"` for audio-only calls.

**Response:**
```json
{
  "status": "success",
  "data": {
    "call_id": 1,
    "meeting_id": "meeting-id",
    "token": "your_jwt_token",
    "type": "video",
    "caller": {
      "id": 1,
      "name": "John Doe"
    },
    "receiver": {
      "id": 2,
      "name": "Jane Doe"
    }
  }
}
```

### Join Call

```
POST /api/v1/video-call/{callId}/join
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "call_id": 1,
    "meeting_id": "meeting-id",
    "token": "your_jwt_token",
    "type": "video",
    "caller": {
      "id": 1,
      "name": "John Doe"
    },
    "receiver": {
      "id": 2,
      "name": "Jane Doe"
    }
  }
}
```

### End Call

```
POST /api/v1/video-call/{callId}/end
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "message": "Call ended successfully",
    "call_id": 1,
    "duration": 120
  }
}
```

### Reject Call

```
POST /api/v1/video-call/{callId}/reject
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "message": "Call rejected successfully",
    "call_id": 1
  }
}
```

### Call History

```
GET /api/v1/video-call/history
```

**Query Parameters (optional):**
```
page=1
per_page=15
status=completed
type=video
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "channel_name": "meeting-id",
        "caller_id": 1,
        "receiver_id": 2,
        "type": "video",
        "status": "completed",
        "started_at": "2023-06-01T12:00:00Z",
        "ended_at": "2023-06-01T12:02:00Z",
        "created_at": "2023-06-01T11:59:00Z",
        "updated_at": "2023-06-01T12:02:00Z",
        "caller": {
          "id": 1,
          "name": "John Doe",
          "profile_photo_path": "path/to/photo"
        },
        "receiver": {
          "id": 2,
          "name": "Jane Doe",
          "profile_photo_path": "path/to/photo"
        }
      }
    ],
    "total": 1,
    "per_page": 15
  }
}
```

## 4. Integration with Frontend

In your React Native app, you'll need to:

1. Call the token generation endpoint to get a token
2. Create a meeting or join an existing one
3. Use the Video SDK React Native library to implement the UI

Example frontend code for initiating a call:

```javascript
// Example of initiating a call
const initiateCall = async (receiverId) => {
  try {
    const response = await fetch('https://your-api.com/api/v1/video-call/initiate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + userToken
      },
      body: JSON.stringify({
        receiver_id: receiverId,
        type: 'video'
      })
    });

    const data = await response.json();

    if (data.status === 'success') {
      // Navigate to call screen with meeting details
      navigation.navigate('CallScreen', {
        meetingId: data.data.meeting_id,
        token: data.data.token,
        callId: data.data.call_id,
        receiver: data.data.receiver
      });
    }
  } catch (error) {
    console.error('Failed to initiate call:', error);
  }
};
```

## 5. Security Considerations

1. **Never expose your Secret Key** on the frontend
2. **Implement proper authentication** before generating tokens
3. **Add rate limiting** to prevent abuse
4. **Validate user permissions** before allowing meeting creation/joining
5. **Store meeting IDs securely** and associate them with user sessions

## 6. Troubleshooting

- **Token generation fails**: Check your API key and secret key
- **Meeting creation fails**: Verify your token is valid and has proper permissions
- **Calls don't connect**: Ensure your mobile app has the correct permissions and token

For more detailed documentation, visit: https://docs.videosdk.live/

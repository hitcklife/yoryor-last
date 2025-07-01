# Agora Integration for Video and Voice Calling

This document provides information on how to set up and use the Agora SDK integration for video and voice calling in the application.

## Setup

1. Install the required packages:
   ```bash
   composer require agora/token-builder
   ```

2. Run the migration to create the calls table:
   ```bash
   php artisan migrate
   ```

3. Add your Agora credentials to the `.env` file:
   ```
   AGORA_APP_ID=your_app_id
   AGORA_APP_CERTIFICATE=your_app_certificate
   ```

4. Make sure the Pusher broadcasting is set up correctly for real-time events.

## API Endpoints

The following API endpoints are available for Agora integration:

### Generate Token

```
POST /api/v1/calls/token
```

Request body:
```json
{
  "channel_name": "your_channel_name",
  "uid": "user_id",
  "role": 2
}
```

Response:
```json
{
  "token": "generated_token",
  "channel_name": "your_channel_name",
  "uid": "user_id"
}
```

### Initiate Call

```
POST /api/v1/calls/initiate
```

Request body:
```json
{
  "receiver_id": 123,
  "type": "video"
}
```

Response:
```json
{
  "call_id": 1,
  "channel_name": "generated_channel_name",
  "token": "generated_token",
  "type": "video",
  "caller": {
    "id": 1,
    "name": "Caller Name"
  },
  "receiver": {
    "id": 123,
    "name": "Receiver Name"
  }
}
```

### Join Call

```
POST /api/v1/calls/{callId}/join
```

Response:
```json
{
  "call_id": 1,
  "channel_name": "channel_name",
  "token": "generated_token",
  "type": "video",
  "caller": {
    "id": 1,
    "name": "Caller Name"
  },
  "receiver": {
    "id": 123,
    "name": "Receiver Name"
  }
}
```

### End Call

```
POST /api/v1/calls/{callId}/end
```

Response:
```json
{
  "message": "Call ended successfully",
  "call_id": 1,
  "duration": 120
}
```

### Reject Call

```
POST /api/v1/calls/{callId}/reject
```

Response:
```json
{
  "message": "Call rejected successfully",
  "call_id": 1
}
```

### Get Call History

```
GET /api/v1/calls/history
```

Response:
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "channel_name": "channel_name",
      "caller_id": 1,
      "receiver_id": 123,
      "type": "video",
      "status": "completed",
      "started_at": "2023-05-15T10:00:00Z",
      "ended_at": "2023-05-15T10:02:00Z",
      "created_at": "2023-05-15T09:59:30Z",
      "updated_at": "2023-05-15T10:02:00Z",
      "caller": {
        "id": 1,
        "name": "Caller Name",
        "profile_photo_path": "path/to/photo"
      },
      "receiver": {
        "id": 123,
        "name": "Receiver Name",
        "profile_photo_path": "path/to/photo"
      }
    }
  ],
  "first_page_url": "...",
  "from": 1,
  "last_page": 1,
  "last_page_url": "...",
  "links": [
    {"url": null, "label": "&laquo; Previous", "active": false},
    {"url": "http://example.com/api/v1/calls/history?page=1", "label": "1", "active": true},
    {"url": null, "label": "Next &raquo;", "active": false}
  ],
  "next_page_url": null,
  "path": "...",
  "per_page": 15,
  "prev_page_url": null,
  "to": 1,
  "total": 1
}
```

## Real-time Events

The following events are broadcasted for real-time updates:

### CallInitiatedEvent

Broadcasted to the receiver when a call is initiated.

Channel: `private-user.{receiver_id}`
Event: `CallInitiated`

### CallStatusChangedEvent

Broadcasted to both caller and receiver (excluding the user who changed the status) when a call's status changes.

Channel: `private-user.{user_id}`
Event: `CallStatusChanged`

## Mobile App Integration

To integrate Agora in your mobile app:

1. Install the Agora SDK for your platform (iOS/Android)
2. Use the token generation endpoint to get a token
3. Use the token and channel name to join a call
4. Use the other endpoints to manage calls (initiate, join, end, reject)
5. Listen for real-time events to update the UI

For more information, refer to the [Agora Documentation](https://docs.agora.io/).

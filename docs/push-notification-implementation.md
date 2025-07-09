# Push Notification Implementation

This document outlines the implementation of push notifications using Expo for the YorYor application.

## Overview

The push notification system allows the application to send notifications to users' mobile devices even when they are not actively using the application. This is achieved using Expo's push notification service, which supports both iOS and Android devices.

## Components

### 1. Device Token Management

Device tokens are managed through the `DeviceTokenController` which provides endpoints for:

- **Storing device tokens**: `POST /api/v1/device-tokens`
- **Deleting device tokens**: `DELETE /api/v1/device-tokens`

The `DeviceToken` model stores information about each device, including:
- Token
- Device name
- Brand
- Model name
- OS name and version
- Device type
- Manufacturer

### 2. Expo Push Service

The `ExpoPushService` class handles sending push notifications to Expo devices. It provides methods for:

- Sending notifications to a single user
- Sending notifications to multiple users
- Sending notifications to specific device tokens

The service validates Expo tokens before sending notifications and handles error logging.

### 3. Notification Service Integration

The existing `NotificationService` has been extended to support push notifications. When a notification is sent using this service, it:

1. Broadcasts the notification via WebSockets for real-time updates
2. Sends push notifications to the user's registered devices

The service includes methods for:
- Sending notifications to individual users
- Sending bulk notifications to multiple users
- Sending notifications to all active users

## WebSocket Optimizations

Several optimizations have been made to the WebSocket events system:

### 1. UserOnlineStatusChanged Event

- Now only broadcasts to the 5 most recent/active chats a user is part of, rather than all chats
- Continues to broadcast to the user's matches/friends channel and the general presence channel
- This reduces overhead for users who are part of many chats

### 2. UserTypingStatusChanged Event

- Now only broadcasts to the presence channel for the chat, rather than both presence and private channels
- This reduces redundancy since all active users in the chat should be subscribed to the presence channel

## Usage

### Sending Push Notifications

To send a push notification to a user:

```php
$notificationService = app(NotificationService::class);
$notificationService->sendNotification(
    $user,
    'notification_type',
    'Notification Title',
    'Notification Message',
    ['additional' => 'data'],
    true // Enable push notifications
);
```

To send bulk notifications:

```php
$notificationService->sendBulkNotification(
    $userIds,
    'notification_type',
    'Notification Title',
    'Notification Message',
    ['additional' => 'data'],
    true // Enable push notifications
);
```

### Client-Side Integration

Mobile clients need to:

1. Register for push notifications using Expo's SDK
2. Send the Expo push token to the server using the `POST /api/v1/device-tokens` endpoint
3. Handle incoming notifications in the app
4. Delete the token when logging out using the `DELETE /api/v1/device-tokens` endpoint

## Testing

To test push notifications:

1. Register a device token using the API
2. Send a notification to the user
3. Verify that the notification is received on the device

## Troubleshooting

Common issues:

- **Invalid tokens**: Ensure tokens are valid Expo push tokens
- **Missing permissions**: Ensure the app has notification permissions on the device
- **Network issues**: Check connectivity between the server and Expo's push service

Logs for push notifications can be found in the Laravel logs.

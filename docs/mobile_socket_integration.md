# Mobile App Socket Integration Guide

This guide explains how to integrate with the chat socket functionality in your mobile app.

## Prerequisites

1. Pusher SDK for your mobile platform:
   - For iOS: [pusher-websocket-swift](https://github.com/pusher/pusher-websocket-swift)
   - For Android: [pusher-websocket-java](https://github.com/pusher/pusher-websocket-java)

2. Authentication token from the API (obtained during login)

## Configuration

Use the following Pusher credentials in your mobile app:

```
PUSHER_APP_ID=1214634
PUSHER_APP_KEY=71d10c58900cc58fb02d
PUSHER_APP_CLUSTER=us2
```

## Authentication

The chat channels are private channels that require authentication. You need to set up your Pusher instance to authenticate with the Laravel backend:

### iOS Example (Swift)

```swift
import PusherSwift

// Initialize Pusher
let options = PusherClientOptions(
    authMethod: .authEndpoint,
    endpoint: "https://your-api-url.com/broadcasting/auth",
    headers: ["Authorization": "Bearer \(userToken)"]
)

let pusher = Pusher(key: "71d10c58900cc58fb02d", options: options)
pusher.connect()
```

### Android Example (Java/Kotlin)

```kotlin
import com.pusher.client.Pusher
import com.pusher.client.PusherOptions
import com.pusher.client.util.HttpAuthorizer

// Initialize Pusher
val authorizer = HttpAuthorizer("https://your-api-url.com/broadcasting/auth")
authorizer.setHeaders(mapOf("Authorization" to "Bearer $userToken"))

val options = PusherOptions()
    .setCluster("us2")
    .setAuthorizer(authorizer)

val pusher = Pusher("71d10c58900cc58fb02d", options)
pusher.connect()
```

## Subscribing to Chat Channels

To receive real-time updates for a specific chat, subscribe to its private channel:

### iOS Example (Swift)

```swift
// Subscribe to a chat channel
let chatChannel = pusher.subscribe("private-chat.123") // where 123 is the chat ID

// Listen for new messages
chatChannel.bind("new.message") { data in
    if let dataString = data as? String,
       let jsonData = dataString.data(using: .utf8),
       let messageData = try? JSONDecoder().decode(MessageData.self, from: jsonData) {
        // Handle new message
        self.handleNewMessage(messageData.message)
    }
}

// Listen for read receipts
chatChannel.bind("messages.read") { data in
    if let dataString = data as? String,
       let jsonData = dataString.data(using: .utf8),
       let readData = try? JSONDecoder().decode(ReadReceiptData.self, from: jsonData) {
        // Handle read receipt
        self.handleReadReceipt(readData)
    }
}
```

### Android Example (Kotlin)

```kotlin
// Subscribe to a chat channel
val chatChannel = pusher.subscribePrivate("private-chat.123") // where 123 is the chat ID

// Listen for new messages
chatChannel.bind("new.message") { event ->
    val messageData = gson.fromJson(event.data, MessageData::class.java)
    // Handle new message
    handleNewMessage(messageData.message)
}

// Listen for read receipts
chatChannel.bind("messages.read") { event ->
    val readData = gson.fromJson(event.data, ReadReceiptData::class.java)
    // Handle read receipt
    handleReadReceipt(readData)
}
```

## Event Data Structures

### New Message Event

When a new message is sent, the `new.message` event is triggered with the following data structure:

```json
{
  "message": {
    "id": 123,
    "chat_id": 456,
    "sender_id": 789,
    "content": "Hello, world!",
    "message_type": "text",
    "media_url": null,
    "media_data": null,
    "sent_at": "2023-06-19T20:30:11.000000Z",
    "status": "sent",
    "sender": {
      "id": 789,
      "email": "user@example.com"
    }
  }
}
```

### Message Read Event

When messages are marked as read, the `messages.read` event is triggered with the following data structure:

```json
{
  "chat_id": 456,
  "user_id": 789,
  "count": 5,
  "timestamp": "2023-06-19T20:35:22.000000Z"
}
```

## Handling Connection States

It's important to handle different connection states to ensure a good user experience:

```kotlin
pusher.connection.bind(ConnectionState.CONNECTED) {
    // Connected to Pusher
    updateConnectionStatus("Connected")
}

pusher.connection.bind(ConnectionState.DISCONNECTED) {
    // Disconnected from Pusher
    updateConnectionStatus("Disconnected")
}

pusher.connection.bind(ConnectionState.CONNECTING) {
    // Connecting to Pusher
    updateConnectionStatus("Connecting...")
}

pusher.connection.bind(ConnectionState.FAILED) {
    // Connection failed
    updateConnectionStatus("Connection failed")
    // Implement retry logic
}
```

## Best Practices

1. **Reconnection Strategy**: Implement a reconnection strategy with exponential backoff to handle network issues.

2. **Offline Mode**: Implement offline mode to handle cases when the user is not connected to the internet.

3. **Unsubscribe When Not Needed**: Unsubscribe from channels when they are no longer needed to save resources.

4. **Error Handling**: Implement proper error handling for all socket operations.

5. **Sync with API**: When reconnecting after being offline, sync with the API to get any missed messages.

## Troubleshooting

1. **Authentication Issues**: Make sure you're sending the correct authentication token in the headers.

2. **Connection Issues**: Check if the Pusher credentials are correct and if the device has internet connectivity.

3. **Channel Subscription Issues**: Verify that the channel name format is correct (`private-chat.{id}`).

4. **Event Binding Issues**: Make sure the event names match exactly (`new.message` and `messages.read`).

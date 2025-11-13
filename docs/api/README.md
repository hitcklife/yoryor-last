# YorYor API Documentation

**Quick Start Guide for Mobile and Third-Party Developers**

---

## 🚀 Quick Start

### Base URLs

```
Production: https://api.yoryor.com/api/v1
Development: http://localhost:8000/api/v1
```

### Authentication

All authenticated endpoints require a Bearer token:

```http
Authorization: Bearer {your-token-here}
```

### Get Started in 5 Minutes

#### 1. Check if Email Exists
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/check-email \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'
```

#### 2. Authenticate (Login/Register)
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/authenticate \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "SecurePassword123"
  }'
```

#### 3. Use the Token
```bash
curl -X GET https://api.yoryor.com/api/v1/profile/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

---

## 📚 Documentation Files

| File | Description |
|------|-------------|
| **[ENDPOINTS.md](ENDPOINTS.md)** | Complete API reference with all 100+ endpoints |
| **[AUTHENTICATION.md](AUTHENTICATION.md)** | Authentication flows, 2FA, OTP, token management |
| **[WEBSOCKETS.md](WEBSOCKETS.md)** | Real-time features with Laravel Reverb |
| **[MOBILE_INTEGRATION.md](MOBILE_INTEGRATION.md)** | React Native/Expo integration guide |

---

## 🔑 Authentication Methods

### 1. Password-Based
```javascript
const response = await fetch('https://api.yoryor.com/api/v1/auth/authenticate', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123'
  })
});
const { token, user } = await response.json();
```

### 2. OTP (Passwordless)
```javascript
// Request OTP
await fetch('https://api.yoryor.com/api/v1/auth/authenticate', {
  method: 'POST',
  body: JSON.stringify({ email: 'user@example.com' })
});

// Verify OTP
const response = await fetch('https://api.yoryor.com/api/v1/auth/authenticate', {
  method: 'POST',
  body: JSON.stringify({ email: 'user@example.com', otp: '123456' })
});
```

### 3. Two-Factor Authentication (2FA)
Enable and verify Google Authenticator codes for enhanced security.

---

## 🎯 Core Features

### User Profiles
```javascript
// Get my profile
GET /v1/profile/me

// Update profile
PUT /v1/profile/update

// Upload photo
POST /v1/photos/upload
```

### Matching & Discovery
```javascript
// Get recommended profiles
GET /v1/home/discovery

// Like a user
POST /v1/likes/{userId}

// Get matches
GET /v1/matches
```

### Chat & Messaging
```javascript
// Get chats
GET /v1/chats

// Send message
POST /v1/messages

// Mark as read
POST /v1/messages/{messageId}/read
```

### Video Calling
```javascript
// Get video call token
GET /v1/video-call/token

// Initiate call
POST /v1/video-call/initiate

// End call
POST /v1/video-call/end
```

---

## 🔄 Real-Time Features (WebSocket)

Connect to Laravel Reverb for real-time updates:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'yoryor-key-123456',
  wsHost: 'api.yoryor.com',
  wsPort: 8080,
  wssPort: 8080,
  forceTLS: true,
  enabledTransports: ['ws', 'wss'],
});

// Listen for new messages
echo.private(`chat.${chatId}`)
  .listen('NewMessageEvent', (e) => {
    console.log('New message:', e.message);
  });
```

See [WEBSOCKETS.md](WEBSOCKETS.md) for complete WebSocket documentation.

---

## 📊 Response Format

All API responses follow JSON:API format:

### Success Response
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "type": "user",
    "attributes": { /* ... */ },
    "relationships": { /* ... */ }
  },
  "message": "Success message"
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error message",
  "errors": {
    "field": ["Validation error"]
  }
}
```

---

## ⚡ Rate Limiting

Different limits for different actions:

| Action Type | Limit |
|-------------|-------|
| Authentication | 10/min |
| Profile Updates | 30/hour |
| Like Actions | 100/hour |
| Message Sending | 500/hour |
| Video Calls | 50/hour |
| Block/Report | 20/hour |

Rate limit headers:
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1635724800
```

---

## 🔒 Security Best Practices

1. **Always use HTTPS** in production
2. **Store tokens securely** (SecureStore for mobile, httpOnly cookies for web)
3. **Never log tokens** or sensitive data
4. **Implement token refresh** for long-lived sessions
5. **Validate responses** from the API
6. **Handle rate limits** gracefully with exponential backoff
7. **Use 2FA** for enhanced security

---

## 🛠️ SDK Examples

### JavaScript (Fetch API)
```javascript
class YorYorAPI {
  constructor(baseURL, token) {
    this.baseURL = baseURL;
    this.token = token;
  }

  async request(endpoint, options = {}) {
    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers: {
        'Authorization': `Bearer ${this.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        ...options.headers,
      },
    });
    return response.json();
  }

  async getProfile() {
    return this.request('/profile/me');
  }

  async sendMessage(chatId, message) {
    return this.request('/messages', {
      method: 'POST',
      body: JSON.stringify({ chat_id: chatId, message }),
    });
  }
}
```

### React Native/Expo
```javascript
import * as SecureStore from 'expo-secure-store';
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.yoryor.com/api/v1',
  headers: { 'Accept': 'application/json' },
});

// Add token to requests
api.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 errors
api.interceptors.response.use(
  response => response,
  async error => {
    if (error.response?.status === 401) {
      await SecureStore.deleteItemAsync('token');
      // Navigate to login
    }
    return Promise.reject(error);
  }
);

export default api;
```

---

## 📖 Common Use Cases

### User Registration Flow
1. Check email → `POST /auth/check-email`
2. Authenticate → `POST /auth/authenticate`
3. Complete profile → `POST /auth/complete-registration`
4. Upload photos → `POST /photos/upload`

### Matching Flow
1. Get recommendations → `GET /home/discovery`
2. Like a user → `POST /likes/{userId}`
3. Check for match → Listen to `NewMatchEvent` via WebSocket
4. Start chatting → `POST /messages`

### Chat Flow
1. Get chats list → `GET /chats`
2. Connect to chat channel → `private-chat.{chatId}`
3. Send message → `POST /messages`
4. Listen for new messages → WebSocket event
5. Mark as read → `POST /messages/{id}/read`

---

## 🐛 Troubleshooting

### Token Issues
```
401 Unauthorized
```
- Token expired or invalid
- Get a new token via `/auth/authenticate`

### Rate Limit Exceeded
```
429 Too Many Requests
```
- Wait for the time specified in `X-RateLimit-Reset` header
- Implement exponential backoff

### WebSocket Connection Failed
- Check Reverb server is running
- Verify correct host and port
- Ensure token is valid
- Check firewall rules

---

## 📞 Support

- 📖 **Full Documentation**: [ENDPOINTS.md](ENDPOINTS.md)
- 🔐 **Authentication**: [AUTHENTICATION.md](AUTHENTICATION.md)
- 🔄 **Real-time**: [WEBSOCKETS.md](WEBSOCKETS.md)
- 📱 **Mobile Guide**: [MOBILE_INTEGRATION.md](MOBILE_INTEGRATION.md)
- 💬 **Issues**: [GitHub Issues](https://github.com/yoryor/yoryor-dating-app/issues)

---

**Happy Coding!** 🚀

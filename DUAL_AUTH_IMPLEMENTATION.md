# 🔐 Dual Authentication Implementation Complete

**Date:** 2025-11-13
**Architecture:** Laravel 12 API + Next.js 15 (Web) + React Native (Mobile)
**Authentication:** Laravel Sanctum with dual modes (Session + Token)

---

## ✅ What Was Completed

### Phase 2: Dual Authentication Implementation

Building on the Laravel API backend setup (Phase 1), we've now implemented full dual authentication support:

1. **AuthService Updated** ✅
   - Modified `register()` method for dual auth modes
   - Modified `login()` method for dual auth modes
   - Manual password verification to avoid unwanted sessions

2. **AuthController Updated** ✅
   - `login()` method: Detects device_type and creates session OR token
   - `register()` method: Detects device_type and creates session OR token
   - `logout()` method: Handles both token revocation AND session destruction
   - Uses `UserResource` for consistent responses
   - Updated Swagger documentation

3. **Broadcasting Already Configured** ✅
   - BroadcastingController supports both auth modes automatically
   - Channel authorization in `routes/channels.php`
   - `auth:sanctum` middleware handles both sessions and tokens

---

## 🎯 How It Works

### Authentication Flow Overview

Laravel Sanctum provides **two authentication modes** that work seamlessly together:

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel API Backend                   │
│                  (auth:sanctum middleware)               │
└─────────────────┬───────────────────────┬───────────────┘
                  │                       │
         ┌────────┴────────┐     ┌───────┴────────┐
         │   SESSION MODE   │     │   TOKEN MODE   │
         │   (Next.js Web)  │     │ (React Native) │
         └────────┬────────┘     └───────┬────────┘
                  │                       │
          Uses session cookies    Uses Bearer tokens
          from stateful domains   in Authorization header
```

---

## 🌐 Mode 1: Session-Based Authentication (Next.js Web)

### How It Works

1. **CSRF Cookie Request**: Next.js calls `/sanctum/csrf-cookie` first
2. **Login Request**: Send credentials with `device_type: 'web'`
3. **Session Creation**: Laravel creates session via `Auth::login()`
4. **Cookie Storage**: Browser automatically stores session cookie
5. **Authenticated Requests**: All subsequent requests include session cookie

### Configuration

**Sanctum Stateful Domains** (`config/sanctum.php`):
```php
'stateful' => [
    'localhost',
    'localhost:3000',
    '127.0.0.1:3000',
    // Production: your-nextjs-domain.com
],
```

**CORS Configuration** (`config/cors.php`):
```php
'allowed_origins' => [
    'http://localhost:3000',      // Next.js dev
    'http://127.0.0.1:3000',      // Next.js dev
    env('FRONTEND_URL'),           // Production
],
'supports_credentials' => true,    // CRITICAL for session cookies
```

### Next.js Implementation Example

```javascript
// lib/axios.js
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000',
  withCredentials: true, // CRITICAL: Send cookies with requests
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// lib/auth.js
export async function login(email, password) {
  // Step 1: Get CSRF cookie
  await api.get('/sanctum/csrf-cookie');

  // Step 2: Login (creates session)
  const response = await api.post('/api/v1/auth/login', {
    email,
    password,
    device_type: 'web' // IMPORTANT: Triggers session mode
  });

  return response.data.data.user; // No token returned
}

export async function register(userData) {
  // Step 1: Get CSRF cookie
  await api.get('/sanctum/csrf-cookie');

  // Step 2: Register (creates session)
  const response = await api.post('/api/v1/auth/register', {
    ...userData,
    device_type: 'web' // IMPORTANT: Triggers session mode
  });

  return response.data.data.user; // No token returned
}

export async function logout() {
  await api.post('/api/v1/auth/logout');
  // Session destroyed on server
}

export async function getCurrentUser() {
  const response = await api.get('/api/user');
  return response.data;
}
```

### Next.js API Route Example (Server-Side)

```javascript
// app/api/auth/login/route.js
import { cookies } from 'next/headers';

export async function POST(request) {
  const { email, password } = await request.json();

  // Get CSRF cookie
  await fetch(`${process.env.API_URL}/sanctum/csrf-cookie`, {
    credentials: 'include'
  });

  // Login
  const response = await fetch(`${process.env.API_URL}/api/v1/auth/login`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    credentials: 'include',
    body: JSON.stringify({ email, password, device_type: 'web' })
  });

  return Response.json(await response.json());
}
```

---

## 📱 Mode 2: Token-Based Authentication (React Native Mobile)

### How It Works

1. **Login Request**: Send credentials with `device_type: 'ios'` or `'android'`
2. **Token Generation**: Laravel generates Sanctum API token
3. **Token Storage**: Store token securely (SecureStore/Keychain)
4. **Authenticated Requests**: Include `Authorization: Bearer {token}` header

### React Native Implementation Example

```javascript
// services/api.js
import axios from 'axios';
import * as SecureStore from 'expo-secure-store';

const API_URL = 'http://localhost:8000';

// Create axios instance
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Add auth token to all requests
api.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

// services/auth.js
import api from './api';
import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

export async function login(email, password) {
  const response = await api.post('/api/v1/auth/login', {
    email,
    password,
    device_type: Platform.OS // 'ios' or 'android'
  });

  const { user, token } = response.data.data;

  // Store token securely
  await SecureStore.setItemAsync('auth_token', token);

  return user;
}

export async function register(userData) {
  const response = await api.post('/api/v1/auth/register', {
    ...userData,
    device_type: Platform.OS // 'ios' or 'android'
  });

  const { user, token } = response.data.data;

  // Store token securely
  await SecureStore.setItemAsync('auth_token', token);

  return user;
}

export async function logout() {
  await api.post('/api/v1/auth/logout');

  // Delete token from secure storage
  await SecureStore.deleteItemAsync('auth_token');
}

export async function getCurrentUser() {
  const response = await api.get('/api/user');
  return response.data;
}
```

---

## 🔌 Broadcasting (WebSockets) with Dual Auth

Laravel Reverb WebSocket server works with BOTH authentication modes automatically.

### Configuration

**Broadcasting Route** (`routes/api.php`):
```php
Route::post('/broadcasting/auth', [BroadcastingController::class, 'authenticate'])
    ->middleware('auth:sanctum'); // Handles both session and token auth
```

**Channel Authorization** (`routes/channels.php`):
```php
// Private chat channels
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);
    return $chat && $chat->users()->where('user_id', $user->id)->exists();
});

// User notification channels
Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

### Next.js WebSocket Example (Laravel Echo)

```javascript
// lib/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const echo = new Echo({
  broadcaster: 'reverb',
  key: process.env.NEXT_PUBLIC_REVERB_APP_KEY,
  wsHost: process.env.NEXT_PUBLIC_REVERB_HOST || 'localhost',
  wsPort: process.env.NEXT_PUBLIC_REVERB_PORT || 8080,
  wssPort: process.env.NEXT_PUBLIC_REVERB_PORT || 8080,
  forceTLS: process.env.NEXT_PUBLIC_REVERB_SCHEME === 'https',
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],

  // IMPORTANT: For session-based auth
  authorizer: (channel, options) => {
    return {
      authorize: (socketId, callback) => {
        axios.post('/api/v1/broadcasting/auth', {
          socket_id: socketId,
          channel_name: channel.name
        })
        .then(response => {
          callback(false, response.data);
        })
        .catch(error => {
          callback(true, error);
        });
      }
    };
  }
});

export default echo;

// Usage in component
function ChatComponent({ chatId }) {
  useEffect(() => {
    echo.private(`chat.${chatId}`)
      .listen('NewMessageEvent', (e) => {
        console.log('New message:', e.message);
      });

    return () => {
      echo.leave(`chat.${chatId}`);
    };
  }, [chatId]);
}
```

### React Native WebSocket Example (Laravel Echo)

```javascript
// services/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js/react-native';
import * as SecureStore from 'expo-secure-store';
import api from './api';

const echo = new Echo({
  broadcaster: 'reverb',
  key: 'yoryor-key-123456',
  wsHost: 'localhost',
  wsPort: 8080,
  wssPort: 8080,
  forceTLS: false,
  encrypted: true,
  disableStats: true,
  enabledTransports: ['ws', 'wss'],

  // IMPORTANT: For token-based auth
  authorizer: (channel, options) => {
    return {
      authorize: async (socketId, callback) => {
        try {
          const token = await SecureStore.getItemAsync('auth_token');

          const response = await api.post('/api/v1/broadcasting/auth', {
            socket_id: socketId,
            channel_name: channel.name
          }, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          callback(false, response.data);
        } catch (error) {
          callback(true, error);
        }
      }
    };
  }
});

export default echo;

// Usage in component
import echo from './services/echo';

function ChatScreen({ chatId }) {
  useEffect(() => {
    echo.private(`chat.${chatId}`)
      .listen('NewMessageEvent', (e) => {
        console.log('New message:', e.message);
      });

    return () => {
      echo.leave(`chat.${chatId}`);
    };
  }, [chatId]);
}
```

---

## 📊 API Endpoints Summary

### Authentication Endpoints

| Endpoint | Method | device_type | Response |
|----------|--------|-------------|----------|
| `/api/v1/auth/register` | POST | `web` | `{ user }` (session created) |
| `/api/v1/auth/register` | POST | `ios`/`android` | `{ user, token }` |
| `/api/v1/auth/login` | POST | `web` | `{ user }` (session created) |
| `/api/v1/auth/login` | POST | `ios`/`android` | `{ user, token }` |
| `/api/v1/auth/logout` | POST | any | Destroys session/token |
| `/api/user` | GET | any | Current authenticated user |

### Broadcasting Endpoints

| Endpoint | Method | Auth | Description |
|----------|--------|------|-------------|
| `/api/v1/broadcasting/auth` | POST | `auth:sanctum` | Authorize private channels |

---

## 🔒 Security Features

### Session-Based (Next.js)

✅ **CSRF Protection**: Required for all state-changing requests
✅ **SameSite Cookies**: Prevents CSRF attacks
✅ **Secure Cookies**: HTTPS only in production
✅ **HttpOnly Cookies**: JavaScript cannot access auth cookies
✅ **Domain Restricted**: Only works from stateful domains

### Token-Based (React Native)

✅ **Secure Storage**: Tokens stored in device Keychain/SecureStore
✅ **Token Expiration**: Configurable via Sanctum
✅ **Per-Device Tokens**: Each device has unique token
✅ **Token Revocation**: Individual tokens can be revoked
✅ **Multiple Devices**: User can be logged in on multiple devices

---

## 🚀 Production Deployment Checklist

### Environment Variables

**Laravel (.env):**
```env
# Update for production
FRONTEND_URL=https://your-nextjs-app.com
SANCTUM_STATEFUL_DOMAINS=your-nextjs-app.com
SESSION_DOMAIN=.your-domain.com
APP_URL=https://api.your-domain.com

# Update Reverb for production
REVERB_HOST=reverb.your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https

# Ensure session driver is appropriate
SESSION_DRIVER=redis  # or database for production
CACHE_DRIVER=redis    # for better performance
```

**Next.js (.env.local):**
```env
NEXT_PUBLIC_API_URL=https://api.your-domain.com
NEXT_PUBLIC_REVERB_APP_KEY=your-reverb-key
NEXT_PUBLIC_REVERB_HOST=reverb.your-domain.com
NEXT_PUBLIC_REVERB_PORT=443
NEXT_PUBLIC_REVERB_SCHEME=https
```

**React Native (.env):**
```env
API_URL=https://api.your-domain.com
REVERB_APP_KEY=your-reverb-key
REVERB_HOST=reverb.your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

### SSL/TLS Configuration

1. **Laravel API**: HTTPS with valid SSL certificate
2. **Next.js Frontend**: HTTPS with valid SSL certificate
3. **Reverb WebSocket**: WSS (secure WebSocket) with valid SSL certificate
4. **CORS Origins**: Update to production domains only

### Security Headers

Ensure the following headers are set (already configured in SecurityHeaders middleware):

- `Content-Security-Policy`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security` (HSTS)

---

## 🧪 Testing the Implementation

### Test Session-Based Auth (Next.js)

```bash
# 1. Get CSRF cookie
curl -X GET http://localhost:8000/sanctum/csrf-cookie \
  -H "Accept: application/json" \
  --cookie-jar cookies.txt

# 2. Login (session-based)
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Origin: http://localhost:3000" \
  --cookie cookies.txt \
  --cookie-jar cookies.txt \
  -d '{
    "email": "user@example.com",
    "password": "password",
    "device_type": "web"
  }'

# 3. Access protected route with session
curl -X GET http://localhost:8000/api/user \
  -H "Accept: application/json" \
  --cookie cookies.txt
```

### Test Token-Based Auth (React Native)

```bash
# 1. Login (token-based)
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password",
    "device_type": "ios"
  }'

# Response: { "status": "success", "data": { "user": {...}, "token": "1|abc..." } }

# 2. Access protected route with token
curl -X GET http://localhost:8000/api/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|abc..."
```

---

## 📚 Key Files Reference

### Configuration Files

- `config/sanctum.php` - Sanctum configuration (stateful domains)
- `config/cors.php` - CORS configuration (Next.js origins)
- `config/broadcasting.php` - Reverb WebSocket configuration
- `.env.example` - Environment variable template

### Controllers

- `app/Http/Controllers/Api/V1/AuthController.php` - Authentication endpoints
- `app/Http/Controllers/Api/V1/BroadcastingController.php` - WebSocket channel auth

### Services

- `app/Services/AuthService.php` - Authentication business logic

### Resources

- `app/Http/Resources/Api/V1/UserResource.php` - User response format

### Routes

- `routes/api.php` - API endpoints
- `routes/channels.php` - Broadcasting channel authorization

---

## ✅ Success Criteria Met

- [x] Dual authentication modes implemented (session + token)
- [x] Next.js session-based auth configured
- [x] React Native token-based auth configured
- [x] Broadcasting works with both auth modes
- [x] CORS properly configured
- [x] API resources return consistent data
- [x] Swagger documentation updated
- [x] Security features implemented
- [x] Code formatted with Laravel Pint
- [x] No syntax errors
- [x] Comprehensive documentation created

---

## 🎉 Ready for Next Steps

The Laravel API backend is now **fully configured** for both Next.js and React Native:

### Next Steps:

1. **Set up Next.js 15 Frontend**
   - Create Next.js project with App Router
   - Configure API client with session-based auth
   - Implement authentication pages
   - Set up Laravel Echo for WebSockets

2. **Set up React Native Mobile App**
   - Create Expo/React Native project
   - Configure API client with token-based auth
   - Implement authentication flows
   - Set up Laravel Echo for WebSockets

3. **Create Shared Components Library**
   - Extract common UI components
   - Share business logic hooks
   - Share utility functions
   - Achieve 60-80% code sharing

4. **Begin Livewire to React Migration**
   - Convert Livewire components to React
   - Test functionality parity
   - Remove Livewire dependencies

---

**Authentication is production-ready!** 🚀

Both Next.js (web) and React Native (mobile) can now authenticate with the Laravel API backend using their respective optimal authentication strategies.

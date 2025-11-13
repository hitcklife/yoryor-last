# YorYor API Authentication Documentation

## Table of Contents
- [Overview](#overview)
- [Authentication Methods](#authentication-methods)
- [Authentication Flows](#authentication-flows)
- [API Endpoints](#api-endpoints)
- [Token Management](#token-management)
- [Security Features](#security-features)
- [Code Examples](#code-examples)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

---

## Overview

YorYor uses **Laravel Sanctum** for token-based API authentication. The platform supports multiple authentication methods to provide flexibility and enhanced security for users.

### Key Features
- **Token-based authentication** using Laravel Sanctum
- **Multi-factor authentication** with OTP and 2FA support
- **Social authentication** via Google and Facebook
- **Session management** for web and mobile clients
- **Account lockout** after failed attempts
- **Secure password requirements** with bcrypt hashing

### Base URL
- **Production:** `https://api.yoryor.com/api/v1`
- **Development:** `http://localhost:8000/api/v1`

### Required Headers
```http
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  # For authenticated requests
```

---

## Authentication Methods

YorYor supports multiple authentication methods to accommodate different user preferences and security requirements:

### 1. Password-Based Authentication
Traditional email/phone + password login with strong password requirements.

**Features:**
- Bcrypt hashing with 12 rounds
- Minimum 8 characters required
- Password strength validation
- Account lockout after 5 failed attempts

### 2. OTP (One-Time Password) Authentication
Passwordless authentication using a 6-digit code sent to the user's phone.

**Features:**
- 6-digit numeric code
- 5-minute expiration window
- Rate limited (5 requests per hour)
- Automatic user creation on first login
- Secure code generation using `random_int()`

### 3. Two-Factor Authentication (2FA)
Additional security layer using TOTP (Time-based One-Time Password) with authenticator apps.

**Features:**
- TOTP-based verification
- QR code generation for easy setup
- 8 backup/recovery codes
- Compatible with Google Authenticator, Authy, Microsoft Authenticator
- Recovery process for lost devices

### 4. Social Authentication
OAuth-based authentication via third-party providers.

**Supported Providers:**
- Google OAuth 2.0
- Facebook Login

**Features:**
- Automatic account creation
- Email verification bypass
- Avatar import from provider
- Existing account linking

---

## Authentication Flows

### Flow 1: Password-Based Registration & Login

#### Registration Flow
```
1. User provides: email, password, name, date_of_birth, gender
2. System validates input (age 18+, strong password)
3. System creates user account (registration_completed: false)
4. System creates profile and preferences
5. System generates Sanctum token
6. User receives token + user data
7. User completes onboarding (photos, additional details)
```

#### Login Flow
```
1. User provides: email/phone + password
2. System validates credentials
3. System checks if account is disabled
4. System generates new Sanctum token
5. User receives token + user data
```

**Endpoints:**
- `POST /v1/auth/register` - Create new account
- `POST /v1/auth/login` - Login with credentials
- `POST /v1/auth/logout` - Revoke token

### Flow 2: OTP Authentication (Passwordless)

```
┌─────────────┐                    ┌─────────────┐                    ┌─────────────┐
│   Client    │                    │   Server    │                    │  SMS Gateway│
└──────┬──────┘                    └──────┬──────┘                    └──────┬──────┘
       │                                  │                                  │
       │  1. POST /v1/auth/authenticate   │                                  │
       │      { phone: "+1234567890" }    │                                  │
       ├─────────────────────────────────>│                                  │
       │                                  │                                  │
       │                                  │  2. Generate OTP (6 digits)      │
       │                                  │     Store in DB (expires: 5min)  │
       │                                  │                                  │
       │                                  │  3. Send OTP via SMS             │
       │                                  ├─────────────────────────────────>│
       │                                  │                                  │
       │                                  │  4. SMS delivered                │
       │                                  │<─────────────────────────────────┤
       │                                  │                                  │
       │  5. OTP sent successfully        │                                  │
       │<─────────────────────────────────┤                                  │
       │                                  │                                  │
       │  6. User enters OTP              │                                  │
       │                                  │                                  │
       │  7. POST /v1/auth/authenticate   │                                  │
       │      { phone: "+123...", otp: "123456" }                            │
       ├─────────────────────────────────>│                                  │
       │                                  │                                  │
       │                                  │  8. Verify OTP                   │
       │                                  │     Mark as used                 │
       │                                  │     Find/Create user             │
       │                                  │     Generate token               │
       │                                  │                                  │
       │  9. Token + User data            │                                  │
       │<─────────────────────────────────┤                                  │
       │                                  │                                  │
```

**Endpoints:**
- `POST /v1/auth/authenticate` - Request OTP or verify OTP
- `POST /v1/auth/complete-registration` - Complete profile setup

### Flow 3: Two-Factor Authentication

```
1. User logs in with email/password
2. System detects 2FA is enabled
3. System prompts for 2FA code
4. User provides code from authenticator app
5. System verifies TOTP code
6. System generates token
7. User is authenticated
```

**Endpoints:**
- `POST /v1/auth/2fa/enable` - Enable 2FA (returns QR code)
- `POST /v1/auth/2fa/verify` - Verify 2FA code during login
- `POST /v1/auth/2fa/disable` - Disable 2FA

### Flow 4: Social Authentication

```
1. User clicks "Login with Google/Facebook"
2. User redirected to OAuth provider
3. User authorizes YorYor access
4. Provider redirects back with token
5. System verifies token with provider
6. System finds or creates user account
7. System logs user in (session or token)
```

**Endpoints:**
- `GET /auth/{provider}/redirect` - Initiate OAuth flow
- `GET /auth/{provider}/callback` - Handle OAuth callback

---

## API Endpoints

### 1. Check Email Availability

Check if an email address is already registered.

**Endpoint:** `POST /v1/auth/check-email`

**Rate Limit:** 10 requests/minute

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response (Available):**
```json
{
  "status": "success",
  "data": {
    "is_taken": false,
    "available": true,
    "email": "user@example.com"
  }
}
```

**Response (Taken):**
```json
{
  "status": "success",
  "data": {
    "is_taken": true,
    "available": false,
    "email": "user@example.com"
  }
}
```

---

### 2. Register New User

Create a new user account with profile information.

**Endpoint:** `POST /v1/auth/register`

**Rate Limit:** 10 requests/minute

**Request:**
```json
{
  "email": "john.doe@example.com",
  "password": "SecureP@ss123",
  "first_name": "John",
  "last_name": "Doe",
  "date_of_birth": "1990-01-15",
  "gender": "male",
  "country": "United States",
  "is_private": false
}
```

**Password Requirements:**
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- Optional special character

**Response (201 Created):**
```json
{
  "status": "success",
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "email": "john.doe@example.com",
      "registration_completed": false,
      "profile": {
        "first_name": "John",
        "last_name": "Doe",
        "age": 35,
        "gender": "male"
      }
    },
    "token": "1|abc123def456..."
  }
}
```

---

### 3. Login with Email/Password

Authenticate user with email/phone and password.

**Endpoint:** `POST /v1/auth/login`

**Rate Limit:** 10 requests/minute (5 failed attempts = account lockout)

**Request (Email):**
```json
{
  "email": "john.doe@example.com",
  "password": "SecureP@ss123"
}
```

**Request (Phone):**
```json
{
  "phone": "+1234567890",
  "password": "SecureP@ss123"
}
```

**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "uuid": "550e8400-e29b-41d4-a716-446655440000",
      "email": "john.doe@example.com",
      "registration_completed": true,
      "profile": { /* profile data */ },
      "preference": { /* preferences */ }
    },
    "token": "1|abc123def456..."
  }
}
```

**Error Response (422 Unprocessable):**
```json
{
  "status": "error",
  "message": "Login failed",
  "errors": {
    "credentials": ["The provided credentials are incorrect."]
  }
}
```

---

### 4. OTP Authentication

Send OTP or verify OTP for passwordless login.

**Endpoint:** `POST /v1/auth/authenticate`

**Rate Limit:** 10 requests/minute

#### Step 1: Request OTP

**Request:**
```json
{
  "phone": "+1234567890"
}
```

**Response:**
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

#### Step 2: Verify OTP

**Request:**
```json
{
  "phone": "+1234567890",
  "otp": "123456"
}
```

**Response (New User):**
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
      "registration_completed": false
    },
    "token": "1|abc123def456..."
  }
}
```

**Response (Existing User):**
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
      "profile": { /* profile data */ },
      "photos": [ /* photo array */ ]
    },
    "token": "1|abc123def456..."
  }
}
```

---

### 5. Complete Registration (OTP Users)

Complete profile setup for users who registered via OTP.

**Endpoint:** `POST /v1/auth/complete-registration`

**Auth Required:** Yes (Bearer token)

**Rate Limit:** 10 requests/minute

**Request:**
```json
{
  "email": "john.doe@example.com",
  "firstName": "John",
  "lastName": "Doe",
  "dateOfBirth": "1990-01-15",
  "gender": "male",
  "age": 35,
  "bio": "Looking for meaningful connection",
  "interests": ["hiking", "photography", "cooking"],
  "profession": "Software Engineer",
  "occupation": "employee",
  "status": "single",
  "country": "United States",
  "countryCode": "US",
  "state": "New York",
  "city": "New York",
  "profile_private": false
}
```

**Response:**
```json
{
  "status": "success",
  "message": "Registration completed successfully",
  "data": {
    "user": {
      "id": 1,
      "email": "john.doe@example.com",
      "phone": "+1234567890",
      "registration_completed": true,
      "profile": { /* complete profile data */ },
      "photos": [ /* uploaded photos */ ]
    }
  }
}
```

---

### 6. Logout

Revoke the current authentication token.

**Endpoint:** `POST /v1/auth/logout`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Successfully logged out"
}
```

---

### 7. Enable Two-Factor Authentication

Enable 2FA for the authenticated user.

**Endpoint:** `POST /v1/auth/2fa/enable`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Two-factor authentication enabled successfully",
  "data": {
    "secret_key": "JBSWY3DPEHPK3PXP",
    "qr_code_url": "otpauth://totp/YorYor:user@example.com?secret=JBSWY3DPEHPK3PXP&issuer=YorYor",
    "backup_codes": [
      "K5M7N9P2Q4",
      "R6S8T1U3V5",
      "W7X9Y2Z4A6",
      "B8C1D3E5F7",
      "G9H2J4K6L8",
      "M1N3P5Q7R9",
      "S2T4U6V8W1",
      "X3Y5Z7A9B2"
    ]
  }
}
```

**Instructions for User:**
1. Scan QR code with authenticator app (Google Authenticator, Authy, etc.)
2. Save backup codes in a secure location
3. Use authenticator app to generate codes for future logins

---

### 8. Verify Two-Factor Code

Verify 2FA code during login or operations requiring additional security.

**Endpoint:** `POST /v1/auth/2fa/verify`

**Auth Required:** Yes

**Request:**
```json
{
  "code": "123456"
}
```

**Response (Valid Code):**
```json
{
  "status": "success",
  "message": "Two-factor authentication code verified successfully"
}
```

**Response (Invalid Code - 401 Unauthorized):**
```json
{
  "status": "error",
  "message": "Invalid two-factor authentication code",
  "error_code": "INVALID_CREDENTIALS"
}
```

---

### 9. Disable Two-Factor Authentication

Disable 2FA for the authenticated user.

**Endpoint:** `POST /v1/auth/2fa/disable`

**Auth Required:** Yes

**Response:**
```json
{
  "status": "success",
  "message": "Two-factor authentication disabled"
}
```

---

### 10. Social Authentication Endpoints

#### Initiate OAuth Flow

**Endpoint:** `GET /auth/{provider}/redirect`

**Supported Providers:** `google`, `facebook`

**Response:** Redirects to OAuth provider's authorization page

#### Handle OAuth Callback

**Endpoint:** `GET /auth/{provider}/callback`

**Response:** Redirects to dashboard or registration completion

---

## Token Management

### How to Obtain Tokens

Tokens are automatically generated and returned after successful authentication via:
- `POST /v1/auth/register`
- `POST /v1/auth/login`
- `POST /v1/auth/authenticate` (with valid OTP)

### Token Usage in Requests

Include the token in the `Authorization` header for all authenticated requests:

```http
GET /v1/profile/me HTTP/1.1
Host: api.yoryor.com
Authorization: Bearer 1|abc123def456...
Accept: application/json
Content-Type: application/json
```

### Token Storage Best Practices

**Mobile Apps (React Native/Expo):**
```javascript
import * as SecureStore from 'expo-secure-store';

// Store token securely
await SecureStore.setItemAsync('auth_token', token);

// Retrieve token
const token = await SecureStore.getItemAsync('auth_token');
```

**Web Apps:**
- **Recommended:** Use httpOnly cookies (Sanctum's stateful authentication)
- **Alternative:** Store in memory or sessionStorage (never localStorage for production)

### Token Lifecycle

- **Issuance:** Tokens issued upon successful authentication
- **Validity:** Tokens remain valid until explicitly revoked
- **Revocation:** Tokens revoked via logout endpoint
- **Expiration:** No automatic expiration (managed by logout or token deletion)

### Token Revocation

```http
POST /v1/auth/logout HTTP/1.1
Authorization: Bearer 1|abc123def456...
```

**Effect:** Deletes all tokens associated with the authenticated user.

### Multiple Device Support

Users can be logged in on multiple devices simultaneously. Each device receives its own token.

**View Active Sessions:**
```php
// Future endpoint: GET /v1/auth/sessions
// Returns list of active tokens/sessions
```

**Revoke Specific Session:**
```php
// Future endpoint: DELETE /v1/auth/sessions/{token_id}
// Revokes specific token
```

---

## Security Features

### 1. Password Security

**Hashing Algorithm:** Bcrypt with 12 rounds

**Password Requirements:**
```
✓ Minimum 8 characters
✓ At least one uppercase letter (A-Z)
✓ At least one lowercase letter (a-z)
✓ At least one number (0-9)
○ Special characters recommended but optional
```

**Validation Example:**
```php
// Using Laravel's StrongPassword rule
'password' => ['required', new StrongPassword()]
```

**Password Storage:**
```php
// Passwords are hashed using bcrypt
$hashedPassword = Hash::make($password);
// Stored in database, never in plain text
```

### 2. Rate Limiting

YorYor implements aggressive rate limiting to prevent brute-force attacks:

| Endpoint | Limit | Purpose |
|----------|-------|---------|
| `/auth/register` | 10/minute | Prevent spam accounts |
| `/auth/login` | 10/minute | Prevent brute-force |
| `/auth/authenticate` | 10/minute | Prevent OTP abuse |
| `/auth/check-email` | 10/minute | Prevent email enumeration |
| `/auth/2fa/verify` | 10/minute | Prevent 2FA brute-force |

**Rate Limit Headers:**
```http
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 9
X-RateLimit-Reset: 1640995200
```

**Rate Limit Exceeded (429 Too Many Requests):**
```json
{
  "status": "error",
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

### 3. Account Lockout

**Trigger:** 5 consecutive failed login attempts

**Duration:** Account temporarily locked (requires password reset or admin unlock)

**Response:**
```json
{
  "status": "error",
  "message": "Account locked due to multiple failed login attempts. Please reset your password or contact support.",
  "error_code": "ACCOUNT_LOCKED"
}
```

### 4. OTP Security

**Code Generation:**
- 6-digit numeric code
- Cryptographically secure random generation (`random_int()`)
- Single-use only (marked as used after verification)

**Expiration:**
- Valid for 5 minutes from generation
- Expired codes automatically rejected

**Storage:**
- Stored in `otp_codes` table with expiration timestamp
- Old unused codes deleted when new OTP requested

**Rate Limiting:**
- Maximum 10 OTP requests per minute per phone number
- Maximum 5 verification attempts before lockout

### 5. Two-Factor Authentication (2FA)

**TOTP Algorithm:**
- Time-based One-Time Password (RFC 6238)
- 30-second time step
- 6-digit code

**Setup Process:**
1. User enables 2FA
2. System generates secret key
3. System creates QR code for authenticator app
4. System generates 8 backup codes
5. Secret and backup codes encrypted in database

**Backup Codes:**
- 8 single-use recovery codes
- Used when authenticator app unavailable
- Automatically removed after use
- New codes can be regenerated

**Security Measures:**
- Secret keys encrypted at rest
- QR code displayed only once during setup
- Backup codes shown only once (user must save)

### 6. Session Security

**Web Sessions:**
- Secure flag enabled (HTTPS only in production)
- HttpOnly flag enabled (no JavaScript access)
- SameSite=Lax (CSRF protection)
- 2-hour default lifetime
- Session regenerated on login

**Configuration:**
```php
// config/session.php
'lifetime' => 120, // minutes
'expire_on_close' => false,
'encrypt' => false,
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

### 7. HTTPS Enforcement

**Production Environment:**
- All API requests require HTTPS
- HTTP requests automatically redirected to HTTPS
- HSTS (HTTP Strict Transport Security) header enabled

**Security Headers:**
```http
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Content-Security-Policy: default-src 'self'
```

---

## Code Examples

### JavaScript (Fetch API)

#### Register User
```javascript
const registerUser = async (userData) => {
  try {
    const response = await fetch('https://api.yoryor.com/api/v1/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        email: userData.email,
        password: userData.password,
        first_name: userData.firstName,
        last_name: userData.lastName,
        date_of_birth: userData.dateOfBirth,
        gender: userData.gender,
      }),
    });

    const data = await response.json();

    if (response.ok) {
      // Store token securely
      localStorage.setItem('auth_token', data.data.token); // Use SecureStore for mobile
      return { success: true, user: data.data.user };
    } else {
      return { success: false, errors: data.errors };
    }
  } catch (error) {
    console.error('Registration error:', error);
    return { success: false, error: 'Network error' };
  }
};
```

#### Login User
```javascript
const loginUser = async (email, password) => {
  try {
    const response = await fetch('https://api.yoryor.com/api/v1/auth/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (response.ok) {
      // Store token securely
      localStorage.setItem('auth_token', data.data.token);
      return { success: true, user: data.data.user, token: data.data.token };
    } else {
      return { success: false, message: data.message, errors: data.errors };
    }
  } catch (error) {
    console.error('Login error:', error);
    return { success: false, error: 'Network error' };
  }
};
```

#### Authenticated Request
```javascript
const getProfile = async () => {
  const token = localStorage.getItem('auth_token');

  try {
    const response = await fetch('https://api.yoryor.com/api/v1/profile/me', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`,
      },
    });

    const data = await response.json();

    if (response.ok) {
      return { success: true, profile: data };
    } else if (response.status === 401) {
      // Token expired or invalid - redirect to login
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    } else {
      return { success: false, error: data.message };
    }
  } catch (error) {
    console.error('Profile fetch error:', error);
    return { success: false, error: 'Network error' };
  }
};
```

#### OTP Authentication
```javascript
// Step 1: Request OTP
const requestOTP = async (phoneNumber) => {
  try {
    const response = await fetch('https://api.yoryor.com/api/v1/auth/authenticate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ phone: phoneNumber }),
    });

    const data = await response.json();
    return { success: response.ok, data };
  } catch (error) {
    return { success: false, error: 'Network error' };
  }
};

// Step 2: Verify OTP
const verifyOTP = async (phoneNumber, otpCode) => {
  try {
    const response = await fetch('https://api.yoryor.com/api/v1/auth/authenticate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        phone: phoneNumber,
        otp: otpCode
      }),
    });

    const data = await response.json();

    if (response.ok && data.data.authenticated) {
      // Store token
      localStorage.setItem('auth_token', data.data.token);
      return {
        success: true,
        user: data.data.user,
        needsRegistration: !data.data.registration_completed
      };
    } else {
      return { success: false, error: data.message };
    }
  } catch (error) {
    return { success: false, error: 'Network error' };
  }
};
```

---

### React Native / Expo

#### Setup (Install Dependencies)
```bash
npm install expo-secure-store axios
```

#### Auth Service
```javascript
import * as SecureStore from 'expo-secure-store';
import axios from 'axios';

const API_URL = 'https://api.yoryor.com/api/v1';

// Axios instance with interceptors
const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add token to requests
api.interceptors.request.use(
  async (config) => {
    const token = await SecureStore.getItemAsync('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Handle 401 responses
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Token expired - clear and redirect to login
      await SecureStore.deleteItemAsync('auth_token');
      // Navigate to login screen
    }
    return Promise.reject(error);
  }
);

// Authentication functions
export const AuthService = {
  // Register
  register: async (userData) => {
    try {
      const response = await api.post('/auth/register', userData);
      const { token, user } = response.data.data;

      // Store token securely
      await SecureStore.setItemAsync('auth_token', token);

      return { success: true, user, token };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors
      };
    }
  },

  // Login
  login: async (email, password) => {
    try {
      const response = await api.post('/auth/login', { email, password });
      const { token, user } = response.data.data;

      await SecureStore.setItemAsync('auth_token', token);

      return { success: true, user, token };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed'
      };
    }
  },

  // OTP Request
  requestOTP: async (phoneNumber) => {
    try {
      const response = await api.post('/auth/authenticate', {
        phone: phoneNumber
      });
      return { success: true, data: response.data.data };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to send OTP'
      };
    }
  },

  // OTP Verify
  verifyOTP: async (phoneNumber, otpCode) => {
    try {
      const response = await api.post('/auth/authenticate', {
        phone: phoneNumber,
        otp: otpCode
      });

      const { token, user, registration_completed } = response.data.data;

      await SecureStore.setItemAsync('auth_token', token);

      return {
        success: true,
        user,
        token,
        needsRegistration: !registration_completed
      };
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Invalid OTP'
      };
    }
  },

  // Logout
  logout: async () => {
    try {
      await api.post('/auth/logout');
      await SecureStore.deleteItemAsync('auth_token');
      return { success: true };
    } catch (error) {
      // Still delete token even if request fails
      await SecureStore.deleteItemAsync('auth_token');
      return { success: true };
    }
  },

  // Get stored token
  getToken: async () => {
    return await SecureStore.getItemAsync('auth_token');
  },

  // Check if user is authenticated
  isAuthenticated: async () => {
    const token = await SecureStore.getItemAsync('auth_token');
    return !!token;
  },
};
```

#### Usage in Component
```javascript
import React, { useState } from 'react';
import { View, TextInput, Button, Alert } from 'react-native';
import { AuthService } from './services/AuthService';

const LoginScreen = ({ navigation }) => {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async () => {
    setLoading(true);

    const result = await AuthService.login(email, password);

    setLoading(false);

    if (result.success) {
      // Navigate to home screen
      navigation.navigate('Home');
    } else {
      Alert.alert('Login Failed', result.error);
    }
  };

  return (
    <View>
      <TextInput
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        autoCapitalize="none"
        keyboardType="email-address"
      />
      <TextInput
        placeholder="Password"
        value={password}
        onChangeText={setPassword}
        secureTextEntry
      />
      <Button
        title={loading ? 'Logging in...' : 'Login'}
        onPress={handleLogin}
        disabled={loading}
      />
    </View>
  );
};

export default LoginScreen;
```

---

### cURL Examples

#### Register User
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "SecureP@ss123",
    "first_name": "John",
    "last_name": "Doe",
    "date_of_birth": "1990-01-15",
    "gender": "male",
    "country": "United States"
  }'
```

#### Login User
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "password": "SecureP@ss123"
  }'
```

#### Authenticated Request
```bash
curl -X GET https://api.yoryor.com/api/v1/profile/me \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

#### Request OTP
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/authenticate \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "+1234567890"
  }'
```

#### Verify OTP
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/authenticate \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "phone": "+1234567890",
    "otp": "123456"
  }'
```

#### Enable 2FA
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/2fa/enable \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

#### Verify 2FA Code
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/2fa/verify \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code": "123456"
  }'
```

#### Logout
```bash
curl -X POST https://api.yoryor.com/api/v1/auth/logout \
  -H "Authorization: Bearer 1|abc123def456..." \
  -H "Accept: application/json"
```

---

## Error Handling

### Standard Error Response Format

All API errors follow a consistent format:

```json
{
  "status": "error",
  "message": "Human-readable error message",
  "error_code": "MACHINE_READABLE_CODE",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

### Common HTTP Status Codes

| Status Code | Meaning | When Used |
|-------------|---------|-----------|
| 200 | OK | Request successful |
| 201 | Created | Resource created (registration) |
| 400 | Bad Request | Malformed request |
| 401 | Unauthorized | Invalid credentials or token |
| 403 | Forbidden | Account disabled or locked |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server-side error |

### Error Codes Reference

#### Authentication Errors

| Error Code | Status | Description | Resolution |
|------------|--------|-------------|------------|
| `VALIDATION_ERROR` | 422 | Input validation failed | Check errors object for field-specific issues |
| `INVALID_CREDENTIALS` | 401 | Wrong email/password or OTP | Verify credentials and retry |
| `ACCOUNT_LOCKED` | 403 | Too many failed attempts | Reset password or contact support |
| `ACCOUNT_DISABLED` | 403 | Account administratively disabled | Contact support |
| `ALREADY_ENABLED` | 409 | 2FA already enabled | No action needed |
| `EMAIL_CHECK_FAILED` | 500 | Email availability check failed | Retry request |
| `FORBIDDEN` | 403 | Registration already completed | User already onboarded |

### Validation Error Examples

#### Missing Required Fields
```json
{
  "status": "error",
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

#### Invalid Email Format
```json
{
  "status": "error",
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "email": ["The email must be a valid email address."]
  }
}
```

#### Weak Password
```json
{
  "status": "error",
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "password": [
      "The password must be at least 8 characters.",
      "The password must contain at least one uppercase letter.",
      "The password must contain at least one number."
    ]
  }
}
```

#### Age Restriction
```json
{
  "status": "error",
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "date_of_birth": ["You must be at least 18 years old to register."]
  }
}
```

#### Email Already Taken
```json
{
  "status": "error",
  "message": "Validation failed",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

### Authentication Error Examples

#### Invalid Login Credentials
```json
{
  "status": "error",
  "message": "Login failed",
  "errors": {
    "credentials": ["The provided credentials are incorrect."]
  }
}
```

#### Invalid OTP
```json
{
  "status": "error",
  "message": "Invalid credentials",
  "error_code": "INVALID_CREDENTIALS"
}
```

#### Expired OTP
```json
{
  "status": "error",
  "message": "OTP verification failed",
  "errors": {
    "otp": ["The OTP is invalid or expired."]
  }
}
```

#### Account Locked
```json
{
  "status": "error",
  "message": "Account locked due to multiple failed login attempts. Please reset your password or contact support.",
  "error_code": "ACCOUNT_LOCKED"
}
```

#### Invalid Token (Unauthorized)
```json
{
  "status": "error",
  "message": "Unauthenticated"
}
```

### Rate Limit Error

```json
{
  "status": "error",
  "message": "Too many requests. Please try again later.",
  "retry_after": 60
}
```

**Headers:**
```http
X-RateLimit-Limit: 10
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1640995260
Retry-After: 60
```

### Error Handling Best Practices

#### 1. Check Status Code First
```javascript
if (response.status === 401) {
  // Token expired - redirect to login
} else if (response.status === 422) {
  // Validation error - show field errors
} else if (response.status === 429) {
  // Rate limited - wait and retry
}
```

#### 2. Display User-Friendly Messages
```javascript
const getErrorMessage = (errorResponse) => {
  if (errorResponse.error_code === 'ACCOUNT_LOCKED') {
    return 'Your account has been locked. Please reset your password.';
  }

  if (errorResponse.errors) {
    // Extract first error message
    const firstError = Object.values(errorResponse.errors)[0];
    return Array.isArray(firstError) ? firstError[0] : firstError;
  }

  return errorResponse.message || 'An unexpected error occurred.';
};
```

#### 3. Handle Network Errors
```javascript
try {
  const response = await fetch(url, options);
  // Handle response
} catch (error) {
  if (error.name === 'TypeError') {
    // Network error
    showError('Network error. Please check your connection.');
  } else {
    showError('An unexpected error occurred.');
  }
}
```

#### 4. Retry Logic for Rate Limits
```javascript
const retryRequest = async (url, options, maxRetries = 3) => {
  for (let i = 0; i < maxRetries; i++) {
    const response = await fetch(url, options);

    if (response.status === 429) {
      const retryAfter = response.headers.get('Retry-After') || 60;
      await sleep(retryAfter * 1000);
      continue;
    }

    return response;
  }

  throw new Error('Max retries exceeded');
};
```

---

## Best Practices

### 1. Token Security

**DO:**
- ✅ Store tokens in secure storage (mobile: SecureStore, web: httpOnly cookies)
- ✅ Use HTTPS for all API requests
- ✅ Implement token refresh mechanism
- ✅ Clear tokens on logout
- ✅ Include token in `Authorization` header

**DON'T:**
- ❌ Store tokens in localStorage (web)
- ❌ Log tokens to console in production
- ❌ Share tokens between users
- ❌ Include tokens in URLs or query parameters

### 2. Password Management

**DO:**
- ✅ Enforce strong password requirements
- ✅ Use bcrypt or similar for hashing
- ✅ Implement password reset flow
- ✅ Show password strength indicator
- ✅ Allow password managers to work

**DON'T:**
- ❌ Store passwords in plain text
- ❌ Log passwords (even hashed)
- ❌ Email passwords to users
- ❌ Impose maximum length restrictions below 128 characters

### 3. OTP Implementation

**DO:**
- ✅ Use cryptographically secure random generation
- ✅ Implement proper expiration
- ✅ Rate limit OTP requests
- ✅ Log OTP generation for audit trails
- ✅ Use SMS/email delivery for production

**DON'T:**
- ❌ Return OTP in API response (except development)
- ❌ Allow OTP reuse
- ❌ Use predictable OTP codes
- ❌ Store OTPs in plain text

### 4. Error Handling

**DO:**
- ✅ Provide clear, actionable error messages
- ✅ Log errors server-side
- ✅ Handle network failures gracefully
- ✅ Implement retry logic for transient errors
- ✅ Validate input client-side before submission

**DON'T:**
- ❌ Expose internal error details to clients
- ❌ Ignore error responses
- ❌ Show technical error messages to users
- ❌ Retry non-idempotent operations automatically

### 5. User Experience

**DO:**
- ✅ Show loading indicators during authentication
- ✅ Provide "Forgot Password" option
- ✅ Support biometric authentication (mobile)
- ✅ Remember user's email/username
- ✅ Auto-dismiss keyboard after submission

**DON'T:**
- ❌ Block UI unnecessarily
- ❌ Auto-logout on minor errors
- ❌ Force password changes too frequently
- ❌ Disable paste in password fields

### 6. Session Management

**DO:**
- ✅ Implement session timeout warnings
- ✅ Refresh tokens before expiration
- ✅ Allow users to view active sessions
- ✅ Provide "Logout All Devices" option
- ✅ Track last login time and location

**DON'T:**
- ❌ Keep sessions active indefinitely
- ❌ Share sessions across different apps
- ❌ Allow concurrent logins without notification

### 7. Multi-Factor Authentication

**DO:**
- ✅ Encourage (but don't force) 2FA adoption
- ✅ Provide backup codes during setup
- ✅ Allow recovery options for lost devices
- ✅ Support multiple authenticator apps
- ✅ Verify 2FA code during setup

**DON'T:**
- ❌ Lock users out without recovery options
- ❌ Store 2FA secrets unencrypted
- ❌ Allow 2FA bypass without proper verification
- ❌ Reuse recovery codes

### 8. API Integration

**DO:**
- ✅ Implement exponential backoff for retries
- ✅ Cache non-sensitive data appropriately
- ✅ Use API versioning (v1, v2, etc.)
- ✅ Monitor API usage and errors
- ✅ Handle rate limits gracefully

**DON'T:**
- ❌ Make unnecessary API calls
- ❌ Ignore API version deprecation notices
- ❌ Hardcode API URLs
- ❌ Skip error handling for "unlikely" scenarios

### 9. Testing

**DO:**
- ✅ Test authentication flows end-to-end
- ✅ Test error scenarios (invalid credentials, network errors)
- ✅ Test rate limiting behavior
- ✅ Test token expiration and refresh
- ✅ Test on actual devices (not just simulators)

**DON'T:**
- ❌ Test only the "happy path"
- ❌ Use production credentials in tests
- ❌ Skip security testing
- ❌ Ignore edge cases

### 10. Compliance

**DO:**
- ✅ Comply with GDPR, CCPA, and local regulations
- ✅ Implement proper consent mechanisms
- ✅ Provide data export functionality
- ✅ Allow account deletion
- ✅ Maintain audit logs

**DON'T:**
- ❌ Collect unnecessary personal data
- ❌ Share user data without consent
- ❌ Ignore privacy laws
- ❌ Make account deletion difficult

---

## Additional Resources

### Related Documentation
- [API Endpoints Reference](/docs/api/ENDPOINTS.md)
- [Security Architecture](/documentation/SECURITY.md)
- [Rate Limiting Guide](/docs/RATE_LIMITING.md)
- [Error Codes Reference](/docs/ERROR_CODES.md)

### External Resources
- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [OAuth 2.0 Specification](https://oauth.net/2/)
- [TOTP RFC 6238](https://tools.ietf.org/html/rfc6238)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

### Support
- **Email:** support@yoryor.com
- **Security Issues:** security@yoryor.com
- **Documentation:** https://docs.yoryor.com

---

**Last Updated:** October 2025
**API Version:** v1.0.0
**Document Version:** 1.0.0

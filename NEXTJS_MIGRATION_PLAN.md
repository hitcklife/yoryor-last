# YorYor: Next.js + React Native Migration Plan

**Architecture:** Laravel 12 API Backend + Next.js 15 Web + React Native Mobile
**Status:** Planning Phase
**Updated:** 2025-11-13
**Laravel Version:** 12.x
**Next.js Version:** 15.x (App Router, React 19)

---

## 🎯 Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                 Laravel 12 API Backend                   │
│  ┌───────────────────────────────────────────────────┐  │
│  │ RESTful API (Sanctum Auth)                        │  │
│  │ ├── /api/v1/auth/* (login, register, logout)     │  │
│  │ ├── /api/v1/matches/* (discover, like, pass)     │  │
│  │ ├── /api/v1/messages/* (chats, send, read)       │  │
│  │ ├── /api/v1/profile/* (get, update, photos)      │  │
│  │ └── ... (68+ endpoints)                           │  │
│  └───────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────┐  │
│  │ WebSocket Server (Laravel Reverb)                 │  │
│  │ ├── Private channels (chat.{id}, user.{id})      │  │
│  │ ├── Broadcasting events (NewMessage, MatchFound) │  │
│  │ └── Real-time presence                            │  │
│  └───────────────────────────────────────────────────┘  │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Database, Cache, Queue, Storage (Unchanged)       │  │
│  └───────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                            │
                ┌───────────┴───────────┐
                ↓                       ↓
┌────────────────────────┐  ┌────────────────────────┐
│  Next.js 15 Web App    │  │ React Native Mobile    │
│  (Vercel/Self-hosted)  │  │ (iOS + Android)        │
│  ┌──────────────────┐  │  │  ┌──────────────────┐  │
│  │ App Router       │  │  │  │ Expo/React Native│  │
│  │ React 19 (RSC)   │  │  │  │ React Navigation │  │
│  │ TypeScript       │  │  │  │ TypeScript       │  │
│  │ Tailwind CSS     │  │  │  │ NativeWind       │  │
│  │ Laravel Echo     │  │  │  │ Laravel Echo     │  │
│  └──────────────────┘  │  │  └──────────────────┘  │
│         │               │  │         │              │
│  ┌──────────────────┐  │  │  ┌──────────────────┐  │
│  │ Shared Package   │←─┼──┼─→│ Shared Package   │  │
│  │ (Components,     │  │  │  │ (Components,     │  │
│  │  Hooks, Utils,   │  │  │  │  Hooks, Utils,   │  │
│  │  API Client)     │  │  │  │  API Client)     │  │
│  └──────────────────┘  │  │  └──────────────────┘  │
└────────────────────────┘  └────────────────────────┘
```

**Code Sharing:** 60-80% between web and mobile using shared packages

---

## 📊 Why Next.js + React Native > Inertia.js

### Inertia.js Limitations
❌ **Web-only** - No mobile story
❌ **Cannot share code** with mobile apps
❌ **Requires Laravel** for every page load
❌ **No static export** for CDN/edge deployment
❌ **Limited ecosystem** compared to Next.js

### Next.js + React Native Benefits
✅ **Cross-platform** - Share 60-80% code between web & mobile
✅ **API-first** - Frontend decoupled from backend
✅ **Better performance** - Static generation, ISR, RSC
✅ **SEO optimized** - Server-side rendering
✅ **Industry standard** - Easier hiring, massive ecosystem
✅ **Future-proof** - Mobile + web from day one
✅ **Deploy anywhere** - Vercel, Netlify, AWS, self-hosted

---

## 🏗️ Phase 1: Laravel API Backend Setup

### 1.1 Install API & Sanctum
```bash
# Laravel 12 API setup
php artisan install:api
```

**What this does:**
- Installs Laravel Sanctum
- Creates `routes/api.php`
- Adds `HasApiTokens` trait
- Configures Sanctum middleware

### 1.2 Configure Sanctum for SPA + Mobile

**config/sanctum.php**
```php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:3000,::1',
        Str::startsWith(env('APP_URL'), 'https://') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : '',
        env('SANCTUM_STATEFUL_DOMAINS') ? ','.env('SANCTUM_STATEFUL_DOMAINS') : ''
    ))),

    'guard' => ['web'],

    'expiration' => null, // Mobile tokens don't expire (or set 60 * 24 * 30 for 30 days)

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],
];
```

### 1.3 Configure CORS for Next.js

**config/cors.php**
```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',      // Next.js dev
        'http://127.0.0.1:3000',      // Next.js dev
        env('FRONTEND_URL'),           // Production Next.js URL
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // CRITICAL for Sanctum SPA auth
];
```

**.env additions:**
```env
# Frontend URLs
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000

# Session domain for SPA auth (Next.js web)
SESSION_DOMAIN=localhost

# API prefix
API_PREFIX=/api/v1
```

### 1.4 Update User Model

**app/Models/User.php**
```php
<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens; // CRITICAL for Sanctum

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'gender', 'date_of_birth',
        'registration_completed', 'last_active_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'last_active_at' => 'datetime',
        'registration_completed' => 'boolean',
    ];

    // Relationships
    public function profile() {
        return $this->hasOne(Profile::class);
    }

    public function photos() {
        return $this->hasMany(UserPhoto::class);
    }

    public function matches() {
        return $this->belongsToMany(User::class, 'matches', 'user_id', 'matched_user_id')
            ->withTimestamps();
    }

    // ... other relationships
}
```

---

## 📋 Phase 2: API Routes Structure

### 2.1 API Route Organization

**routes/api.php** (Generated by `install:api`)
```php
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1;

// Public routes
Route::prefix('v1')->group(function () {

    // Authentication (public)
    Route::prefix('auth')->controller(V1\AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/otp/send', 'sendOtp');
        Route::post('/otp/verify', 'verifyOtp');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'resetPassword');

        // Social auth
        Route::get('/google', 'redirectToGoogle');
        Route::get('/google/callback', 'handleGoogleCallback');
    });
});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Auth user
    Route::prefix('auth')->controller(V1\AuthController::class)->group(function () {
        Route::get('/user', 'user');
        Route::post('/logout', 'logout');
        Route::post('/2fa/enable', 'enableTwoFactor');
        Route::post('/2fa/verify', 'verifyTwoFactor');
    });

    // Profile
    Route::prefix('profile')->controller(V1\ProfileController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
        Route::post('/photos', 'uploadPhoto');
        Route::delete('/photos/{photo}', 'deletePhoto');
        Route::put('/photos/{photo}/primary', 'setPrimaryPhoto');

        // Extended profiles
        Route::put('/cultural', 'updateCultural');
        Route::put('/career', 'updateCareer');
        Route::put('/family', 'updateFamily');
        Route::put('/lifestyle', 'updateLifestyle');
        Route::put('/location', 'updateLocation');
        Route::put('/preferences', 'updatePreferences');
    });

    // Discovery & Matching
    Route::prefix('discover')->controller(V1\DiscoveryController::class)->group(function () {
        Route::get('/', 'index');              // Get discovery cards
        Route::post('/{user}/like', 'like');    // Like user
        Route::post('/{user}/pass', 'pass');    // Pass on user
        Route::post('/{user}/super-like', 'superLike');
    });

    // Matches
    Route::prefix('matches')->controller(V1\MatchController::class)->group(function () {
        Route::get('/', 'index');              // Get all matches
        Route::get('/{match}', 'show');        // Get single match
        Route::delete('/{match}', 'unmatch');  // Unmatch
    });

    // Likes
    Route::prefix('likes')->controller(V1\LikeController::class)->group(function () {
        Route::get('/sent', 'sentLikes');      // Likes I sent
        Route::get('/received', 'receivedLikes'); // Likes I received
    });

    // Messages & Chat
    Route::prefix('messages')->controller(V1\MessageController::class)->group(function () {
        Route::get('/', 'index');              // Get all chats
        Route::get('/{chat}', 'show');         // Get chat messages
        Route::post('/{chat}', 'send');        // Send message
        Route::put('/{message}', 'update');    // Edit message
        Route::delete('/{message}', 'destroy'); // Delete message
        Route::post('/{chat}/read', 'markAsRead'); // Mark as read
        Route::post('/{chat}/typing', 'typing'); // Typing indicator
    });

    // Notifications
    Route::prefix('notifications')->controller(V1\NotificationController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/{notification}/read', 'markAsRead');
        Route::post('/read-all', 'markAllAsRead');
        Route::delete('/{notification}', 'destroy');
    });

    // Settings
    Route::prefix('settings')->controller(V1\SettingController::class)->group(function () {
        Route::get('/', 'show');
        Route::put('/preferences', 'updatePreferences');
        Route::put('/privacy', 'updatePrivacy');
        Route::put('/notifications', 'updateNotifications');
        Route::post('/change-password', 'changePassword');
        Route::post('/change-email', 'changeEmail');
        Route::post('/deactivate', 'deactivateAccount');
        Route::delete('/account', 'deleteAccount');
    });

    // Subscription
    Route::prefix('subscription')->controller(V1\SubscriptionController::class)->group(function () {
        Route::get('/', 'current');
        Route::post('/subscribe', 'subscribe');
        Route::post('/cancel', 'cancel');
        Route::get('/plans', 'plans');
    });

    // Verification
    Route::prefix('verification')->controller(V1\VerificationController::class)->group(function () {
        Route::get('/', 'status');
        Route::post('/submit', 'submit');
        Route::post('/documents', 'uploadDocuments');
    });

    // Video Calls
    Route::prefix('calls')->controller(V1\VideoCallController::class)->group(function () {
        Route::post('/initiate', 'initiate');
        Route::post('/{call}/join', 'join');
        Route::post('/{call}/end', 'end');
        Route::get('/{call}/token', 'getToken');
    });

    // Search
    Route::get('/search', [V1\SearchController::class, 'search']);

    // Blocking & Reporting
    Route::post('/users/{user}/block', [V1\BlockController::class, 'block']);
    Route::delete('/users/{user}/unblock', [V1\BlockController::class, 'unblock']);
    Route::get('/blocked-users', [V1\BlockController::class, 'index']);

    Route::post('/users/{user}/report', [V1\ReportController::class, 'report']);

    // Panic Button
    Route::post('/panic', [V1\PanicButtonController::class, 'trigger']);

    // Device Tokens (Push Notifications)
    Route::post('/device-tokens', [V1\DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens/{token}', [V1\DeviceTokenController::class, 'destroy']);
});

// Admin routes (separate middleware)
Route::prefix('v1/admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/dashboard', [V1\Admin\DashboardController::class, 'index']);
    Route::apiResource('/users', V1\Admin\UserController::class);
    Route::apiResource('/reports', V1\Admin\ReportController::class);
    Route::apiResource('/verifications', V1\Admin\VerificationController::class);
    // ... more admin routes
});
```

### 2.2 API Versioning Strategy

- **Current version:** `/api/v1/*`
- **Future versions:** `/api/v2/*` (maintain backwards compatibility)
- **Version in URL**, not headers (easier for mobile apps)

---

## 🔐 Phase 3: Authentication Strategy

### 3.1 Two Authentication Modes

**Mode 1: SPA Authentication (Next.js Web)**
- Uses **session cookies** (stateful)
- CSRF protection enabled
- Works like traditional Laravel auth
- Flow:
  1. Next.js calls `/sanctum/csrf-cookie`
  2. Laravel returns CSRF token in cookie
  3. Next.js sends login request with CSRF token
  4. Laravel creates session
  5. Subsequent requests authenticated via session cookie

**Mode 2: Token Authentication (React Native Mobile)**
- Uses **API tokens** (stateless)
- No CSRF needed
- Tokens stored in secure storage
- Flow:
  1. Mobile app sends login credentials
  2. Laravel returns API token
  3. Mobile stores token securely
  4. All requests include `Authorization: Bearer {token}` header

### 3.2 Auth Controller Example

**app/Http/Controllers/Api/V1/AuthController.php**
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Register new user
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:18 years ago',
            'device_type' => 'nullable|in:web,ios,android', // Track platform
        ]);

        $result = $this->authService->register($validated);

        // For mobile: return token
        if ($request->input('device_type') !== 'web') {
            $token = $result['user']->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'user' => new UserResource($result['user']),
                'token' => $token,
            ], 201);
        }

        // For web: return user (session handles auth)
        return response()->json([
            'user' => new UserResource($result['user']),
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_type' => 'nullable|in:web,ios,android',
        ]);

        $result = $this->authService->login($validated);

        if (!$result['success']) {
            return response()->json([
                'message' => $result['message']
            ], 401);
        }

        // For mobile: return token
        if ($request->input('device_type') !== 'web') {
            $token = $result['user']->createToken('mobile-app')->plainTextToken;

            return response()->json([
                'user' => new UserResource($result['user']),
                'token' => $token,
            ]);
        }

        // For web: create session
        Auth::login($result['user']);

        return response()->json([
            'user' => new UserResource($result['user']),
        ]);
    }

    /**
     * Get authenticated user
     */
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        // For mobile: revoke token
        if ($request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        // For web: destroy session
        Auth::guard('web')->logout();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}
```

---

## 📦 Phase 4: API Resources (JSON:API Format)

### 4.1 Create API Resources for All Models

```bash
# Generate resources
php artisan make:resource UserResource
php artisan make:resource ProfileResource
php artisan make:resource MessageResource
php artisan make:resource MatchResource
php artisan make:resource ChatResource
# ... etc for all 55+ models
```

### 4.2 Example Resource

**app/Http/Resources/UserResource.php**
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'gender' => $this->gender,
            'age' => $this->age,
            'profile_photo' => $this->profile?->profile_photo,
            'bio' => $this->profile?->bio,
            'is_verified' => $this->is_verified,
            'is_online' => $this->is_online,
            'last_active_at' => $this->last_active_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),

            // Conditionally include relationships
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'photos' => UserPhotoResource::collection($this->whenLoaded('photos')),
            'preferences' => new PreferencesResource($this->whenLoaded('preferences')),
        ];
    }
}
```

### 4.3 Collection Resources with Pagination

**app/Http/Resources/UserCollection.php**
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }
}
```

---

## 📡 Phase 5: Broadcasting for Next.js WebSocket Clients

### 5.1 Laravel Reverb Configuration

Laravel Reverb already installed. Configure for Next.js clients:

**config/broadcasting.php** (Already configured)
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST', '0.0.0.0'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
    ],
],
```

### 5.2 Channel Authorization for Next.js

**routes/channels.php**
```php
<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Chat;

// Private user channel (for personal notifications)
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    return (int) $user->id === (int) $userId;
});

// Private chat channel (only participants can join)
Broadcast::channel('chat.{chatId}', function (User $user, int $chatId) {
    $chat = Chat::find($chatId);
    return $chat && $chat->users()->where('user_id', $user->id)->exists();
});

// Presence channel (online users)
Broadcast::channel('online', function (User $user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'photo' => $user->profile?->profile_photo,
    ];
});
```

### 5.3 Broadcasting Authentication Endpoint

**routes/api.php** (Add this)
```php
// Broadcasting authentication (for Next.js and React Native)
Route::post('/broadcasting/auth', function (Request $request) {
    return Broadcast::auth($request);
})->middleware('auth:sanctum');
```

### 5.4 Event Broadcasting Example

**app/Events/NewMessageEvent.php**
```php
<?php

namespace App\Events;

use App\Models\Message;
use App\Http\Resources\MessageResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.new';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => new MessageResource($this->message),
        ];
    }
}
```

---

## 🎨 Phase 6: Next.js 15 Setup (Separate Phase)

Will be created in separate repository or monorepo structure:

```
yoryor-app/                          # Root monorepo
├── backend/                         # Laravel API (current repo)
│   ├── app/
│   ├── routes/
│   ├── database/
│   └── ...
│
├── frontend/                        # Next.js 15 web app
│   ├── app/                        # Next.js App Router
│   │   ├── (auth)/                 # Auth routes
│   │   ├── (dashboard)/            # Dashboard routes
│   │   ├── layout.tsx
│   │   └── page.tsx
│   ├── components/
│   ├── lib/
│   │   ├── api-client.ts          # Axios instance for Laravel API
│   │   └── echo.ts                # Laravel Echo setup
│   └── package.json
│
├── mobile/                          # React Native app
│   ├── src/
│   ├── android/
│   ├── ios/
│   └── package.json
│
├── shared/                          # Shared code library
│   ├── components/                 # Shared React components
│   ├── hooks/                      # Shared hooks (useAuth, useChat, etc)
│   ├── types/                      # TypeScript types
│   ├── utils/                      # Shared utilities
│   └── api/                        # API client functions
│
└── package.json                     # Root package.json (monorepo)
```

---

## ✅ Phase 1 Laravel Tasks (Immediate Actions)

### Task 1: Install Sanctum API
```bash
php artisan install:api
```

### Task 2: Configure Environment
Update `.env`:
```env
# Frontend
FRONTEND_URL=http://localhost:3000
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost

# API
API_PREFIX=/api/v1
```

### Task 3: Configure CORS
Update `config/cors.php` (shown above)

### Task 4: Update User Model
Add `HasApiTokens` trait (shown above)

### Task 5: Create API Controllers Structure
```bash
mkdir -p app/Http/Controllers/Api/V1
mkdir -p app/Http/Controllers/Api/V1/Admin
```

### Task 6: Create Base API Controller
**app/Http/Controllers/Api/V1/Controller.php**
```php
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{
    protected function success($data = null, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($message, $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
```

### Task 7: Create Auth Controller
Move existing authentication logic to API controller (shown above)

### Task 8: Create API Resources
```bash
# Create resources for all major models
php artisan make:resource UserResource
php artisan make:resource ProfileResource
php artisan make:resource MatchResource
php artisan make:resource MessageResource
php artisan make:resource ChatResource
# ... etc
```

### Task 9: Update Routes
- Keep existing `routes/web.php` for landing pages (public facing)
- Use `routes/api.php` for all API endpoints
- Move all Livewire logic to API controllers

### Task 10: Test API Endpoints
```bash
# Test with curl or Postman
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@test.com","password":"password","password_confirmation":"password","gender":"male","date_of_birth":"2000-01-01"}'
```

---

## 📊 Migration Progress Tracking

### Current Status: Phase 1 (Laravel API Backend)

**Completed:**
- [x] Research Laravel 12 API + Sanctum
- [x] Research Next.js 15 + React Server Components
- [x] Architecture planning

**In Progress:**
- [ ] Install Laravel API & Sanctum
- [ ] Configure CORS for Next.js
- [ ] Create API route structure
- [ ] Implement authentication endpoints
- [ ] Create API resources for all models
- [ ] Configure Broadcasting for Next.js WebSocket

**Not Started:**
- [ ] Next.js 15 setup
- [ ] React Native setup
- [ ] Shared components library
- [ ] Component migration
- [ ] Remove Livewire

---

## 🎯 Success Criteria

### Laravel API Backend Ready When:
- [x] Sanctum installed and configured
- [x] CORS configured for Next.js origin
- [x] All API routes defined (`routes/api.php`)
- [x] Authentication working (SPA + token modes)
- [x] API resources created for all models
- [x] Broadcasting configured for WebSocket clients
- [x] API endpoints documented (Swagger/Postman)
- [x] Tests passing for all endpoints

### Next.js Frontend Ready When:
- [ ] Next.js 15 App Router setup
- [ ] API client configured (Axios)
- [ ] Laravel Echo configured for WebSocket
- [ ] Authentication flow working
- [ ] Shared components library created
- [ ] First page converted and working

---

## 🚀 Next Steps

1. **Approve this plan** - Review and confirm architecture
2. **Start Laravel API setup** - Install Sanctum, configure CORS
3. **Create API endpoints** - Convert existing logic to API controllers
4. **Test API** - Postman/curl testing
5. **Set up Next.js** - Create frontend repository
6. **Integrate** - Connect Next.js to Laravel API
7. **Migrate components** - Convert Livewire to Next.js pages
8. **Deploy** - Separate deployments for API and frontend

---

## 📚 Resources

**Laravel 12:**
- [Sanctum Documentation](https://laravel.com/docs/12.x/sanctum)
- [API Resources](https://laravel.com/docs/12.x/eloquent-resources)
- [Broadcasting](https://laravel.com/docs/12.x/broadcasting)

**Next.js 15:**
- [Next.js Documentation](https://nextjs.org/docs)
- [App Router](https://nextjs.org/docs/app)
- [React Server Components](https://nextjs.org/docs/app/getting-started/server-and-client-components)

**Integration:**
- [Laravel + Next.js Starter](https://github.com/laravel/breeze-next)
- [Laravel Echo with Next.js](https://laravel.com/docs/12.x/broadcasting#client-side-installation)

---

**Ready to start? Let me know and I'll begin with the Laravel API setup!** 🚀

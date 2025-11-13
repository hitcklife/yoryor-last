# YorYor System Architecture

## Table of Contents
- [Overview](#overview)
- [Layered Architecture](#layered-architecture)
- [Service Layer Pattern](#service-layer-pattern)
- [Livewire Full-Stack Components](#livewire-full-stack-components)
- [Real-Time Architecture](#real-time-architecture)
- [Route Organization](#route-organization)
- [Design Patterns](#design-patterns)
- [Data Flow](#data-flow)
- [Key Architectural Principles](#key-architectural-principles)
- [Component Architecture](#component-architecture)
- [Security Architecture](#security-architecture)
- [Scalability Considerations](#scalability-considerations)

---

## Overview

YorYor follows a **strict layered architecture** combined with **service-oriented principles**, built on Laravel 12 framework. The architecture emphasizes clean separation of concerns, testability, and maintainability.

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        PRESENTATION LAYER                            │
│  ┌───────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  Blade Views  │  │   Livewire   │  │   API Resources      │    │
│  │  (Templates)  │  │  Components  │  │   (JSON:API)         │    │
│  └───────────────┘  └──────────────┘  └──────────────────────┘    │
└────────────┬─────────────────┬─────────────────┬────────────────────┘
             │                 │                 │
┌────────────▼─────────────────▼─────────────────▼────────────────────┐
│                      APPLICATION LAYER                               │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────┐  │
│  │   Web Routes     │  │   API Routes     │  │  WebSocket      │  │
│  │   (routes/web)   │  │   (routes/api)   │  │  (Reverb)       │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────┬────────┘  │
│           │                     │                      │            │
│  ┌────────▼─────────┐  ┌────────▼─────────┐  ┌────────▼────────┐  │
│  │    Livewire      │  │   API            │  │  Broadcasting   │  │
│  │    Controllers   │  │   Controllers    │  │  Controllers    │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────┬────────┘  │
└───────────┼──────────────────────┼───────────────────────┼──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                        MIDDLEWARE LAYER                              │
│  ┌──────────┐ ┌───────────┐ ┌──────────┐ ┌──────────────────────┐ │
│  │   Auth   │ │   CORS    │ │   Rate   │ │      Security        │ │
│  │          │ │           │ │  Limit   │ │      Headers         │ │
│  └──────────┘ └───────────┘ └──────────┘ └──────────────────────┘ │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                         SERVICE LAYER                                │
│  ┌────────────┐ ┌─────────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │   Auth     │ │  Matching   │ │  Media   │ │   Notification   │ │
│  │  Service   │ │   Service   │ │ Service  │ │     Service      │ │
│  └────────────┘ └─────────────┘ └──────────┘ └──────────────────┘ │
│  ┌────────────┐ ┌─────────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │  Payment   │ │  VideoSDK   │ │   OTP    │ │    Verification  │ │
│  │  Service   │ │   Service   │ │ Service  │ │     Service      │ │
│  └────────────┘ └─────────────┘ └──────────┘ └──────────────────┘ │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                      DOMAIN LAYER (Models)                           │
│  ┌────────────┐ ┌─────────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │    User    │ │   Profile   │ │   Chat   │ │      Match       │ │
│  │   Model    │ │    Model    │ │  Model   │ │      Model       │ │
│  └────────────┘ └─────────────┘ └──────────┘ └──────────────────┘ │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                       DATA ACCESS LAYER                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────┐  │
│  │     Database     │  │      Cache       │  │      Queue      │  │
│  │  (MySQL/SQLite)  │  │  (Redis/DB)      │  │   (Database)    │  │
│  └──────────────────┘  └──────────────────┘  └─────────────────┘  │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                   EXTERNAL SERVICES LAYER                            │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐  ┌──────────┐ │
│  │   VideoSDK   │  │  Cloudflare  │  │    Expo    │  │   Agora  │ │
│  │   (Video)    │  │     R2       │  │   (Push)   │  │  (Backup)│ │
│  └──────────────┘  └──────────────┘  └────────────┘  └──────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Layered Architecture

### 1. Presentation Layer

**Purpose:** Handle user interface and user interaction

**Components:**
- **Blade Templates** (`resources/views/`)
  - Traditional server-side rendered views
  - Layout templates for consistent UI
  - Reusable components

- **Livewire Components** (`app/Livewire/`)
  - Full-stack reactive components
  - Server-side state management
  - Real-time UI updates

- **API Resources** (`app/Http/Resources/`)
  - JSON:API formatted responses
  - Consistent API structure
  - Include relationships and metadata

- **JavaScript** (`resources/js/`)
  - Alpine.js for lightweight interactivity
  - Echo for WebSocket connections
  - Video calling integration

**File Structure:**
```
resources/
├── views/
│   ├── components/         # Reusable Blade components
│   ├── livewire/          # Livewire component views
│   ├── landing/           # Landing pages
│   └── layouts/           # Layout templates
└── js/
    ├── app.js             # Main JavaScript entry
    ├── echo.js            # WebSocket client
    ├── auth.js            # Authentication logic
    └── messages.js        # Chat functionality
```

### 2. Application Layer (Controllers)

**Purpose:** Orchestrate requests between presentation and business logic

**Controller Types:**

#### API Controllers
Location: `app/Http/Controllers/Api/V1/`

```php
class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
        private NotificationService $notificationService
    ) {}

    public function sendMessage(Request $request, $chatId)
    {
        // 1. Validate input
        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'type' => 'in:text,image,video',
        ]);

        // 2. Call service (business logic)
        $message = $this->chatService->sendMessage($chatId, $validated);

        // 3. Send notification
        $this->notificationService->notifyNewMessage($message);

        // 4. Return formatted response
        return new MessageResource($message);
    }
}
```

**Responsibilities:**
- Request validation
- Service orchestration
- Response formatting
- Error handling
- Authorization checks

**Critical Rule:** Controllers MUST NOT contain business logic. All business logic lives in services.

#### Web Controllers
Location: `app/Http/Controllers/Web/`

Handle traditional web routes and page rendering.

#### Livewire Components
Location: `app/Livewire/`

Full-stack components that handle both backend logic and frontend rendering:

```php
class SwipeCards extends Component
{
    public $profiles = [];
    public $currentIndex = 0;

    public function mount()
    {
        $this->loadProfiles();
    }

    public function like($userId)
    {
        // Business logic via service
        app(LikeService::class)->like(auth()->id(), $userId);

        // Dispatch event for UI update
        $this->dispatch('profile-liked', userId: $userId);

        $this->currentIndex++;
    }

    public function render()
    {
        return view('livewire.dashboard.swipe-cards');
    }
}
```

### 3. Middleware Layer

**Purpose:** Filter and process HTTP requests

**Custom Middleware:**
```
app/Http/Middleware/
├── Authenticate.php              # Custom auth (redirects to /start)
├── ApiRateLimit.php             # Dynamic API rate limiting
├── ChatRateLimit.php            # Chat-specific rate limits
├── SecurityHeaders.php          # Security header injection
├── PerformanceMonitor.php       # Request performance tracking
├── UpdateLastActive.php         # User activity tracking
├── LanguageMiddleware.php       # Locale detection
├── SetLocale.php                # Set app locale
└── AdminMiddleware.php          # Admin authorization
```

**Middleware Flow:**
```
Request
  → CSRF Verification
  → Authentication
  → Rate Limiting
  → Security Headers
  → Controller
  → Response
```

**Rate Limiting Strategy:**

Different limits per action type:

```php
// app/Http/Middleware/ApiRateLimit.php
protected $limits = [
    'auth_action' => ['max' => 10, 'decay' => 60],        // 10/min
    'like_action' => ['max' => 100, 'decay' => 3600],     // 100/hour
    'message_action' => ['max' => 500, 'decay' => 3600],  // 500/hour
    'call_action' => ['max' => 50, 'decay' => 3600],      // 50/hour
    'panic_activation' => ['max' => 5, 'decay' => 86400], // 5/day
];
```

Applied in routes:
```php
Route::post('/like', [LikeController::class, 'store'])
    ->middleware('api.rate.limit:like_action');
```

### 4. Service Layer

**Purpose:** Encapsulate ALL business logic

**Location:** `app/Services/`

**Service Categories:**

#### Authentication Services
```
├── AuthService.php              # User registration, login, logout
├── OtpService.php              # OTP generation/verification
├── TwoFactorAuthService.php    # 2FA with Google Authenticator
└── ValidationService.php        # Business validation rules
```

#### Core Business Services
```
├── MediaUploadService.php      # File upload to Cloudflare R2
├── ImageProcessingService.php  # Image manipulation
├── PresenceService.php         # Online status tracking
└── NotificationService.php     # Push notifications
```

#### Advanced Services
```
├── VerificationService.php         # Identity verification
├── PanicButtonService.php         # Emergency system
├── EnhancedReportingService.php   # User reporting
├── FamilyApprovalService.php      # Family features
├── MatchmakerService.php          # Professional matchmaking
├── UsageLimitsService.php         # Subscription limits
├── PaymentManager.php             # Payment processing
└── PrayerTimeService.php          # Islamic prayer times
```

See `/docs/development/SERVICES.md` for detailed service documentation.

### 5. Domain Layer (Models)

**Purpose:** Represent business entities and relationships

**Location:** `app/Models/`

**Model Organization:**

#### User-Related Models
```
├── User.php                      # Core user model
├── Profile.php                   # User profile data
├── UserPreference.php            # Matching preferences
├── UserPhoto.php                # Profile photos
├── UserSetting.php              # User settings
└── UserActivity.php             # Activity tracking
```

#### Profile Extension Models
```
├── UserCulturalProfile.php      # Religious/cultural data
├── UserCareerProfile.php        # Education/career
├── UserPhysicalProfile.php      # Physical attributes
├── UserFamilyPreference.php     # Family expectations
└── UserLocationPreference.php   # Location preferences
```

**Model Relationship Example:**
```php
class User extends Authenticatable
{
    // One-to-One relationships
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function setting(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    public function culturalProfile(): HasOne
    {
        return $this->hasOne(UserCulturalProfile::class);
    }

    // One-to-Many relationships
    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(UserStory::class);
    }

    // Many-to-Many relationships
    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_users')
            ->withPivot('role', 'is_muted', 'last_read_at')
            ->withTimestamps();
    }

    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'matches', 'user_id', 'matched_user_id')
            ->withPivot('matched_at')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->whereNull('disabled_at');
    }

    public function scopeOnline($query)
    {
        return $query->where('last_active_at', '>', now()->subMinutes(5));
    }
}
```

### 6. Data Access Layer

**Purpose:** Manage data persistence and retrieval

**Components:**
- **Eloquent ORM** - Database abstraction
- **Query Builder** - Raw query construction
- **Migrations** (`database/migrations/`) - Version-controlled schema
- **Seeders** (`database/seeders/`) - Sample data generation
- **Cache** - Query result caching
- **Queue** - Asynchronous job storage

---

## Service Layer Pattern

**The service layer is the heart of YorYor's business logic.**

### Why Services?

1. **Separation of Concerns** - Controllers stay thin
2. **Reusability** - Services used across controllers, Livewire, jobs
3. **Testability** - Easy to unit test
4. **Maintainability** - Business logic in one place
5. **Single Responsibility** - One service, one purpose

### Service Example

```php
// app/Services/AuthService.php
namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private OtpService $otpService,
        private NotificationService $notification
    ) {}

    public function register(array $data): array
    {
        // Transaction ensures data consistency
        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'uuid' => Str::uuid(),
            ]);

            // Create profile
            $user->profile()->create([
                'first_name' => $data['first_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'country_id' => $data['country_id'],
            ]);

            // Create default settings
            $user->setting()->create([]);

            // Create default preferences
            $user->preference()->create([
                'gender_preference' => $data['gender_preference'] ?? 'both',
                'min_age' => 18,
                'max_age' => 99,
            ]);

            // Send welcome notification
            $this->notification->sendWelcomeNotification($user);

            // Generate auth token
            $token = $user->createToken('auth_token')->plainTextToken;

            DB::commit();

            return [
                'user' => $user->load('profile'),
                'token' => $token,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function authenticate(array $credentials): array
    {
        // Authentication logic
        if (!Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = Auth::user();

        // Check 2FA
        if ($user->two_factor_enabled) {
            return ['requires_2fa' => true, 'user' => $user];
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('profile'),
            'token' => $token,
        ];
    }
}
```

### Service Usage in Controller

```php
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(Request $request)
    {
        // Validate
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|string|max:100',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'country_id' => 'required|exists:countries,id',
        ]);

        // Business logic in service
        $result = $this->authService->register($validated);

        // Return response
        return response()->json([
            'user' => new UserResource($result['user']),
            'token' => $result['token'],
        ], 201);
    }
}
```

### Transaction Pattern

**Always use transactions for multi-model operations:**

```php
public function createMatch($userId, $likedUserId)
{
    DB::beginTransaction();

    try {
        // Create match record
        $match = Match::create([
            'user_id' => $userId,
            'matched_user_id' => $likedUserId,
            'matched_at' => now(),
        ]);

        // Create reverse match
        Match::create([
            'user_id' => $likedUserId,
            'matched_user_id' => $userId,
            'matched_at' => now(),
        ]);

        // Create private chat
        $chat = Chat::create(['type' => 'private']);
        $chat->users()->attach([$userId, $likedUserId]);

        // Fire event
        event(new NewMatchEvent($match));

        DB::commit();

        return $match;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

---

## Livewire Full-Stack Components

**Livewire allows writing full-stack features in PHP, without separate API calls.**

### Component Structure

```php
// app/Livewire/Dashboard/SwipeCards.php
namespace App\Livewire\Dashboard;

use Livewire\Component;
use App\Services\DiscoveryService;
use App\Services\LikeService;

class SwipeCards extends Component
{
    // Public properties are available in view
    public $profiles = [];
    public $currentIndex = 0;
    public $showProfile = null;

    // Services injected in constructor
    public function __construct(
        private DiscoveryService $discoveryService,
        private LikeService $likeService
    ) {
        parent::__construct();
    }

    // Mount runs on component initialization
    public function mount()
    {
        $this->loadProfiles();
    }

    // Public methods are callable from view
    public function like($userId)
    {
        // Call service
        $this->likeService->like(auth()->id(), $userId);

        // Dispatch event for other components
        $this->dispatch('profile-liked', userId: $userId);

        // Update UI
        $this->currentIndex++;

        // Show success notification
        $this->dispatch('notify', message: 'Profile liked!');
    }

    public function pass($userId)
    {
        $this->likeService->pass(auth()->id(), $userId);
        $this->currentIndex++;
    }

    public function viewProfile($userId)
    {
        $this->showProfile = $userId;
        $this->dispatch('open-modal', modal: 'profile-detail');
    }

    // Listen to events from other components
    #[On('profile-updated')]
    public function refreshProfiles()
    {
        $this->loadProfiles();
    }

    // Private helper methods
    private function loadProfiles()
    {
        $this->profiles = $this->discoveryService->getDiscoveryProfiles(
            auth()->user()
        );
    }

    // Render method
    public function render()
    {
        return view('livewire.dashboard.swipe-cards');
    }
}
```

### Component View

```blade
{{-- resources/views/livewire/dashboard/swipe-cards.blade.php --}}
<div class="swipe-cards">
    @if(count($profiles) > 0)
        @foreach($profiles as $index => $profile)
            @if($index === $currentIndex)
                <div class="profile-card" wire:key="profile-{{ $profile->id }}">
                    <img src="{{ $profile->photo_url }}" alt="{{ $profile->name }}">

                    <h2>{{ $profile->first_name }}, {{ $profile->age }}</h2>
                    <p>{{ $profile->bio }}</p>

                    <div class="actions">
                        <button wire:click="pass({{ $profile->user_id }})">
                            Pass
                        </button>

                        <button wire:click="viewProfile({{ $profile->user_id }})">
                            View Profile
                        </button>

                        <button wire:click="like({{ $profile->user_id }})">
                            Like
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    @else
        <p>No more profiles to show!</p>
    @endif
</div>
```

### State Management

Livewire maintains state across requests:

```php
class MatchesPage extends Component
{
    // Properties persist across requests
    public $filter = 'all';
    public $search = '';
    public $page = 1;

    // Computed properties (cached)
    #[Computed]
    public function filteredMatches()
    {
        return Match::query()
            ->where('user_id', auth()->id())
            ->when($this->filter !== 'all', function ($query) {
                $query->where('status', $this->filter);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('matchedUser.profile', function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%");
                });
            })
            ->paginate(20);
    }

    // Updated automatically when input changes
    public function updatedSearch()
    {
        $this->page = 1; // Reset to first page
    }

    public function render()
    {
        return view('livewire.pages.matches-page', [
            'matches' => $this->filteredMatches(),
        ]);
    }
}
```

---

## Real-Time Architecture

### Laravel Reverb (WebSocket Server)

```
┌──────────────┐
│  Client App  │
└──────┬───────┘
       │ WebSocket (ws://localhost:8080)
       ↓
┌──────────────────┐
│ Laravel Reverb   │ ← Standalone WebSocket Server
│  (Port 8080)     │    Handles connections, broadcasts
└──────┬───────────┘
       │ Events
       ↓
┌──────────────────┐
│  Laravel App     │ ← Dispatches events
│  (Port 8000)     │
└──────────────────┘
```

### Broadcasting Channels

```php
// routes/channels.php
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Verify user is participant
    return Chat::where('id', $chatId)
        ->whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->exists();
});

Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Verify user identity
    return (int) $user->id === (int) $userId;
});
```

### Event Broadcasting

```php
// app/Events/NewMessageEvent.php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NewMessageEvent implements ShouldBroadcast
{
    public function __construct(
        public Message $message
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->message->chat_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'sender' => [
                'id' => $this->message->sender_id,
                'name' => $this->message->sender->name,
            ],
            'sent_at' => $this->message->sent_at,
        ];
    }
}
```

### JavaScript Client

```javascript
// resources/js/echo.js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// Listen to private channel
Echo.private(`chat.${chatId}`)
    .listen('.message.new', (e) => {
        // Update UI with new message
        appendMessage(e.message);
    })
    .listenForWhisper('typing', (e) => {
        // Show typing indicator
        showTyping(e.userId);
    });
```

---

## Route Organization

Routes are organized by purpose and authentication requirements:

### routes/api.php
**Mobile/API endpoints - Sanctum authentication**

```php
// Public endpoints
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('profiles', ProfileController::class);
    Route::apiResource('matches', MatchController::class);
    Route::apiResource('chats', ChatController::class);

    // Rate limited
    Route::post('/likes', [LikeController::class, 'store'])
        ->middleware('api.rate.limit:like_action');
});
```

### routes/web.php
**Public web routes**

```php
// Landing pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/pricing', [HomeController::class, 'pricing'])->name('pricing');

// Authentication (Livewire)
Route::get('/start', Login::class)->name('login');
Route::get('/register', Register::class)->name('register');
```

### routes/user.php
**Authenticated user routes - web middleware**

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/discover', DiscoverPage::class)->name('discover');
    Route::get('/matches', MatchesPage::class)->name('matches');
    Route::get('/messages', MessagesPage::class)->name('messages');
    Route::get('/profile', ProfilePage::class)->name('profile');
});
```

### routes/admin.php
**Admin dashboard routes**

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/reports', [AdminController::class, 'reports']);
});
```

---

## Design Patterns

### 1. Repository Pattern (via Eloquent)

Laravel's Eloquent ORM acts as repository:

```php
// Instead of custom repositories, use Eloquent directly
$users = User::active()
    ->with('profile', 'photos')
    ->where('gender', 'male')
    ->whereBetween('age', [25, 35])
    ->get();
```

### 2. Service Pattern

Business logic in dedicated services:

```php
class MatchmakerService
{
    public function createIntroduction(
        Matchmaker $matchmaker,
        User $clientA,
        User $clientB,
        string $message
    ): Introduction {
        // Complex business logic
    }
}
```

### 3. Observer Pattern

Model observers for event-driven actions:

```php
// app/Observers/UserObserver.php
class UserObserver
{
    public function created(User $user)
    {
        // Create default settings
        $user->setting()->create([]);

        // Send welcome email
        $user->notify(new WelcomeNotification());
    }

    public function updated(User $user)
    {
        // Clear cache
        Cache::forget("user-{$user->id}");
    }
}
```

### 4. Factory Pattern

Model factories for testing:

```php
User::factory()
    ->has(Profile::factory())
    ->has(UserPhoto::factory()->count(3))
    ->create();
```

### 5. Strategy Pattern

Rate limiting strategies:

```php
protected $strategies = [
    'like_action' => ['max' => 100, 'decay' => 60],
    'message_action' => ['max' => 500, 'decay' => 60],
];
```

### 6. Facade Pattern

Laravel facades for simplified access:

```php
Cache::remember('user-' . $userId, 3600, function () use ($userId) {
    return User::with('profile')->find($userId);
});

DB::transaction(function () {
    // Database operations
});

Storage::disk('r2')->put($path, $file);
```

---

## Data Flow

### Request-Response Flow (API)

```
1. Client Request (JSON)
   ↓
2. Route Matching (routes/api.php)
   ↓
3. Middleware Stack
   ├── Authenticate (Sanctum)
   ├── Rate Limit
   └── Security Headers
   ↓
4. Controller Method
   ├── Validate Input (FormRequest)
   ├── Call Service (Business Logic)
   └── Format Response (API Resource)
   ↓
5. Service Layer
   ├── Complex Business Logic
   ├── Model Interaction
   ├── External API Calls
   └── Event Dispatching
   ↓
6. Model/Database
   ├── Query Execution
   ├── Relationship Loading
   └── Data Transformation
   ↓
7. API Resource
   ├── JSON:API Format
   ├── Include Relationships
   └── Add Metadata
   ↓
8. JSON Response
```

### Livewire Component Flow

```
1. User Interaction (click, type, etc.)
   ↓
2. JavaScript Event (wire:click, wire:model)
   ↓
3. AJAX Request to Server
   ↓
4. Livewire Component Method
   ├── Update Properties
   ├── Call Service (Business Logic)
   ├── Emit Events
   └── Validate Data
   ↓
5. Re-render Component
   ├── Execute Computed Properties
   ├── Render Blade View
   └── Generate HTML Diff
   ↓
6. JavaScript Receives Response
   ├── Apply DOM Diff (Morphdom)
   ├── Update UI
   └── Trigger Browser Events
   ↓
7. UI Updated (No Page Reload)
```

### Real-Time Message Flow

```
1. User Sends Message
   ↓
2. ChatController::sendMessage()
   ├── Validate message
   ├── Store in database
   └── Return success
   ↓
3. event(new NewMessageEvent($message))
   ↓
4. Laravel Reverb Receives Event
   ├── Authenticate channel
   ├── Find connected clients
   └── Broadcast to recipients
   ↓
5. Recipients Receive via WebSocket
   ├── Echo.private('chat.{id}').listen()
   ├── Update Livewire component
   └── Play notification sound
   ↓
6. UI Updated in Real-Time
```

---

## Key Architectural Principles

### 1. Separation of Concerns

- **Controllers** orchestrate
- **Services** contain business logic
- **Models** represent data
- **Views** handle presentation

### 2. Dependency Injection

```php
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private OtpService $otpService,
        private NotificationService $notificationService
    ) {}
}
```

### 3. Single Responsibility

Each class has one reason to change:
- `UserService` - User management
- `AuthService` - Authentication
- `MediaUploadService` - File uploads

### 4. DRY (Don't Repeat Yourself)

Reusable services across controllers, Livewire, jobs:

```php
// In controller
$this->authService->authenticate($credentials);

// In Livewire
app(AuthService::class)->authenticate($credentials);

// In job
$this->authService->authenticate($credentials);
```

### 5. Transaction Management

Always use transactions for multi-model operations:

```php
DB::transaction(function () {
    // Multiple operations
});
```

### 6. Event-Driven Architecture

Loosely coupled components via events:

```php
// Dispatch
event(new UserRegistered($user));

// Listen
class SendWelcomeEmail
{
    public function handle(UserRegistered $event)
    {
        Mail::to($event->user)->send(new WelcomeEmail());
    }
}
```

---

## Component Architecture

### Livewire Component Hierarchy

```
Pages/                          # Full-page components
├── DiscoverPage                # Main discovery page
│   ├── uses: Dashboard/SwipeCards
│   └── uses: Dashboard/ProfileModal
├── MatchesPage                 # Matches listing
├── MessagesPage                # Chat interface
└── ProfilePage                 # Profile management

Dashboard/                      # Feature components
├── SwipeCards                  # Swipe interface
├── DiscoveryGrid               # Grid view
├── ProfileModal                # Profile details
└── StoriesBar                  # 24-hour stories

Profile/                        # Profile editing
├── BasicInfo                   # Basic information
├── Photos                      # Photo management
├── Preferences                 # Match preferences
├── CulturalBackground          # Cultural/religious info
└── CareerEducation             # Career details

Components/                     # Reusable components
├── UnifiedSidebar              # Navigation sidebar
├── LanguageSwitcher            # Language selection
└── NotificationBell            # Notifications
```

---

## Security Architecture

### Authentication Flow

```
1. User Enters Credentials
   ↓
2. AuthController::authenticate()
   ├── Validate credentials
   └── Check password hash
   ↓
3. OtpService::generateCode()
   ├── Generate 6-digit code
   ├── Store in database
   └── Send via email/SMS
   ↓
4. User Enters OTP
   ↓
5. OtpService::verifyCode()
   ├── Verify code
   └── Check expiration
   ↓
6. 2FA Check (if enabled)
   ├── TwoFactorAuthService::verify()
   └── Verify TOTP code
   ↓
7. Generate Sanctum Token
   ├── $user->createToken()
   └── Return token
   ↓
8. Return User + Token
```

### Authorization Layers

```
1. Middleware Level
   ├── Authenticate (logged in?)
   ├── AdminMiddleware (is admin?)
   └── Verified (email verified?)

2. Controller Level
   ├── $this->authorize('view', $profile)
   └── Gate::allows('update', $profile)

3. Model Level
   ├── Global scopes
   └── Protected attributes

4. Database Level
   ├── Foreign key constraints
   └── Unique constraints
```

---

## Scalability Considerations

### Horizontal Scaling

- Stateless application design
- Session stored in database/Redis
- WebSocket clustering ready
- Load balancer compatible

### Database Optimization

- Indexed foreign keys
- Composite indexes on queries
- Query result caching
- Read replicas ready

### Caching Strategy

```
Level 1: Application Cache (Redis)
  ├── User profiles (1 hour)
  ├── Match results (30 minutes)
  └── Discovery profiles (15 minutes)

Level 2: Query Cache
  ├── Expensive aggregations
  └── Lookup tables

Level 3: CDN Cache
  ├── Images (Cloudflare R2)
  └── Static assets
```

### Queue System

```
Background Jobs:
├── SendEmergencyNotificationJob
├── ProcessVerificationDocumentsJob
├── SendPushNotificationJob
├── ProcessImageUploadJob
└── GenerateMatchRecommendationsJob
```

---

**This architecture ensures YorYor is maintainable, scalable, and follows Laravel best practices.**

*Last Updated: October 2025*

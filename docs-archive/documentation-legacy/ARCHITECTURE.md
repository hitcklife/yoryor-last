# YorYor Architecture Documentation

## Table of Contents
1. [System Architecture Overview](#system-architecture-overview)
2. [Application Layers](#application-layers)
3. [Design Patterns](#design-patterns)
4. [Component Architecture](#component-architecture)
5. [Data Flow](#data-flow)
6. [Real-time Architecture](#real-time-architecture)
7. [Security Architecture](#security-architecture)
8. [Scalability Considerations](#scalability-considerations)

---

## System Architecture Overview

YorYor follows a **layered architecture** pattern combined with **service-oriented architecture (SOA)** principles, built on top of the Laravel framework's MVC foundation.

### Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                           PRESENTATION LAYER                         │
│  ┌───────────────┐  ┌──────────────┐  ┌──────────────────────┐    │
│  │  Blade Views  │  │   Livewire   │  │   API Responses      │    │
│  │  (Templates)  │  │  Components  │  │   (JSON:API)         │    │
│  └───────────────┘  └──────────────┘  └──────────────────────┘    │
└────────────┬─────────────────┬─────────────────┬────────────────────┘
             │                 │                 │
┌────────────▼─────────────────▼─────────────────▼────────────────────┐
│                         APPLICATION LAYER                            │
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
│                          MIDDLEWARE LAYER                            │
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
│                         DOMAIN LAYER (Models)                        │
│  ┌────────────┐ ┌─────────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │    User    │ │   Profile   │ │   Chat   │ │      Match       │ │
│  │   Model    │ │    Model    │ │  Model   │ │      Model       │ │
│  └────────────┘ └─────────────┘ └──────────┘ └──────────────────┘ │
│  ┌────────────┐ ┌─────────────┐ ┌──────────┐ ┌──────────────────┐ │
│  │  Message   │ │    Call     │ │  Story   │ │   Subscription   │ │
│  │   Model    │ │    Model    │ │  Model   │ │      Model       │ │
│  └────────────┘ └─────────────┘ └──────────┘ └──────────────────┘ │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                        DATA ACCESS LAYER                             │
│  ┌──────────────────┐  ┌──────────────────┐  ┌─────────────────┐  │
│  │     Database     │  │      Cache       │  │      Queue      │  │
│  │  (MySQL/SQLite)  │  │  (Redis/DB)      │  │   (Database)    │  │
│  └──────────────────┘  └──────────────────┘  └─────────────────┘  │
└───────────┬──────────────────────┬───────────────────────┬──────────┘
            │                      │                       │
┌───────────▼──────────────────────▼───────────────────────▼──────────┐
│                      EXTERNAL SERVICES LAYER                         │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────┐  ┌──────────┐ │
│  │   VideoSDK   │  │  Cloudflare  │  │    Expo    │  │   Agora  │ │
│  │   (Video)    │  │     R2       │  │   (Push)   │  │  (Backup)│ │
│  └──────────────┘  └──────────────┘  └────────────┘  └──────────┘ │
└──────────────────────────────────────────────────────────────────────┘
```

---

## Application Layers

### 1. Presentation Layer

**Purpose:** Handle user interface and user interaction

**Components:**
- **Blade Templates:** Traditional server-side rendered views
- **Livewire Components:** Full-stack reactive components with server-side rendering
- **API Resources:** JSON:API formatted responses for mobile/external clients
- **JavaScript:** Alpine.js for lightweight client-side interactivity

**Key Files:**
```
resources/
├── views/
│   ├── components/      # Reusable Blade components
│   ├── livewire/        # Livewire component views
│   ├── landing/         # Landing pages
│   └── layouts/         # Layout templates
└── js/
    ├── app.js           # Main JavaScript entry
    ├── echo.js          # WebSocket client
    ├── auth.js          # Authentication logic
    └── messages.js      # Chat functionality
```

### 2. Application Layer (Controllers)

**Purpose:** Handle HTTP requests, coordinate between services and views

**Types of Controllers:**
- **API Controllers:** RESTful API endpoints (`app/Http/Controllers/Api/V1/`)
- **Web Controllers:** Traditional web routes (`app/Http/Controllers/Web/`)
- **Livewire Components:** Full-stack components (`app/Livewire/`)
- **Broadcasting Controllers:** WebSocket authentication

**Responsibilities:**
- Request validation
- Business logic orchestration
- Response formatting
- Error handling

**Example Structure:**
```php
// app/Http/Controllers/Api/V1/ChatController.php
class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService,
        private NotificationService $notificationService
    ) {}

    public function sendMessage(Request $request, $chatId)
    {
        $validated = $request->validate([...]);
        $message = $this->chatService->sendMessage($chatId, $validated);
        $this->notificationService->notifyNewMessage($message);
        return new MessageResource($message);
    }
}
```

### 3. Middleware Layer

**Purpose:** Filter and process HTTP requests before reaching controllers

**Custom Middleware:**
```
app/Http/Middleware/
├── Authenticate.php              # Custom auth with /start redirect
├── ApiRateLimit.php             # Dynamic API rate limiting
├── ChatRateLimit.php            # Chat-specific rate limits
├── SecurityHeaders.php          # Security header injection
├── PerformanceMonitor.php       # Request performance tracking
├── UpdateLastActive.php         # User activity tracking
├── LanguageMiddleware.php       # Locale detection
└── AdminMiddleware.php          # Admin authorization
```

**Middleware Flow:**
```
Request → CSRF → Auth → Rate Limit → Security → Controller → Response
```

### 4. Service Layer

**Purpose:** Encapsulate business logic, coordinate between models, handle external APIs

**Key Services:**

#### Authentication Services
```
app/Services/
├── AuthService.php              # User registration, login, logout
├── OtpService.php              # OTP generation and verification
├── TwoFactorAuthService.php    # 2FA with Google Authenticator
└── ValidationService.php        # Business validation rules
```

#### Core Business Services
```
├── UserService.php             # User management logic
├── MediaUploadService.php      # File upload handling
├── ImageProcessingService.php  # Image manipulation
├── PresenceService.php         # Online status tracking
└── NotificationService.php     # Push notifications
```

#### Communication Services
```
├── CallMessageService.php      # Call-related messaging
├── ExpoPushService.php        # Expo push notifications
└── MonitoringService.php      # System monitoring
```

#### Video Services
```
├── VideoSDKService.php        # VideoSDK integration
├── AgoraService.php          # Agora RTC (backup)
└── AgoraTokenBuilder.php     # Token generation
```

#### Advanced Services
```
├── VerificationService.php         # Identity verification
├── PanicButtonService.php         # Emergency system
├── EnhancedReportingService.php   # User reporting
├── FamilyApprovalService.php      # Family features
├── MatchmakerService.php          # Professional matchmaking
├── UsageLimitsService.php         # Subscription limits
├── PrayerTimeService.php          # Islamic prayer times
├── PaymentManager.php             # Payment processing
├── PrivacyService.php             # Privacy controls
├── CacheService.php               # Cache management
└── ErrorHandlingService.php       # Error tracking
```

**Service Pattern Example:**
```php
class AuthService
{
    public function __construct(
        private MediaUploadService $mediaUpload,
        private ImageProcessingService $imageProcessing,
        private OtpService $otpService
    ) {}

    public function register(array $data): array
    {
        DB::beginTransaction();
        try {
            $user = User::create([...]);
            $user->profile()->create([...]);
            $user->preference()->create([...]);
            $token = $user->createToken('auth_token')->plainTextToken;
            DB::commit();
            return ['user' => $user, 'token' => $token];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

### 5. Domain Layer (Models)

**Purpose:** Represent business entities and their relationships

**Model Organization:**

#### User-Related Models
```
app/Models/
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

#### Communication Models
```
├── Chat.php                     # Chat conversations
├── Message.php                  # Chat messages
├── MessageRead.php              # Read receipts
├── ChatUser.php                 # Chat participants (pivot)
└── Call.php                     # Video/voice calls
```

#### Matching Models
```
├── MatchModel.php               # Mutual matches
├── Like.php                     # User likes
└── Dislike.php                  # User passes
```

#### Content Models
```
├── UserStory.php                # 24-hour stories
└── Media.php                    # Media files
```

#### Subscription Models
```
├── SubscriptionPlan.php         # Available plans
├── PlanFeature.php             # Plan features
├── PlanPricing.php             # Pricing tiers
├── UserSubscription.php         # Active subscriptions
├── PaymentTransaction.php       # Payment history
├── UserUsageLimits.php         # Usage limits
└── UserMonthlyUsage.php        # Usage tracking
```

#### Safety & Moderation Models
```
├── UserBlock.php                # Blocked users
├── UserReport.php               # Basic reports
├── EnhancedUserReport.php      # Detailed reports
├── ReportEvidence.php          # Report attachments
├── UserSafetyScore.php         # Safety ratings
├── PanicActivation.php         # Panic button logs
└── UserEmergencyContact.php    # Emergency contacts
```

#### Matchmaker Models
```
├── Matchmaker.php              # Matchmaker profiles
├── MatchmakerService.php       # Services offered
├── MatchmakerClient.php        # Client relationships
├── MatchmakerConsultation.php  # Consultations
├── MatchmakerIntroduction.php  # Introductions made
├── MatchmakerReview.php        # Reviews
└── MatchmakerAvailability.php  # Schedule
```

#### Verification Models
```
├── VerificationRequest.php     # Verification submissions
└── UserVerifiedBadge.php       # Earned badges
```

#### RBAC Models
```
├── Role.php                    # User roles
└── Permission.php              # Permissions
```

#### System Models
```
├── Country.php                 # Country data
├── OtpCode.php                # OTP codes
├── DeviceToken.php            # Push notification tokens
├── Notification.php           # In-app notifications
├── EmergencyContact.php       # Emergency contacts
├── UserFeedback.php           # User feedback
├── DataExportRequest.php      # GDPR export requests
├── FamilyMember.php           # Family accounts
├── FamilyApproval.php         # Family approvals
└── UserPrayerTime.php         # Prayer time preferences
```

**Model Relationship Example:**
```php
class User extends Authenticatable
{
    // One-to-One
    public function profile(): HasOne
    public function preference(): HasOne
    public function setting(): HasOne
    public function culturalProfile(): HasOne
    public function careerProfile(): HasOne

    // One-to-Many
    public function photos(): HasMany
    public function stories(): HasMany
    public function messages(): HasMany
    public function sentLikes(): HasMany

    // Many-to-Many
    public function chats(): BelongsToMany
    public function matches(): BelongsToMany
    public function blockedUsers(): BelongsToMany
    public function roles(): BelongsToMany

    // Polymorphic
    public function activities(): MorphMany
}
```

### 6. Data Access Layer

**Purpose:** Manage data persistence and retrieval

**Components:**
- **Eloquent ORM:** Database abstraction
- **Query Builder:** Raw query construction
- **Migrations:** Version-controlled schema
- **Seeders:** Sample data generation
- **Cache:** Query result caching
- **Queue:** Asynchronous job storage

---

## Design Patterns

### 1. Repository Pattern (via Eloquent)
Laravel's Eloquent ORM acts as a repository pattern implementation.

### 2. Service Pattern
Business logic encapsulated in dedicated service classes.

### 3. Observer Pattern
Model observers for event-driven actions:
```php
// app/Observers/UserObserver.php
class UserObserver
{
    public function created(User $user)
    {
        // Create default settings
        $user->setting()->create([...]);
    }
}
```

### 4. Factory Pattern
Model factories for testing and seeding:
```php
// database/factories/UserFactory.php
UserFactory::new()->create([
    'email' => 'test@example.com'
]);
```

### 5. Strategy Pattern
Rate limiting strategies:
```php
// app/Http/Middleware/ApiRateLimit.php
protected $strategies = [
    'like_action' => ['max' => 100, 'decay' => 60],
    'message_action' => ['max' => 500, 'decay' => 60],
    'call_action' => ['max' => 50, 'decay' => 60],
];
```

### 6. Decorator Pattern
Middleware decorating requests/responses.

### 7. Facade Pattern
Laravel facades for simplified API access.

### 8. Event-Driven Architecture
Broadcasting events for real-time updates:
```php
// Dispatch event
NewMessageEvent::dispatch($message);

// Listen in JavaScript
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        // Handle new message
    });
```

---

## Component Architecture

### Livewire Component Structure

```
app/Livewire/
├── Auth/                        # Authentication components
│   ├── Login.php
│   ├── Register.php
│   └── VerifyEmail.php
├── Profile/                     # Profile management
│   ├── BasicInfo.php
│   ├── Photos.php
│   ├── Preferences.php
│   └── Preview.php
├── Dashboard/                   # Main app components
│   ├── SwipeCards.php
│   ├── DiscoveryGrid.php
│   ├── ProfileModal.php
│   └── StoriesBar.php
├── Pages/                       # Full-page components
│   ├── DiscoverPage.php
│   ├── MatchesPage.php
│   ├── ChatPage.php
│   └── MessagesPage.php
├── Components/                  # Reusable components
│   ├── UnifiedSidebar.php
│   └── LanguageSwitcher.php
├── Admin/                       # Admin dashboard
│   ├── Dashboard.php
│   ├── Users.php
│   └── Reports.php
└── Settings/                    # User settings
    ├── Profile.php
    ├── Password.php
    └── Appearance.php
```

### Livewire Component Lifecycle

```
Mount → Hydrate → Render → Update → Dehydrate
  ↓                                      ↓
Initialize State                    Persist State
```

---

## Data Flow

### Request-Response Flow (API)

```
1. Client Request
   ↓
2. Route Matching (routes/api.php)
   ↓
3. Middleware Stack
   ├── Authenticate
   ├── Rate Limit
   └── Security Headers
   ↓
4. Controller Method
   ├── Validate Input
   ├── Call Service
   └── Format Response
   ↓
5. Service Layer
   ├── Business Logic
   ├── Model Interaction
   └── External API Calls
   ↓
6. Model/Database
   ├── Query Execution
   ├── Relationship Loading
   └── Data Transformation
   ↓
7. API Resource
   ├── JSON:API Format
   └── Include Relationships
   ↓
8. JSON Response
```

### Livewire Component Flow

```
1. User Interaction (click, type, etc.)
   ↓
2. JavaScript Event
   ↓
3. AJAX Request to Server
   ↓
4. Livewire Component Method
   ├── Update Properties
   ├── Call Service
   └── Emit Events
   ↓
5. Re-render Component
   ↓
6. DOM Diff & Update
   ↓
7. UI Updated
```

### Real-Time Message Flow

```
1. User Sends Message
   ↓
2. ChatController::sendMessage()
   ↓
3. Message Saved to Database
   ↓
4. NewMessageEvent::dispatch()
   ↓
5. Laravel Reverb Broadcasts
   ↓
6. Recipients Receive via WebSocket
   ↓
7. Livewire Updates UI
```

---

## Real-time Architecture

### Laravel Reverb (WebSocket Server)

```
┌──────────────┐
│  Client App  │
└──────┬───────┘
       │ WebSocket Connection
       ↓
┌──────────────────┐
│ Laravel Reverb   │ ← Standalone WebSocket Server
│  (Port 8080)     │
└──────┬───────────┘
       │ Event Broadcasting
       ↓
┌──────────────────┐
│  Laravel App     │
│  (Port 8000)     │
└──────────────────┘
```

### Broadcasting Channels

```php
// Private User Channel
private-user.{userId}
  ├── NewMessageEvent
  ├── NewMatchEvent
  ├── CallInitiatedEvent
  └── GeneralNotificationEvent

// Private Chat Channel
private-chat.{chatId}
  ├── NewMessageEvent
  ├── MessageEditedEvent
  ├── MessageDeletedEvent
  └── UserTypingStatusChanged

// Presence Channel
presence-chat.{chatId}
  ├── UserOnlineStatusChanged
  └── UserTypingStatusChanged
```

### Event Broadcasting Flow

```php
// 1. Dispatch Event
event(new NewMessageEvent($message));

// 2. Event Class
class NewMessageEvent implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("chat.{$this->message->chat_id}"),
        ];
    }
}

// 3. Client Listens
Echo.private(`chat.${chatId}`)
    .listen('NewMessageEvent', (e) => {
        // Update UI with new message
    });
```

---

## Security Architecture

### Authentication Flow

```
1. User Enters Credentials (Email/Phone + Password)
   ↓
2. AuthController::authenticate()
   ↓
3. OtpService::generateCode()
   ↓
4. OTP Sent (Email/SMS)
   ↓
5. User Enters OTP
   ↓
6. OtpService::verifyCode()
   ↓
7. 2FA Check (if enabled)
   ├── TwoFactorAuthService::verify()
   └── Google Authenticator Code
   ↓
8. Generate Sanctum Token
   ↓
9. Return User + Token
```

### Authorization Layers

```
1. Middleware Level
   ├── Authenticate (logged in?)
   ├── AdminMiddleware (is admin?)
   └── Verified (email verified?)

2. Controller Level
   ├── $this->authorize('view', $profile)
   └── Gate checks

3. Model Level
   ├── Global scopes
   └── Protected attributes

4. Database Level
   ├── Foreign key constraints
   └── Row-level security (future)
```

### Rate Limiting Architecture

```php
// Dynamic rate limiting based on action type
ApiRateLimit::handle($request, $next, $actionType)
  ↓
Strategies:
├── auth_action: 10/min (login/register)
├── like_action: 100/hour
├── message_action: 500/hour
├── call_action: 50/hour
├── panic_activation: 5/day
└── profile_update: 30/hour
```

---

## Scalability Considerations

### Horizontal Scaling
- Stateless application design
- Load balancer ready
- Session stored in database/Redis
- WebSocket clustering support

### Database Optimization
- Indexed foreign keys
- Composite indexes on common queries
- Query result caching
- Read replicas ready

### Caching Strategy
```
Level 1: Application Cache (Redis/Memcached)
  ├── User profiles
  ├── Match results
  └── Static content

Level 2: Query Cache
  ├── Expensive aggregations
  └── Lookup tables (countries, etc.)

Level 3: CDN Cache
  ├── Images (Cloudflare R2)
  └── Static assets
```

### Queue System
```
Queue Workers (Background Processing)
├── SendEmergencyNotificationJob
├── ProcessVerificationDocumentsJob
├── SendPushNotificationJob (planned)
├── ProcessImageUploadJob (planned)
└── GenerateMatchRecommendationsJob (planned)
```

### Performance Monitoring
- Laravel Telescope (development)
- Laravel Pulse (production metrics)
- Laravel Horizon (queue monitoring)
- Custom PerformanceMonitor middleware

---

## Technology Decisions

### Why Laravel 12?
- Modern PHP 8.2+ features
- Excellent ecosystem
- Built-in WebSocket support (Reverb)
- Strong security defaults
- Active community

### Why Livewire over Vue/React?
- Faster development
- No API layer needed
- Server-side rendering
- Better SEO
- Simpler state management
- Native Laravel integration

### Why SQLite (Dev) + MySQL (Prod)?
- Fast local development
- Easy testing
- Production-grade options
- Migration compatibility

### Why Cloudflare R2?
- S3-compatible API
- Zero egress fees
- Global CDN
- Cost-effective
- Excellent performance

### Why VideoSDK over Agora?
- Better pricing model
- Easier integration
- Good documentation
- WebRTC-based
- Agora kept as backup

---

**Last Updated:** 2025-09-30
**Document Version:** 1.0.0
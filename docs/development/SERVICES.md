# YorYor Service Layer Documentation

## Table of Contents
- [Overview](#overview)
- [Service Principles](#service-principles)
- [Authentication Services](#authentication-services)
- [Media Services](#media-services)
- [Communication Services](#communication-services)
- [Video Services](#video-services)
- [Advanced Services](#advanced-services)
- [Utility Services](#utility-services)
- [Service Patterns](#service-patterns)
- [Testing Services](#testing-services)
- [Best Practices](#best-practices)

---

## Overview

The service layer is the **heart of YorYor's business logic**. All complex operations, business rules, and workflows are encapsulated in dedicated service classes.

**Why Services?**
- **Separation of Concerns** - Controllers stay thin and focused
- **Reusability** - Services used across controllers, Livewire, jobs, commands
- **Testability** - Easy to unit test in isolation
- **Maintainability** - Business logic in one predictable location
- **Single Responsibility** - One service, one clear purpose

**Location:** All services are in `app/Services/`

**Total Services:** 25+

---

## Service Principles

### 1. Single Responsibility Principle

Each service handles one domain:

```php
// Good - Focused service
class AuthService {
    public function register() {}
    public function login() {}
    public function logout() {}
}

// Bad - Mixed responsibilities
class UserService {
    public function register() {}
    public function uploadPhoto() {}
    public function sendMessage() {}  // Wrong domain
}
```

### 2. Dependency Injection

Services inject their dependencies:

```php
class AuthService
{
    public function __construct(
        private OtpService $otpService,
        private MediaUploadService $mediaUpload,
        private NotificationService $notification
    ) {}
}
```

### 3. Transaction Management

Always use transactions for multi-model operations:

```php
public function createMatch($userId, $likedUserId)
{
    DB::beginTransaction();

    try {
        // Multiple database operations
        $match = Match::create([...]);
        $chat = Chat::create([...]);
        $chat->users()->attach([...]);

        event(new NewMatchEvent($match));

        DB::commit();
        return $match;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### 4. Type Hinting

Always use type hints for parameters and return types:

```php
public function authenticate(array $credentials): array
{
    // Implementation
}
```

---

## Authentication Services

### AuthService

**Location:** `app/Services/AuthService.php`

**Purpose:** User authentication, registration, and session management

#### Methods

**register(array $data): array**

Create new user account with all related data.

```php
public function register(array $data): array
{
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
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'country_id' => $data['country_id'],
        ]);

        // Create default settings
        $user->setting()->create([
            'theme' => 'system',
            'language' => 'en',
            'notifications_enabled' => true,
        ]);

        // Create default preferences
        $user->preference()->create([
            'gender_preference' => $data['gender_preference'] ?? 'both',
            'min_age' => 18,
            'max_age' => 99,
            'search_radius' => 50,
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
```

**authenticate(array $credentials): array**

Authenticate user with email/password.

```php
public function authenticate(array $credentials): array
{
    // Attempt authentication
    if (!Auth::attempt([
        'email' => $credentials['email'],
        'password' => $credentials['password']
    ])) {
        throw new AuthenticationException('Invalid credentials');
    }

    $user = Auth::user();

    // Check if account is active
    if (!$user->is_active || $user->disabled_at) {
        throw new AccountDisabledException('Account is disabled');
    }

    // Check 2FA requirement
    if ($user->two_factor_enabled) {
        return [
            'requires_2fa' => true,
            'user_id' => $user->id,
        ];
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
```

**checkEmailExists(string $email): array**

Check if email is already registered.

```php
public function checkEmailExists(string $email): array
{
    $user = User::where('email', $email)->first();

    if (!$user) {
        return [
            'exists' => false,
            'can_register' => true,
        ];
    }

    return [
        'exists' => true,
        'requires_password' => !empty($user->password),
        'can_use_otp' => true,
        'has_2fa' => $user->two_factor_enabled,
        'provider' => $user->provider,
    ];
}
```

**logout(User $user): void**

Logout user and revoke all tokens.

```php
public function logout(User $user): void
{
    // Revoke all tokens
    $user->tokens()->delete();

    // Clear user cache
    Cache::forget("user-{$user->id}");
    Cache::forget("user-online-{$user->id}");
}
```

---

### OtpService

**Location:** `app/Services/OtpService.php`

**Purpose:** OTP generation, validation, and delivery

#### Methods

**generate(string $email, string $type = 'email'): string**

Generate and send OTP code.

```php
public function generate(string $email, string $type = 'email'): string
{
    // Rate limiting check
    $this->checkRateLimit($email);

    // Generate 6-digit code
    $code = (string) random_int(100000, 999999);

    // Store in database with expiration
    OtpCode::updateOrCreate(
        ['identifier' => $email, 'type' => $type],
        [
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]
    );

    // Send via appropriate channel
    if ($type === 'email') {
        Mail::to($email)->queue(new OtpEmail($code));
    } elseif ($type === 'sms') {
        $this->sendSms($email, $code);
    }

    // Log OTP generation
    Log::info('OTP generated', [
        'identifier' => $email,
        'type' => $type,
    ]);

    return $code; // Only for development/testing
}
```

**verify(string $email, string $code, string $type = 'email'): bool**

Verify OTP code.

```php
public function verify(string $email, string $code, string $type = 'email'): bool
{
    $otpRecord = OtpCode::where('identifier', $email)
        ->where('type', $type)
        ->where('expires_at', '>', now())
        ->first();

    if (!$otpRecord) {
        throw new OtpExpiredException('OTP expired or not found');
    }

    // Check attempts limit
    if ($otpRecord->attempts >= 3) {
        $otpRecord->delete();
        throw new TooManyAttemptsException('Too many failed attempts');
    }

    // Verify code
    if (!Hash::check($code, $otpRecord->code)) {
        $otpRecord->increment('attempts');
        return false;
    }

    // Valid OTP - mark as verified and delete
    $otpRecord->update(['verified_at' => now()]);
    $otpRecord->delete();

    return true;
}
```

**checkRateLimit(string $identifier): void**

Check if too many OTP requests.

```php
protected function checkRateLimit(string $identifier): void
{
    $key = "otp-rate-limit:{$identifier}";
    $attempts = Cache::get($key, 0);

    if ($attempts >= 5) {
        throw new TooManyOtpRequestsException(
            'Too many OTP requests. Please try again later.'
        );
    }

    Cache::put($key, $attempts + 1, now()->addMinutes(15));
}
```

---

### TwoFactorAuthService

**Location:** `app/Services/TwoFactorAuthService.php`

**Purpose:** 2FA setup and verification with Google Authenticator

#### Methods

**enable(User $user): array**

Enable 2FA for user and generate QR code.

```php
public function enable(User $user): array
{
    $google2fa = new Google2FA();

    // Generate secret key
    $secret = $google2fa->generateSecretKey();

    // Update user with encrypted secret
    $user->update([
        'two_factor_secret' => encrypt($secret),
        'two_factor_enabled' => true,
    ]);

    // Generate backup codes
    $backupCodes = $this->generateBackupCodes($user);

    // Generate QR code URL
    $qrCodeUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $secret
    );

    return [
        'qr_code_url' => $qrCodeUrl,
        'secret' => $secret,
        'backup_codes' => $backupCodes,
    ];
}
```

**verify(User $user, string $code): bool**

Verify TOTP code.

```php
public function verify(User $user, string $code): bool
{
    if (!$user->two_factor_enabled) {
        return false;
    }

    $google2fa = new Google2FA();
    $secret = decrypt($user->two_factor_secret);

    // Verify TOTP code (allows 1 window tolerance)
    return $google2fa->verifyKey($secret, $code, 1);
}
```

**generateBackupCodes(User $user): array**

Generate recovery codes.

```php
protected function generateBackupCodes(User $user): array
{
    $codes = [];

    for ($i = 0; $i < 8; $i++) {
        $codes[] = strtoupper(Str::random(8));
    }

    // Store encrypted codes
    $user->update([
        'two_factor_recovery_codes' => encrypt(json_encode($codes))
    ]);

    return $codes;
}
```

---

## Media Services

### MediaUploadService

**Location:** `app/Services/MediaUploadService.php`

**Purpose:** File uploads to Cloudflare R2 storage

#### Methods

**uploadPhoto(UploadedFile $file, User $user, array $options = []): string**

Upload photo with processing.

```php
public function uploadPhoto(
    UploadedFile $file,
    User $user,
    array $options = []
): string {
    // Validate file
    $this->validatePhoto($file);

    // Generate unique filename
    $filename = $this->generateFilename($file, $user);

    // Process image (resize, optimize)
    $processedImage = $this->imageProcessor->process($file, [
        'resize' => [800, 800],
        'quality' => 85,
        'format' => 'jpg',
    ]);

    // Upload to R2
    $path = Storage::disk('r2')->putFileAs(
        "photos/{$user->id}",
        $processedImage,
        $filename,
        'public'
    );

    // Generate thumbnails
    if ($options['generate_thumbnails'] ?? true) {
        $this->generateThumbnails($processedImage, $user, $filename);
    }

    return Storage::disk('r2')->url($path);
}
```

**uploadVideo(UploadedFile $file, User $user): string**

Upload video for stories.

```php
public function uploadVideo(UploadedFile $file, User $user): string
{
    // Validate video
    if ($file->getSize() > 50 * 1024 * 1024) { // 50MB
        throw new FileTooLargeException('Video must be less than 50MB');
    }

    $allowedMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
    if (!in_array($file->getMimeType(), $allowedMimes)) {
        throw new InvalidFileTypeException('Invalid video format');
    }

    // Generate filename
    $filename = uniqid('video_') . '.' . $file->getClientOriginalExtension();

    // Upload to R2
    $path = Storage::disk('r2')->putFileAs(
        "videos/{$user->id}",
        $file,
        $filename,
        'public'
    );

    return Storage::disk('r2')->url($path);
}
```

**deleteFile(string $url): bool**

Delete file from storage.

```php
public function deleteFile(string $url): bool
{
    $path = $this->extractPathFromUrl($url);

    return Storage::disk('r2')->delete($path);
}
```

---

### ImageProcessingService

**Location:** `app/Services/ImageProcessingService.php`

**Purpose:** Image manipulation and optimization

#### Methods

**process(UploadedFile $file, array $options): Image**

Process image with given options.

```php
public function process(UploadedFile $file, array $options): Image
{
    $image = InterventionImage::make($file);

    // Auto-orient based on EXIF
    $image->orientate();

    // Resize if needed
    if (isset($options['resize'])) {
        [$width, $height] = $options['resize'];
        $image->fit($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    // Compress
    $quality = $options['quality'] ?? 85;
    $format = $options['format'] ?? 'jpg';
    $image->encode($format, $quality);

    // Apply filters
    if (isset($options['filter'])) {
        $this->applyFilter($image, $options['filter']);
    }

    return $image;
}
```

**generateThumbnail(Image $image, int $width, int $height): Image**

Generate thumbnail.

```php
public function generateThumbnail(Image $image, int $width, int $height): Image
{
    return $image->fit($width, $height, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
}
```

---

## Communication Services

### NotificationService

**Location:** `app/Services/NotificationService.php`

**Purpose:** Push notifications via Expo

#### Methods

**sendPushNotification(User $user, string $title, string $body, array $data = []): void**

Send push notification to user's devices.

```php
public function sendPushNotification(
    User $user,
    string $title,
    string $body,
    array $data = []
): void {
    // Get active device tokens
    $tokens = $user->deviceTokens()
        ->where('is_active', true)
        ->pluck('token')
        ->toArray();

    if (empty($tokens)) {
        return;
    }

    // Build notification payload
    $messages = [];
    foreach ($tokens as $token) {
        $messages[] = [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sound' => 'default',
            'badge' => $user->unreadNotifications()->count(),
            'priority' => 'high',
        ];
    }

    // Send via Expo Push API
    $this->expoPushService->send($messages);
}
```

**sendMatchNotification(User $user, Match $match): void**

Send notification for new match.

```php
public function sendMatchNotification(User $user, Match $match): void
{
    $matchedUser = $match->otherUser($user);

    $this->sendPushNotification(
        $user,
        'New Match!',
        "You matched with {$matchedUser->first_name}!",
        [
            'type' => 'match',
            'match_id' => $match->id,
            'user_id' => $matchedUser->id,
        ]
    );
}
```

**sendMessageNotification(User $recipient, Message $message): void**

Send notification for new message.

```php
public function sendMessageNotification(User $recipient, Message $message): void
{
    $sender = $message->sender;

    $this->sendPushNotification(
        $recipient,
        $sender->first_name,
        $message->content,
        [
            'type' => 'message',
            'chat_id' => $message->chat_id,
            'message_id' => $message->id,
        ]
    );
}
```

---

### PresenceService

**Location:** `app/Services/PresenceService.php`

**Purpose:** User online status and presence tracking

#### Methods

**updateOnlineStatus(User $user, string $status): void**

Update user's online status.

```php
public function updateOnlineStatus(User $user, string $status): void
{
    // Status: 'online', 'away', 'offline'
    Cache::put("user-online-{$user->id}", $status, now()->addMinutes(5));

    // Broadcast to connected users
    broadcast(new UserOnlineStatusChanged($user, $status))->toOthers();

    // Update last active timestamp
    $user->update(['last_active_at' => now()]);
}
```

**isUserOnline(User $user): bool**

Check if user is online.

```php
public function isUserOnline(User $user): bool
{
    $status = Cache::get("user-online-{$user->id}");
    return $status === 'online';
}
```

**getOnlineMatches(User $user): Collection**

Get user's online matches.

```php
public function getOnlineMatches(User $user): Collection
{
    return $user->matches()
        ->where('last_active_at', '>', now()->subMinutes(5))
        ->get();
}
```

**updateTypingStatus(User $user, Chat $chat, bool $typing): void**

Update typing indicator.

```php
public function updateTypingStatus(User $user, Chat $chat, bool $typing): void
{
    broadcast(new UserTypingStatusChanged($user, $chat, $typing))
        ->toOthers();
}
```

---

## Video Services

### VideoSDKService

**Location:** `app/Services/VideoSDKService.php`

**Purpose:** VideoSDK.live integration (primary video provider)

#### Methods

**generateToken(): string**

Generate VideoSDK auth token.

```php
public function generateToken(): string
{
    $apiKey = config('services.videosdk.api_key');
    $secretKey = config('services.videosdk.secret_key');

    $payload = [
        'apikey' => $apiKey,
        'permissions' => ['allow_join', 'allow_mod'],
        'version' => 2,
        'iat' => time(),
        'exp' => time() + 86400, // 24 hours
    ];

    return JWT::encode($payload, $secretKey, 'HS256');
}
```

**createMeeting(): array**

Create VideoSDK meeting room.

```php
public function createMeeting(): array
{
    $token = $this->generateToken();

    $response = Http::withToken($token)
        ->post(config('services.videosdk.api_endpoint') . '/meetings', [
            'region' => 'sg001',
        ]);

    if (!$response->successful()) {
        throw new VideoSDKException('Failed to create meeting');
    }

    return [
        'meeting_id' => $response->json('meetingId'),
        'token' => $token,
    ];
}
```

**validateMeeting(string $meetingId): bool**

Validate meeting ID exists.

```php
public function validateMeeting(string $meetingId): bool
{
    $token = $this->generateToken();

    $response = Http::withToken($token)
        ->get(config('services.videosdk.api_endpoint') . "/meetings/{$meetingId}");

    return $response->successful();
}
```

---

## Advanced Services

### VerificationService

**Location:** `app/Services/VerificationService.php`

**Purpose:** User identity verification system

#### Methods

**submitVerificationRequest(User $user, string $type, array $documents): VerificationRequest**

Submit verification request.

```php
public function submitVerificationRequest(
    User $user,
    string $type,
    array $documents
): VerificationRequest {
    // Validate type
    $validTypes = ['identity', 'photo', 'employment', 'education', 'income'];
    if (!in_array($type, $validTypes)) {
        throw new InvalidVerificationTypeException();
    }

    // Upload documents securely
    $documentPaths = [];
    foreach ($documents as $document) {
        $path = $this->mediaUploadService->uploadDocument($document, $user);
        $documentPaths[] = encrypt($path); // Encrypt sensitive paths
    }

    // Create verification request
    $request = VerificationRequest::create([
        'user_id' => $user->id,
        'verification_type' => $type,
        'documents' => json_encode($documentPaths),
        'status' => 'pending',
        'submitted_at' => now(),
    ]);

    // Notify admins
    $this->notifyAdmins($request);

    return $request;
}
```

**processVerification(VerificationRequest $request, bool $approved, string $notes = null): void**

Admin processes verification.

```php
public function processVerification(
    VerificationRequest $request,
    bool $approved,
    string $notes = null
): void {
    DB::transaction(function () use ($request, $approved, $notes) {
        // Update request
        $request->update([
            'status' => $approved ? 'approved' : 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
            'admin_notes' => $notes,
        ]);

        if ($approved) {
            // Grant verification badge
            $request->user->verifiedBadges()->create([
                'badge_type' => $request->verification_type,
                'verified_at' => now(),
            ]);

            // Delete sensitive documents
            $this->deleteVerificationDocuments($request);
        }

        // Notify user
        $request->user->notify(
            new VerificationProcessed($request, $approved)
        );
    });
}
```

---

### PanicButtonService

**Location:** `app/Services/PanicButtonService.php`

**Purpose:** Emergency panic button system

#### Methods

**activatePanic(User $user, array $location, string $notes = null): PanicActivation**

Activate emergency panic button.

```php
public function activatePanic(
    User $user,
    array $location,
    string $notes = null
): PanicActivation {
    DB::transaction(function () use ($user, $location, $notes) {
        // Create activation record
        $activation = PanicActivation::create([
            'user_id' => $user->id,
            'location_lat' => $location['latitude'],
            'location_lng' => $location['longitude'],
            'notes' => $notes,
            'status' => 'active',
            'activated_at' => now(),
        ]);

        // Notify emergency contacts immediately
        $this->notifyEmergencyContacts($user, $activation);

        // Notify admins
        $this->notifyAdmins($activation);

        // Log critical event
        Log::emergency('Panic button activated', [
            'user_id' => $user->id,
            'location' => $location,
            'activation_id' => $activation->id,
        ]);

        return $activation;
    });
}
```

**notifyEmergencyContacts(User $user, PanicActivation $activation): void**

Send alerts to emergency contacts.

```php
protected function notifyEmergencyContacts(
    User $user,
    PanicActivation $activation
): void {
    $contacts = $user->emergencyContacts()
        ->where('is_verified', true)
        ->get();

    foreach ($contacts as $contact) {
        // Send SMS if phone available
        if ($contact->phone) {
            $message = "EMERGENCY ALERT: {$user->name} has activated their panic button. "
                     . "Location: {$activation->location_lat}, {$activation->location_lng}";
            $this->sendEmergencySMS($contact->phone, $message);
        }

        // Send email
        if ($contact->email) {
            Mail::to($contact->email)->send(
                new EmergencyAlert($user, $activation)
            );
        }
    }
}
```

---

### UsageLimitsService

**Location:** `app/Services/UsageLimitsService.php`

**Purpose:** Track and enforce subscription limits

#### Methods

**checkLimit(User $user, string $feature): bool**

Check if user can use feature.

```php
public function checkLimit(User $user, string $feature): bool
{
    // Get user's subscription limits
    $limits = $this->getLimitsForUser($user);

    // Unlimited access
    if ($limits[$feature] === 'unlimited' || $limits[$feature] === -1) {
        return true;
    }

    // Get current usage
    $usage = $this->getUsage($user, $feature);

    return $usage < $limits[$feature];
}
```

**incrementUsage(User $user, string $feature): void**

Increment feature usage counter.

```php
public function incrementUsage(User $user, string $feature): void
{
    $usageLimits = $user->usageLimits()->firstOrCreate([
        'user_id' => $user->id,
    ]);

    switch ($feature) {
        case 'likes':
            $usageLimits->decrement('likes_remaining');
            break;
        case 'messages':
            $usageLimits->decrement('messages_remaining');
            break;
        case 'profile_views':
            $usageLimits->decrement('profile_views_remaining');
            break;
    }
}
```

---

## Service Patterns

### Transaction Pattern

```php
public function complexOperation($data)
{
    DB::beginTransaction();

    try {
        // Step 1
        $result1 = $this->step1($data);

        // Step 2
        $result2 = $this->step2($result1);

        // Step 3
        $this->step3($result2);

        DB::commit();

        return $result2;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

### Repository Pattern (with Eloquent)

```php
class UserService
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findActive(): Collection
    {
        return User::where('is_active', true)
            ->whereNull('disabled_at')
            ->get();
    }
}
```

### Strategy Pattern

```php
interface PaymentProcessor
{
    public function process(Payment $payment): bool;
}

class StripeProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // Stripe logic
    }
}

class PaypalProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // PayPal logic
    }
}
```

---

## Testing Services

### Unit Test Example

```php
// tests/Unit/AuthServiceTest.php
use Tests\TestCase;
use App\Services\AuthService;

class AuthServiceTest extends TestCase
{
    public function test_authenticate_with_valid_credentials()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123')
        ]);

        $authService = app(AuthService::class);

        $result = $authService->authenticate([
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotNull($result['token']);
    }

    public function test_authenticate_with_invalid_credentials()
    {
        $this->expectException(AuthenticationException::class);

        $authService = app(AuthService::class);

        $authService->authenticate([
            'email' => 'invalid@example.com',
            'password' => 'wrong',
        ]);
    }
}
```

---

## Best Practices

### 1. Type Hints

Always use type hints:

```php
public function createUser(array $data): User
{
    // Implementation
}
```

### 2. DocBlock Comments

Document public methods:

```php
/**
 * Authenticate user with email and password.
 *
 * @param array $credentials ['email', 'password']
 * @return array ['user' => User, 'token' => string]
 * @throws AuthenticationException
 */
public function authenticate(array $credentials): array
{
    // Implementation
}
```

### 3. Service Registration

Register singletons in AppServiceProvider:

```php
// app/Providers/AppServiceProvider.php
public function register()
{
    $this->app->singleton(AuthService::class);
    $this->app->singleton(OtpService::class);
}
```

### 4. Exception Handling

Throw specific exceptions:

```php
if (!$user) {
    throw new UserNotFoundException("User not found with ID: {$userId}");
}
```

### 5. Dependency Injection

Inject dependencies, don't instantiate:

```php
// Good
public function __construct(
    private AuthService $authService
) {}

// Bad
public function someMethod()
{
    $authService = new AuthService();
}
```

---

**Services are the backbone of YorYor's business logic. Understanding and using them properly is key to maintaining code quality.**

*Last Updated: October 2025*

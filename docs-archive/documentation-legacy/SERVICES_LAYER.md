# YorYor Services Layer Documentation

## Table of Contents
- [Overview](#overview)
- [Service Architecture](#service-architecture)
- [Authentication Services](#authentication-services)
- [Media Services](#media-services)
- [Communication Services](#communication-services)
- [Matching & Discovery Services](#matching--discovery-services)
- [Safety & Security Services](#safety--security-services)
- [Payment Services](#payment-services)
- [Utility Services](#utility-services)
- [Service Patterns](#service-patterns)

---

## Overview

YorYor's service layer encapsulates business logic and complex operations, providing a clean separation between controllers and models. Services handle cross-cutting concerns, third-party integrations, and complex workflows.

### Service Principles
- **Single Responsibility**: Each service has one clear purpose
- **Dependency Injection**: Services injected via constructor
- **Testability**: Services designed for easy unit testing
- **Reusability**: Common logic extracted into services
- **Maintainability**: Complex logic isolated from controllers

### Service Location
All services are located in: `app/Services/`

---

## Service Architecture

### Service Registration
Services can be registered in `app/Providers/AppServiceProvider.php`:

```php
public function register()
{
    $this->app->singleton(AuthService::class);
    $this->app->singleton(OtpService::class);
    $this->app->bind(PaymentManager::class, function ($app) {
        return new PaymentManager(config('services.stripe.key'));
    });
}
```

### Service Usage

**In Controllers:**
```php
class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected OtpService $otpService
    ) {}

    public function authenticate(Request $request)
    {
        return $this->authService->authenticate($request->validated());
    }
}
```

**In Livewire Components:**
```php
class Register extends Component
{
    public function register()
    {
        $authService = app(AuthService::class);
        $user = $authService->createUser($this->formData);
    }
}
```

---

## Authentication Services

### AuthService
**Location**: `app/Services/AuthService.php`

Handles user authentication, registration, and session management.

#### Methods

**authenticate(array $credentials): array**
Authenticate user with email/password or OTP.

```php
public function authenticate(array $credentials): array
{
    // Check if OTP authentication
    if (isset($credentials['otp'])) {
        return $this->authenticateWithOtp($credentials['email'], $credentials['otp']);
    }

    // Password authentication
    if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
        $user = Auth::user();

        // Check 2FA
        if ($user->two_factor_enabled) {
            return ['requires_2fa' => true, 'user' => $user];
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ];
    }

    throw new AuthenticationException('Invalid credentials');
}
```

**createUser(array $data): User**
Create new user account.

```php
public function createUser(array $data): User
{
    $user = User::create([
        'email' => $data['email'],
        'password' => Hash::make($data['password']),
        'name' => $data['name'],
        'uuid' => Str::uuid(),
    ]);

    // Create associated profile
    $user->profile()->create([
        'date_of_birth' => $data['date_of_birth'],
        'gender' => $data['gender'],
        'country_id' => $data['country_id'],
    ]);

    // Send welcome email
    $user->notify(new WelcomeNotification());

    return $user;
}
```

**checkEmailExists(string $email): array**
Check if email is registered and determine auth methods.

```php
public function checkEmailExists(string $email): array
{
    $user = User::where('email', $email)->first();

    if (!$user) {
        return [
            'exists' => false,
            'can_use_otp' => true,
        ];
    }

    return [
        'exists' => true,
        'requires_password' => !empty($user->password),
        'can_use_otp' => true,
        'has_2fa' => $user->two_factor_enabled,
    ];
}
```

**logout(User $user): void**
Logout user and revoke tokens.

---

### OtpService
**Location**: `app/Services/OtpService.php`

Manages OTP generation, validation, and delivery.

#### Methods

**generate(string $email): string**
Generate and send OTP code.

```php
public function generate(string $email): string
{
    // Rate limiting check
    $this->checkRateLimit($email);

    // Generate 6-digit code
    $code = (string) random_int(100000, 999999);

    // Store in database
    OtpCode::updateOrCreate(
        ['email' => $email],
        [
            'code' => Hash::make($code),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ]
    );

    // Send via email
    Mail::to($email)->queue(new OtpMail($code));

    return $code; // Return for dev/testing, don't return in production
}
```

**verify(string $email, string $code): bool**
Verify OTP code.

```php
public function verify(string $email, string $code): bool
{
    $otpRecord = OtpCode::where('email', $email)
        ->where('expires_at', '>', now())
        ->first();

    if (!$otpRecord) {
        throw new OtpExpiredException('OTP expired or not found');
    }

    // Check attempts
    if ($otpRecord->attempts >= 3) {
        $otpRecord->delete();
        throw new TooManyAttemptsException('Too many failed attempts');
    }

    // Verify code
    if (!Hash::check($code, $otpRecord->code)) {
        $otpRecord->increment('attempts');
        return false;
    }

    // Valid OTP
    $otpRecord->delete();
    return true;
}
```

**checkRateLimit(string $email): void**
Check if too many OTP requests.

---

### TwoFactorAuthService
**Location**: `app/Services/TwoFactorAuthService.php`

Handles two-factor authentication setup and verification.

#### Methods

**enable(User $user): array**
Enable 2FA for user.

```php
public function enable(User $user): array
{
    $google2fa = new Google2FA();
    $secret = $google2fa->generateSecretKey();

    $user->update([
        'two_factor_secret' => encrypt($secret),
        'two_factor_enabled' => true,
    ]);

    // Generate backup codes
    $backupCodes = $this->generateBackupCodes($user);

    $qrCodeUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $secret
    );

    return [
        'qr_code' => $qrCodeUrl,
        'secret' => $secret,
        'backup_codes' => $backupCodes,
    ];
}
```

**verify(User $user, string $code): bool**
Verify 2FA code.

**generateBackupCodes(User $user): array**
Generate backup codes for 2FA recovery.

---

### ValidationService
**Location**: `app/Services/ValidationService.php`

Centralized validation logic for complex scenarios.

#### Methods

**validateProfileCompleteness(User $user): array**
Check profile completion status.

```php
public function validateProfileCompleteness(User $user): array
{
    $sections = [
        'basic_info' => $this->validateBasicInfo($user),
        'photos' => $user->photos()->count() >= 2,
        'cultural_profile' => $user->culturalProfile()->exists(),
        'preferences' => $user->preferences()->exists(),
        'career_profile' => $user->careerProfile()->exists(),
        'family_preferences' => $user->familyPreferences()->exists(),
    ];

    $completed = array_filter($sections);
    $percentage = (count($completed) / count($sections)) * 100;

    return [
        'completion_percentage' => $percentage,
        'completed_sections' => array_keys($completed),
        'incomplete_sections' => array_keys(array_diff_key($sections, $completed)),
    ];
}
```

---

## Media Services

### MediaUploadService
**Location**: `app/Services/MediaUploadService.php`

Handles file uploads to cloud storage (Cloudflare R2).

#### Methods

**uploadPhoto(UploadedFile $file, User $user, array $options = []): string**
Upload photo to cloud storage.

```php
public function uploadPhoto(UploadedFile $file, User $user, array $options = []): string
{
    // Validate file
    $this->validatePhoto($file);

    // Generate unique filename
    $filename = $this->generateFilename($file, $user);

    // Process image
    $processedImage = $this->imageProcessor->process($file, [
        'resize' => [800, 800],
        'quality' => 85,
        'format' => 'jpg',
    ]);

    // Upload to R2
    $path = Storage::disk('cloudflare-r2')->putFileAs(
        "photos/{$user->id}",
        $processedImage,
        $filename,
        'public'
    );

    // Generate thumbnails
    if ($options['generate_thumbnails'] ?? true) {
        $this->generateThumbnails($processedImage, $user, $filename);
    }

    return Storage::disk('cloudflare-r2')->url($path);
}
```

**uploadVideo(UploadedFile $file, User $user): string**
Upload video (for stories).

**deleteFile(string $url): bool**
Delete file from cloud storage.

```php
public function deleteFile(string $url): bool
{
    $path = $this->extractPathFromUrl($url);
    return Storage::disk('cloudflare-r2')->delete($path);
}
```

---

### ImageProcessingService
**Location**: `app/Services/ImageProcessingService.php`

Image manipulation and optimization using Intervention Image.

#### Methods

**process(UploadedFile $file, array $options): Image**
Process image with given options.

```php
public function process(UploadedFile $file, array $options): Image
{
    $image = InterventionImage::make($file);

    // Resize
    if (isset($options['resize'])) {
        [$width, $height] = $options['resize'];
        $image->fit($width, $height, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
    }

    // Compress
    $quality = $options['quality'] ?? 85;
    $image->encode('jpg', $quality);

    // Apply filters
    if (isset($options['filter'])) {
        $this->applyFilter($image, $options['filter']);
    }

    return $image;
}
```

**generateThumbnail(Image $image, int $width, int $height): Image**
Generate thumbnail from image.

**detectFaces(Image $image): array**
Detect faces in image (for verification).

**validatePhotoContent(UploadedFile $file): bool**
Check for inappropriate content using AI (integration ready).

---

## Communication Services

### NotificationService
**Location**: `app/Services/NotificationService.php`

Handles push notifications via Expo.

#### Methods

**sendPushNotification(User $user, string $title, string $body, array $data = []): void**
Send push notification to user's devices.

```php
public function sendPushNotification(User $user, string $title, string $body, array $data = []): void
{
    $tokens = $user->deviceTokens()
        ->where('is_active', true)
        ->pluck('token')
        ->toArray();

    if (empty($tokens)) {
        return;
    }

    foreach ($tokens as $token) {
        ExpoNotification::create([
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sound' => 'default',
            'badge' => $user->unreadNotifications()->count(),
        ])->send();
    }
}
```

**sendMatchNotification(User $user, Match $match): void**
Send notification for new match.

**sendMessageNotification(User $user, Message $message): void**
Send notification for new message.

---

### ExpoPushService
**Location**: `app/Services/ExpoPushService.php`

Wrapper for Expo push notification API.

#### Methods

**send(array $messages): array**
Send batch push notifications.

```php
public function send(array $messages): array
{
    $chunks = array_chunk($messages, 100); // Expo limit
    $results = [];

    foreach ($chunks as $chunk) {
        $response = Http::post('https://exp.host/--/api/v2/push/send', $chunk);
        $results[] = $response->json();
    }

    return $results;
}
```

---

### PresenceService
**Location**: `app/Services/PresenceService.php`

Manages user online status and presence.

#### Methods

**updateOnlineStatus(User $user, string $status): void**
Update user's online status.

```php
public function updateOnlineStatus(User $user, string $status): void
{
    Cache::put("user-online-{$user->id}", $status, now()->addMinutes(5));

    broadcast(new UserOnlineStatusChanged($user, $status))->toOthers();

    // Update last active
    $user->update(['last_active_at' => now()]);
}
```

**isUserOnline(User $user): bool**
Check if user is currently online.

```php
public function isUserOnline(User $user): bool
{
    return Cache::has("user-online-{$user->id}");
}
```

**getOnlineMatches(User $user): Collection**
Get user's matches that are currently online.

**updateTypingStatus(User $user, Chat $chat, bool $typing): void**
Update typing indicator in chat.

---

## Matching & Discovery Services

### MatchmakerService
**Location**: `app/Services/MatchmakerService.php`

Professional matchmaker functionality.

#### Methods

**createIntroduction(Matchmaker $matchmaker, User $clientA, User $clientB, string $message): Introduction**
Create introduction between two clients.

```php
public function createIntroduction(
    Matchmaker $matchmaker,
    User $clientA,
    User $clientB,
    string $message
): Introduction {
    // Verify both are clients
    $this->verifyClientRelationship($matchmaker, $clientA);
    $this->verifyClientRelationship($matchmaker, $clientB);

    // Check compatibility
    $compatibility = $this->calculateCompatibility($clientA, $clientB);

    $introduction = Introduction::create([
        'matchmaker_id' => $matchmaker->id,
        'client_a_id' => $clientA->id,
        'client_b_id' => $clientB->id,
        'message' => $message,
        'compatibility_score' => $compatibility,
        'status' => 'pending',
    ]);

    // Notify both clients
    $clientA->notify(new IntroductionReceived($introduction));
    $clientB->notify(new IntroductionReceived($introduction));

    return $introduction;
}
```

**calculateCompatibility(User $userA, User $userB): int**
Calculate compatibility score between two users.

**respondToIntroduction(Introduction $introduction, User $user, bool $accept): void**
Handle user response to introduction.

---

### FamilyApprovalService
**Location**: `app/Services/FamilyApprovalService.php`

Family approval system for matches.

#### Methods

**inviteFamilyMember(User $user, string $email, string $relationship): FamilyMember**
Invite family member to review matches.

**requestApproval(User $user, Match $match): FamilyApproval**
Request family approval for a match.

**submitApproval(FamilyMember $member, FamilyApproval $approval, bool $approved, string $notes = null): void**
Family member submits approval/disapproval.

---

## Safety & Security Services

### VerificationService
**Location**: `app/Services/VerificationService.php`

User verification system.

#### Methods

**submitVerificationRequest(User $user, string $type, array $documents): VerificationRequest**
Submit verification request.

```php
public function submitVerificationRequest(
    User $user,
    string $type,
    array $documents
): VerificationRequest {
    // Upload documents securely
    $documentPaths = [];
    foreach ($documents as $document) {
        $path = $this->mediaUploadService->uploadDocument($document, $user);
        $documentPaths[] = encrypt($path); // Encrypt paths
    }

    $request = VerificationRequest::create([
        'user_id' => $user->id,
        'type' => $type,
        'documents' => $documentPaths,
        'status' => 'pending',
        'submitted_at' => now(),
    ]);

    // Notify admins
    $this->notifyAdmins($request);

    return $request;
}
```

**processVerification(VerificationRequest $request, bool $approved, string $notes = null): void**
Admin processes verification request.

```php
public function processVerification(
    VerificationRequest $request,
    bool $approved,
    string $notes = null
): void {
    $request->update([
        'status' => $approved ? 'approved' : 'rejected',
        'reviewed_at' => now(),
        'reviewer_notes' => $notes,
    ]);

    if ($approved) {
        // Grant badge
        $request->user->verifiedBadges()->create([
            'type' => $request->type,
            'verified_at' => now(),
        ]);

        // Delete documents
        $this->deleteVerificationDocuments($request);
    }

    // Notify user
    $request->user->notify(new VerificationProcessed($request, $approved));
}
```

**deleteVerificationDocuments(VerificationRequest $request): void**
Delete documents after verification.

---

### PanicButtonService
**Location**: `app/Services/PanicButtonService.php`

Emergency panic button system.

#### Methods

**activatePanic(User $user, array $location, string $notes = null): PanicActivation**
Activate panic button.

```php
public function activatePanic(
    User $user,
    array $location,
    string $notes = null
): PanicActivation {
    $activation = PanicActivation::create([
        'user_id' => $user->id,
        'location' => $location,
        'notes' => $notes,
        'status' => 'active',
        'activated_at' => now(),
    ]);

    // Notify emergency contacts
    $this->notifyEmergencyContacts($user, $activation);

    // Notify admins
    $this->notifyAdmins($activation);

    // Log incident
    Log::emergency('Panic button activated', [
        'user_id' => $user->id,
        'location' => $location,
    ]);

    return $activation;
}
```

**notifyEmergencyContacts(User $user, PanicActivation $activation): void**
Send alerts to emergency contacts.

```php
protected function notifyEmergencyContacts(User $user, PanicActivation $activation): void
{
    $contacts = $user->emergencyContacts()->where('verified', true)->get();

    foreach ($contacts as $contact) {
        // Send SMS
        if ($contact->phone) {
            $this->sendEmergencySMS($contact->phone, $user, $activation);
        }

        // Send email
        if ($contact->email) {
            Mail::to($contact->email)->send(new EmergencyAlert($user, $activation));
        }
    }
}
```

**cancelPanic(PanicActivation $activation): void**
Cancel false alarm panic.

**resolvePanic(PanicActivation $activation, string $resolution): void**
Admin resolves panic incident.

---

### EnhancedReportingService
**Location**: `app/Services/EnhancedReportingService.php`

User reporting and moderation system.

#### Methods

**submitReport(User $reporter, User $reported, string $category, string $details, array $evidence = []): Report**
Submit user report.

```php
public function submitReport(
    User $reporter,
    User $reported,
    string $category,
    string $details,
    array $evidence = []
): Report {
    $report = Report::create([
        'reporter_id' => $reporter->id,
        'reported_user_id' => $reported->id,
        'category' => $category,
        'details' => $details,
        'status' => 'pending',
    ]);

    // Upload evidence
    foreach ($evidence as $file) {
        $path = $this->mediaUploadService->uploadDocument($file, $reporter);
        $report->evidence()->create(['file_path' => $path]);
    }

    // Automatic actions for severe categories
    if (in_array($category, ['harassment', 'threat', 'illegal_content'])) {
        $this->takeAutomaticAction($reported, $category);
    }

    // Update safety score
    $this->updateSafetyScore($reported);

    // Notify moderation team
    $this->notifyModerators($report);

    return $report;
}
```

**processReport(Report $report, string $action, string $notes = null): void**
Moderator processes report.

**updateSafetyScore(User $user): void**
Update user's safety score.

---

### PrivacyService
**Location**: `app/Services/PrivacyService.php`

Privacy and data protection features.

#### Methods

**exportUserData(User $user): string**
Export all user data (GDPR).

```php
public function exportUserData(User $user): string
{
    $data = [
        'profile' => $user->profile->toArray(),
        'photos' => $user->photos->map(fn($p) => $p->url),
        'preferences' => $user->preferences->toArray(),
        'matches' => $user->matches->map(fn($m) => [
            'matched_with' => $m->otherUser($user)->name,
            'matched_at' => $m->created_at,
        ]),
        'messages' => $user->messages->map(fn($m) => [
            'content' => $m->content,
            'sent_at' => $m->created_at,
            'chat_id' => $m->chat_id,
        ]),
        'activity' => $user->activities->toArray(),
    ];

    $filename = "user-data-{$user->id}-" . now()->format('Y-m-d') . '.json';
    Storage::put("exports/{$filename}", json_encode($data, JSON_PRETTY_PRINT));

    return $filename;
}
```

**deleteUserData(User $user): void**
Delete all user data (right to be forgotten).

```php
public function deleteUserData(User $user): void
{
    DB::transaction(function () use ($user) {
        // Delete photos
        foreach ($user->photos as $photo) {
            $this->mediaUploadService->deleteFile($photo->url);
            $photo->delete();
        }

        // Anonymize messages
        $user->messages()->update([
            'user_id' => null,
            'content' => '[Deleted User]',
        ]);

        // Delete matches
        $user->matches()->delete();

        // Delete profile data
        $user->profile()->delete();
        $user->preferences()->delete();
        $user->culturalProfile()->delete();

        // Delete user
        $user->delete();
    });
}
```

---

## Payment Services

### PaymentManager
**Location**: `app/Services/PaymentManager.php`

Payment processing (Stripe integration ready).

#### Methods

**createSubscription(User $user, string $planId): Subscription**
Create subscription for user.

**cancelSubscription(Subscription $subscription): void**
Cancel subscription.

**processPayment(User $user, float $amount, string $description): Payment**
Process one-time payment.

---

### UsageLimitsService
**Location**: `app/Services/UsageLimitsService.php`

Track and enforce usage limits by subscription tier.

#### Methods

**checkLimit(User $user, string $feature): bool**
Check if user can use feature.

```php
public function checkLimit(User $user, string $feature): bool
{
    $limits = $this->getLimitsForUser($user);

    if ($limits[$feature] === 'unlimited') {
        return true;
    }

    $usage = $this->getUsage($user, $feature);

    return $usage < $limits[$feature];
}
```

**incrementUsage(User $user, string $feature): void**
Increment feature usage counter.

**resetMonthlyUsage(): void**
Reset usage counters monthly (via scheduled job).

---

## Utility Services

### AgoraService
**Location**: `app/Services/AgoraService.php`

Agora video calling integration.

#### Methods

**generateToken(string $channelName, int $uid): string**
Generate Agora RTC token.

**createChannel(string $channelName): array**
Create Agora channel.

---

### VideoSDKService
**Location**: `app/Services/VideoSDKService.php`

VideoSDK integration (primary video provider).

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

**validateMeeting(string $meetingId): bool**
Validate meeting ID.

---

### PrayerTimeService
**Location**: `app/Services/PrayerTimeService.php`

Islamic prayer times calculation.

#### Methods

**getPrayerTimes(User $user, Carbon $date): array**
Get prayer times for user's location.

```php
public function getPrayerTimes(User $user, Carbon $date): array
{
    $location = $user->profile->location;

    // Calculate prayer times based on location and date
    // Using astronomical calculations
    return [
        'fajr' => '05:30',
        'sunrise' => '06:45',
        'dhuhr' => '12:30',
        'asr' => '15:45',
        'maghrib' => '18:15',
        'isha' => '19:30',
    ];
}
```

---

### CacheService
**Location**: `app/Services/CacheService.php`

Centralized caching logic.

#### Methods

**rememberUser(int $userId, Closure $callback): User**
Cache user data.

**rememberProfileCompletion(User $user): array**
Cache profile completion status.

**invalidateUserCache(User $user): void**
Clear user-related caches.

---

### ErrorHandlingService
**Location**: `app/Services/ErrorHandlingService.php`

Centralized error handling and logging.

#### Methods

**handle(Exception $exception, array $context = []): void**
Handle exception with proper logging.

**notifyAdmins(Exception $exception): void**
Notify admins of critical errors.

---

### MonitoringService
**Location**: `app/Services/MonitoringService.php`

Application monitoring and metrics.

#### Methods

**trackEvent(string $event, array $data = []): void**
Track application event.

**recordMetric(string $metric, float $value): void**
Record performance metric.

---

### LoggingService
**Location**: `app/Services/LoggingService.php`

Structured logging service.

#### Methods

**logUserAction(User $user, string $action, array $data = []): void**
Log user action for audit trail.

```php
public function logUserAction(User $user, string $action, array $data = []): void
{
    Log::info('User action', [
        'user_id' => $user->id,
        'action' => $action,
        'data' => $data,
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
        'timestamp' => now(),
    ]);
}
```

---

## Service Patterns

### Repository Pattern
For data access abstraction:

```php
class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
```

### Strategy Pattern
For interchangeable algorithms:

```php
interface PaymentProcessor
{
    public function process(Payment $payment): bool;
}

class StripePaymentProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // Stripe-specific logic
    }
}

class PaypalPaymentProcessor implements PaymentProcessor
{
    public function process(Payment $payment): bool
    {
        // PayPal-specific logic
    }
}
```

### Observer Pattern
For event-driven logic:

```php
class UserObserver
{
    public function created(User $user)
    {
        app(NotificationService::class)->sendWelcomeNotification($user);
        app(LoggingService::class)->logUserAction($user, 'account_created');
    }
}
```

### Factory Pattern
For object creation:

```php
class NotificationFactory
{
    public function create(string $type, array $data): Notification
    {
        return match($type) {
            'email' => new EmailNotification($data),
            'push' => new PushNotification($data),
            'sms' => new SmsNotification($data),
        };
    }
}
```

---

## Best Practices

### Service Design
1. **Single Responsibility**: One service, one purpose
2. **Dependency Injection**: Inject dependencies via constructor
3. **Type Hinting**: Use type hints for parameters and return types
4. **Exception Handling**: Throw specific exceptions
5. **Documentation**: DocBlock comments for all public methods

### Testing Services
```php
class AuthServiceTest extends TestCase
{
    public function test_authenticate_with_valid_credentials()
    {
        $authService = new AuthService(new OtpService());

        $result = $authService->authenticate([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertEquals('success', $result['status']);
    }
}
```

### Service Organization
- Group related services in subdirectories
- Use service providers for registration
- Keep services focused and small
- Extract common logic to utility services

---

*Services are the backbone of YorYor's business logic, providing clean, testable, and maintainable code.*

Last Updated: September 2025
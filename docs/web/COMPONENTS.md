# YorYor Livewire Components Catalog

## Table of Contents

- [Overview](#overview)
- [Architecture](#architecture)
- [Authentication Components](#authentication-components)
- [Profile Components](#profile-components)
- [Dashboard Components](#dashboard-components)
- [Page Components](#page-components)
- [Admin Components](#admin-components)
- [Settings Components](#settings-components)
- [Reusable Components](#reusable-components)
- [Component Lifecycle](#component-lifecycle)
- [Event System](#event-system)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

---

## Overview

YorYor's frontend is built using **Livewire 3.6** with **Flux 2.1** UI components, providing a modern, reactive user interface without the complexity of a traditional SPA. The application includes **60+ Livewire components** organized by feature area, each handling both server-side logic and frontend rendering.

### Technology Stack

- **Livewire 3.6**: Full-stack reactive components
- **Flux 2.1**: UI component library
- **Alpine.js 3**: Lightweight client-side interactivity
- **Tailwind CSS 4**: Utility-first CSS framework
- **Lucide Icons**: Modern icon system
- **Laravel Reverb**: WebSocket integration

### Key Benefits

1. **Server-Side Rendering**: SEO-friendly, fast initial load
2. **Real-Time Updates**: WebSocket integration for live features
3. **No API Complexity**: Direct model access in components
4. **Stateful Components**: Properties persist across requests
5. **Progressive Enhancement**: JavaScript enhances, doesn't require

---

## Architecture

### Component Organization Pattern

YorYor follows a hierarchical component structure:

```
Pages/                    # Full-page components (routes)
  └─ DiscoverPage        # Contains feature components
      ├─ SwipeCards      # Feature component
      └─ ProfileModal    # Feature component

Dashboard/                # Feature-specific components
  ├─ SwipeCards          # Main discovery interface
  ├─ DiscoveryGrid       # Alternative grid view
  ├─ ProfileModal        # Profile detail modal
  └─ StoriesBar          # Stories feature

Profile/                  # Profile editing components
  ├─ BasicInfo           # Basic profile fields
  ├─ Photos              # Photo management
  ├─ Preferences         # Match preferences
  └─ CulturalBackground  # Cultural info

Components/               # Reusable utility components
  ├─ UnifiedSidebar      # Navigation sidebar
  ├─ LanguageSwitcher    # Language selection
  └─ PanicButton         # Emergency button
```

### Component Communication

Livewire components communicate through:

1. **Events**: `$this->dispatch('event-name', data: $data)`
2. **Properties**: Public properties auto-sync with frontend
3. **Actions**: Methods called via `wire:click="methodName"`
4. **WebSocket**: Real-time updates via Laravel Echo

---

## Authentication Components

Location: `app/Livewire/Auth/`

### Register.php

Multi-step registration wizard with real-time validation.

**Purpose**: Guide users through account creation process.

**Public Properties**:
```php
public int $currentStep = 1;
public string $email = '';
public string $password = '';
public string $first_name = '';
public string $last_name = '';
public string $date_of_birth = '';
public string $gender = '';
public int $country_id = 1;
```

**Key Methods**:
- `nextStep()`: Advance to next registration step
- `previousStep()`: Go back to previous step
- `submitStep1()`: Validate and save email/password
- `submitStep2()`: Validate and save profile info
- `completeRegistration()`: Finalize account creation

**Events Dispatched**:
- `registration-completed`: When user finishes registration
- `step-changed`: When moving between steps

**Usage**:
```blade
<livewire:auth.register />
```

**Features**:
- Step-by-step wizard (3 steps)
- Real-time validation on each field
- Progress indicator
- Country selection with search
- Date picker for birthdate
- Password strength meter

---

### Login.php

Login form with multiple authentication methods.

**Purpose**: User authentication with email/password or OTP.

**Public Properties**:
```php
public string $email = '';
public string $password = '';
public bool $remember = false;
public string $loginMethod = 'password'; // 'password' or 'otp'
```

**Key Methods**:
- `authenticate()`: Validate credentials and log user in
- `sendOtp()`: Send one-time password to email
- `verifyOtp()`: Verify OTP code

**Events Dispatched**:
- `login-success`: After successful authentication
- `otp-sent`: When OTP is sent

**Usage**:
```blade
<livewire:auth.login />
```

**Features**:
- Email/password authentication
- OTP login option
- "Remember me" functionality
- Error handling with user-friendly messages
- Social login integration (Google)

---

### VerifyEmail.php

Email verification component for new users.

**Purpose**: Verify user email address after registration.

**Public Properties**:
```php
public string $code = '';
public int $resendTimer = 60;
public bool $canResend = false;
```

**Key Methods**:
- `verify()`: Verify the entered code
- `resendCode()`: Send new verification code
- `updateTimer()`: Countdown timer for resend

**Events Dispatched**:
- `email-verified`: After successful verification
- `code-resent`: When new code is sent

**Usage**:
```blade
<livewire:auth.verify-email />
```

**Features**:
- 6-digit verification code input
- Auto-submit when code complete
- Resend code with countdown timer
- Auto-focus on input fields
- Code expiration handling

---

### ForgotPassword.php

Password reset request form.

**Purpose**: Initiate password reset process.

**Public Properties**:
```php
public string $email = '';
public bool $emailSent = false;
```

**Key Methods**:
- `sendResetLink()`: Send password reset email

**Usage**:
```blade
<livewire:auth.forgot-password />
```

---

### ResetPassword.php

Password reset form with token validation.

**Purpose**: Allow users to set new password.

**Public Properties**:
```php
public string $token = '';
public string $email = '';
public string $password = '';
public string $password_confirmation = '';
```

**Key Methods**:
- `resetPassword()`: Update password with new value

**Usage**:
```blade
<livewire:auth.reset-password :token="$token" :email="$email" />
```

---

### ConfirmPassword.php

Password confirmation for sensitive operations.

**Purpose**: Confirm user identity before critical actions.

**Public Properties**:
```php
public string $password = '';
```

**Key Methods**:
- `confirm()`: Verify password matches

**Usage**:
```blade
<livewire:auth.confirm-password />
```

---

## Profile Components

Location: `app/Livewire/Profile/`

### BasicInfo.php

Basic profile information editor.

**Purpose**: Edit core profile details (name, bio, location).

**Public Properties**:
```php
public User $user;
public string $first_name = '';
public string $last_name = '';
public string $bio = '';
public string $city = '';
public int $country_id;
```

**Key Methods**:
- `mount()`: Initialize component with user data
- `save()`: Update basic profile information
- `rules()`: Validation rules for fields

**Events Dispatched**:
- `profile-updated`: After successful save

**Events Listened**:
- None

**Usage**:
```blade
<livewire:profile.basic-info :user="$user" />
```

**Validation Rules**:
```php
protected function rules(): array
{
    return [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'bio' => 'nullable|string|max:1000',
        'city' => 'nullable|string|max:255',
        'country_id' => 'required|exists:countries,id',
    ];
}
```

---

### Photos.php

Photo management component with drag-and-drop upload.

**Purpose**: Upload, reorder, and manage profile photos.

**Public Properties**:
```php
use WithFileUploads;

public $photos = [];
public $newPhoto;
public int $maxPhotos = 6;
public bool $uploading = false;
```

**Key Methods**:
- `uploadPhoto()`: Handle new photo upload
- `deletePhoto($photoId)`: Remove a photo
- `reorderPhotos($order)`: Update photo order
- `setPrimaryPhoto($photoId)`: Set main profile photo

**Events Dispatched**:
- `photo-uploaded`: After successful upload
- `photo-deleted`: After photo removal
- `photos-reordered`: After reordering

**Usage**:
```blade
<livewire:profile.photos />
```

**Features**:
- Drag-and-drop upload
- Image preview before upload
- Reorder photos with drag-and-drop
- Set primary photo
- Upload progress indicator
- Maximum 6 photos per profile
- Image validation (type, size, dimensions)

**Code Example**:
```php
public function uploadPhoto()
{
    $this->validate([
        'newPhoto' => 'required|image|max:5120|dimensions:min_width=400,min_height=400',
    ]);

    $this->uploading = true;

    // Upload to Cloudflare R2 via MediaUploadService
    $path = app(MediaUploadService::class)->upload(
        $this->newPhoto,
        'profile-photos',
        $this->user->id
    );

    // Create photo record
    UserPhoto::create([
        'user_id' => $this->user->id,
        'path' => $path,
        'order' => $this->photos->count() + 1,
        'is_primary' => $this->photos->isEmpty(),
    ]);

    $this->uploading = false;
    $this->dispatch('photo-uploaded');
    $this->photos = $this->user->photos()->orderBy('order')->get();
    $this->reset('newPhoto');
}
```

---

### Preferences.php

Match preference editor with real-time preview.

**Purpose**: Set preferences for match discovery.

**Public Properties**:
```php
public int $minAge = 18;
public int $maxAge = 50;
public int $distance = 50; // km
public array $genderPreference = [];
public array $religiousLevels = [];
public bool $showMatchCount = true;
public int $matchCount = 0;
```

**Key Methods**:
- `mount()`: Load current preferences
- `save()`: Update preferences
- `updateMatchCount()`: Calculate matching profiles
- `updatedMinAge()`: Validate age range
- `updatedMaxAge()`: Validate age range

**Events Dispatched**:
- `preferences-updated`: After save
- `match-count-updated`: When count changes

**Usage**:
```blade
<livewire:profile.preferences />
```

**Features**:
- Age range slider (18-80)
- Distance radius selector (1-500 km)
- Multi-select filters (religion, education, etc.)
- Real-time match count preview
- Saved preferences auto-apply to discovery

---

### CulturalBackground.php

Cultural and religious profile editor.

**Purpose**: Capture cultural and religious information.

**Public Properties**:
```php
public string $religion = '';
public string $sect = '';
public string $religiosity = '';
public string $prayer_frequency = '';
public array $languages = [];
public string $dietary_preference = '';
```

**Key Methods**:
- `mount()`: Load cultural profile
- `save()`: Update cultural information
- `updatedReligion()`: Update available sects

**Events Dispatched**:
- `cultural-profile-updated`

**Usage**:
```blade
<livewire:profile.cultural-background />
```

**Features**:
- Religion selection (Islam, Christian, Jewish, etc.)
- Sect selection (conditional on religion)
- Religiosity level (Very religious, Moderate, Not religious)
- Prayer frequency
- Language multi-select
- Dietary preferences (Halal, Kosher, Vegetarian, etc.)

---

### LocationPreferences.php

Location and relocation preferences.

**Purpose**: Set location preferences for matching.

**Public Properties**:
```php
public string $current_city = '';
public int $current_country_id;
public array $willing_to_relocate_countries = [];
public string $relocation_willingness = 'maybe';
public int $max_distance = 50;
```

**Key Methods**:
- `save()`: Update location preferences

**Usage**:
```blade
<livewire:profile.location-preferences />
```

---

### FamilyMarriage.php

Family and marriage preferences.

**Purpose**: Capture family-related preferences.

**Public Properties**:
```php
public string $marital_status = 'never_married';
public bool $has_children = false;
public int $number_of_children = 0;
public bool $want_children = true;
public string $family_involvement_level = 'moderate';
public string $living_situation = 'with_family';
```

**Key Methods**:
- `save()`: Update family preferences
- `updatedHasChildren()`: Show/hide children count

**Usage**:
```blade
<livewire:profile.family-marriage />
```

---

### CareerEducation.php

Career and education profile.

**Purpose**: Professional and educational background.

**Public Properties**:
```php
public string $education_level = '';
public string $field_of_study = '';
public string $occupation = '';
public string $income_level = '';
public string $work_status = 'employed';
```

**Key Methods**:
- `save()`: Update career profile

**Usage**:
```blade
<livewire:profile.career-education />
```

---

### EnhanceProfile.php

Profile enhancement suggestions and completion tracking.

**Purpose**: Guide users to complete their profile.

**Public Properties**:
```php
public int $completionPercentage = 0;
public array $missingSections = [];
public array $suggestions = [];
```

**Key Methods**:
- `mount()`: Calculate completion status
- `calculateCompletion()`: Determine percentage

**Usage**:
```blade
<livewire:profile.enhance-profile />
```

**Features**:
- Completion percentage calculation
- Missing sections highlight
- Quick action buttons
- Guided completion flow

---

## Dashboard Components

Location: `app/Livewire/Dashboard/`

### SwipeCards.php

Tinder-style card swipe interface for profile discovery.

**Purpose**: Main discovery interface with swipeable cards.

**Public Properties**:
```php
public $profiles = [];
public int $currentIndex = 0;
public bool $loading = false;
public bool $showMatchAnimation = false;
public ?User $matchedUser = null;
```

**Key Methods**:
- `mount()`: Load initial profiles
- `loadProfiles()`: Fetch matching profiles
- `like($userId)`: Like a profile
- `pass($userId)`: Pass on a profile
- `superLike($userId)`: Super like (premium)
- `undo()`: Undo last action (premium)
- `checkForMatch($userId)`: Check if match created

**Events Dispatched**:
- `profile-liked`: When user likes someone
- `profile-passed`: When user passes
- `match-created`: When mutual like happens

**Events Listened**:
- `profile-updated`: Refresh profiles

**Usage**:
```blade
<livewire:dashboard.swipe-cards />
```

**Features**:
- Swipeable card interface
- Swipe animations (via Alpine.js)
- Like, pass, super like actions
- Undo last swipe (premium feature)
- Match animation on mutual like
- Infinite loading of profiles
- Empty state when no profiles

**Alpine.js Integration**:
```html
<div x-data="swipeCards()" @swipe="handleSwipe($event)">
    <div class="card" x-show="currentIndex === index">
        <!-- Profile card content -->
    </div>
</div>

<script>
Alpine.data('swipeCards', () => ({
    handleSwipe(event) {
        if (event.detail.direction === 'right') {
            this.$wire.like(event.detail.userId);
        } else if (event.detail.direction === 'left') {
            this.$wire.pass(event.detail.userId);
        }
    }
}));
</script>
```

---

### DiscoveryGrid.php

Grid view for profile discovery (alternative to swipe cards).

**Purpose**: Browse profiles in grid layout.

**Public Properties**:
```php
use WithPagination;

public string $sortBy = 'last_active';
public string $viewMode = 'grid'; // 'grid' or 'list'
public bool $showFilters = false;
```

**Key Methods**:
- `loadMore()`: Infinite scroll pagination
- `likeProfile($userId)`: Quick like from grid
- `viewProfile($userId)`: Open profile modal
- `toggleFilters()`: Show/hide filter sidebar

**Events Dispatched**:
- `profile-liked`
- `profile-modal-opened`

**Usage**:
```blade
<livewire:dashboard.discovery-grid />
```

**Features**:
- Grid/list view toggle
- Infinite scroll
- Quick like/pass actions
- Filter sidebar
- Sort options (last active, newest, distance)

---

### ProfileModal.php

Full-screen profile detail modal.

**Purpose**: View complete profile with all sections.

**Public Properties**:
```php
public bool $show = false;
public ?User $user = null;
public int $currentPhotoIndex = 0;
```

**Key Methods**:
- `open($userId)`: Open modal with user profile
- `close()`: Close modal
- `nextPhoto()`: View next photo
- `previousPhoto()`: View previous photo
- `like()`: Like the profile
- `pass()`: Pass on the profile
- `report()`: Report the profile

**Events Listened**:
- `open-profile-modal`: Open modal for specific user

**Usage**:
```blade
<livewire:dashboard.profile-modal />

<!-- Trigger from another component -->
<button wire:click="$dispatch('open-profile-modal', { userId: {{ $user->id }} })">
    View Profile
</button>
```

**Features**:
- Full-screen overlay
- Photo gallery with swipe navigation
- All profile sections displayed
- Action buttons (like, pass, message)
- Report/block options
- Smooth animations

---

### StoriesBar.php

Horizontal scrolling stories bar (Instagram-style).

**Purpose**: Display user stories at top of dashboard.

**Public Properties**:
```php
public $stories = [];
public $activeStories = [];
```

**Key Methods**:
- `mount()`: Load stories from matches
- `viewStory($storyId)`: Open story viewer
- `markAsViewed($storyId)`: Mark story as seen

**Events Dispatched**:
- `story-viewed`
- `open-story-viewer`

**Usage**:
```blade
<livewire:dashboard.stories-bar />
```

**Features**:
- Horizontal scroll
- Unviewed indicator (colored ring)
- Story upload button
- Click to view
- Auto-expire after 24 hours

---

### StoryViewer.php

Full-screen story viewer with auto-advance.

**Purpose**: Display stories in full-screen mode.

**Public Properties**:
```php
public $stories = [];
public int $currentIndex = 0;
public bool $show = false;
```

**Key Methods**:
- `open($userId)`: Open stories for user
- `next()`: Advance to next story
- `previous()`: Go to previous story
- `close()`: Close viewer

**Events Listened**:
- `open-story-viewer`

**Usage**:
```blade
<livewire:dashboard.story-viewer />
```

**Features**:
- Full-screen viewer
- Auto-advance (5 seconds per story)
- Progress bars
- Swipe navigation
- Reply to story
- Close button

---

### ActivitySidebar.php

Real-time activity sidebar showing online users and notifications.

**Purpose**: Display real-time user activity.

**Public Properties**:
```php
public $onlineMatches = [];
public $recentActivity = [];
public $newLikes = [];
```

**Key Methods**:
- `mount()`: Load initial data
- `updateOnlineUsers($users)`: Update online status
- `newMatch($match)`: Handle new match event

**WebSocket Listeners**:
```php
protected function getListeners()
{
    return [
        'echo:presence-online,here' => 'updateOnlineUsers',
        'echo:presence-online,joining' => 'userJoined',
        'echo:presence-online,leaving' => 'userLeft',
        "echo-private:user.{$this->userId},MatchCreated" => 'newMatch',
    ];
}
```

**Usage**:
```blade
<livewire:dashboard.activity-sidebar />
```

**Features**:
- Online matches list
- Recent activity feed
- New likes notifications
- Unread message count
- Quick chat access
- Real-time updates via WebSocket

---

### ComprehensiveProfile.php

Complete profile view with all sections in tabs.

**Purpose**: Display or edit full profile in organized tabs.

**Public Properties**:
```php
public User $user;
public string $activeTab = 'about';
public bool $editMode = false;
```

**Key Methods**:
- `switchTab($tab)`: Change active tab
- `toggleEditMode()`: Enable/disable editing
- `save()`: Save all changes

**Usage**:
```blade
<livewire:dashboard.comprehensive-profile :user="$user" />
```

**Features**:
- Tabbed interface (About, Photos, Cultural, Career, Preferences)
- Edit mode toggle
- Progress saving
- Section validation

---

### ModernHeader.php

Dashboard header with navigation and notifications.

**Purpose**: Main navigation header for authenticated users.

**Public Properties**:
```php
public int $unreadCount = 0;
public bool $showNotifications = false;
public bool $showMobileMenu = false;
```

**Key Methods**:
- `mount()`: Load notification count
- `toggleNotifications()`: Show/hide dropdown
- `markAllAsRead()`: Clear notifications

**Events Listened**:
- `new-notification`: Update count

**Usage**:
```blade
<livewire:dashboard.modern-header />
```

**Features**:
- Navigation menu
- Notifications dropdown
- Profile dropdown
- Search bar
- Mobile menu toggle
- Unread badge

---

## Page Components

Location: `app/Livewire/Pages/`

Full-page components that are mapped to routes.

### DiscoverPage.php

Main discovery page combining swipe cards and grid view.

**Purpose**: Primary discovery interface.

**Usage**:
```php
// Route definition
Route::get('/discover', DiscoverPage::class)->name('discover');
```

---

### MatchesPage.php

View all matches with filtering and sorting.

**Purpose**: Browse existing matches.

**Public Properties**:
```php
use WithPagination;

public string $search = '';
public string $filter = 'all'; // 'all', 'recent', 'online'
public string $sortBy = 'last_active';
```

**Key Methods**:
- `render()`: Display matches with filters
- `updatingSearch()`: Reset pagination on search

**Usage**:
```php
Route::get('/matches', MatchesPage::class)->name('matches');
```

**Features**:
- Search matches by name
- Filter by status (recent, online)
- Sort options
- Grid/list view
- Quick chat access

---

### ChatPage.php

Real-time chat interface.

**Purpose**: Message matches in real-time.

**Public Properties**:
```php
public ?int $selectedChatId = null;
public $messages = [];
public string $newMessage = '';
public bool $typing = false;
```

**Key Methods**:
- `selectChat($chatId)`: Load chat conversation
- `sendMessage()`: Send new message
- `messageReceived($payload)`: Handle incoming message
- `userTyping($payload)`: Show typing indicator
- `updateTyping($isTyping)`: Broadcast typing status

**WebSocket Listeners**:
```php
protected function getListeners()
{
    return [
        "echo-private:chat.{$this->selectedChatId},NewMessageEvent" => 'messageReceived',
        "echo-private:chat.{$this->selectedChatId},UserTyping" => 'userTyping',
    ];
}
```

**Usage**:
```php
Route::get('/messages', ChatPage::class)->name('messages');
Route::get('/messages/{chatId}', ChatPage::class)->name('messages.show');
```

**Features**:
- Conversation list
- Real-time messages
- Typing indicators
- Message status (sent, delivered, read)
- Media upload
- Emoji picker
- Scroll to bottom on new message

---

### LikesPage.php

View received and sent likes.

**Purpose**: Browse who liked you and who you liked.

**Public Properties**:
```php
public string $tab = 'received'; // 'received' or 'sent'
public $receivedLikes = [];
public $sentLikes = [];
```

**Usage**:
```php
Route::get('/likes', LikesPage::class)->name('likes');
```

**Features**:
- Received likes tab (premium feature)
- Sent likes tab
- Like back to create match
- Unlike option

---

### MyProfilePage.php

User's own profile view and editing.

**Purpose**: View and edit own profile.

**Usage**:
```php
Route::get('/profile', MyProfilePage::class)->name('profile');
```

---

### UserProfilePage.php

View another user's public profile.

**Purpose**: Display other user's profile.

**Public Properties**:
```php
public User $user;
public bool $canMessage = false;
public bool $isBlocked = false;
```

**Key Methods**:
- `mount($userId)`: Load user profile
- `like()`: Like the user
- `pass()`: Pass on the user
- `sendMessage()`: Start conversation (if matched)
- `block()`: Block the user
- `report()`: Report the user

**Usage**:
```php
Route::get('/user/{userId}', UserProfilePage::class)->name('user.profile');
```

---

### SettingsPage.php

Settings interface with tabs.

**Purpose**: User settings management.

**Public Properties**:
```php
public string $activeTab = 'profile';
```

**Usage**:
```php
Route::get('/settings', SettingsPage::class)->name('settings');
```

---

### BlockedUsersPage.php

Manage blocked users list.

**Purpose**: View and unblock users.

**Public Properties**:
```php
public $blockedUsers = [];
```

**Key Methods**:
- `unblock($userId)`: Remove block

**Usage**:
```php
Route::get('/blocked', BlockedUsersPage::class)->name('blocked');
```

---

### SubscriptionPage.php

Subscription plans and payment.

**Purpose**: Manage subscription and payments.

**Public Properties**:
```php
public $plans = [];
public ?string $currentPlan = null;
public $usageStats = [];
```

**Key Methods**:
- `subscribe($planId)`: Subscribe to plan
- `cancelSubscription()`: Cancel current plan
- `viewUsage()`: Display usage statistics

**Usage**:
```php
Route::get('/subscription', SubscriptionPage::class)->name('subscription');
```

**Features**:
- Plan comparison table
- Current plan status
- Usage statistics
- Payment history
- Upgrade/downgrade options

---

### VerificationPage.php

Identity verification submission and status.

**Purpose**: Submit verification documents.

**Public Properties**:
```php
use WithFileUploads;

public string $verificationType = 'identity';
public $document;
public string $status = 'pending';
```

**Key Methods**:
- `submitVerification()`: Upload documents
- `checkStatus()`: Check verification status

**Usage**:
```php
Route::get('/verification', VerificationPage::class)->name('verification');
```

---

### VideoCallPage.php

Video calling interface.

**Purpose**: Video/voice calls with matches.

**Public Properties**:
```php
public string $meetingId = '';
public string $token = '';
public bool $videoEnabled = true;
public bool $audioEnabled = true;
```

**Key Methods**:
- `mount($callId)`: Initialize call
- `toggleVideo()`: Enable/disable camera
- `toggleAudio()`: Mute/unmute
- `endCall()`: Terminate call

**Usage**:
```php
Route::get('/call/{callId}', VideoCallPage::class)->name('call');
```

---

### InsightsPage.php

User analytics and insights.

**Purpose**: Display profile performance metrics.

**Public Properties**:
```php
public array $profileViews = [];
public array $matchStats = [];
public array $activityChart = [];
```

**Usage**:
```php
Route::get('/insights', InsightsPage::class)->name('insights');
```

**Features**:
- Profile view count
- Match statistics
- Activity charts
- Comparison to averages
- Weekly/monthly trends

---

### NotificationsPage.php

Notification center.

**Purpose**: View all notifications.

**Public Properties**:
```php
use WithPagination;

public string $filter = 'all';
```

**Key Methods**:
- `markAsRead($notificationId)`: Mark single as read
- `markAllAsRead()`: Clear all notifications
- `deleteNotification($notificationId)`: Remove notification

**Usage**:
```php
Route::get('/notifications', NotificationsPage::class)->name('notifications');
```

---

### SearchPage.php

Advanced search with filters.

**Purpose**: Search for specific profiles.

**Public Properties**:
```php
public string $query = '';
public array $filters = [];
public string $sortBy = 'relevance';
```

**Key Methods**:
- `search()`: Execute search
- `saveSearch()`: Save search criteria

**Usage**:
```php
Route::get('/search', SearchPage::class)->name('search');
```

---

## Admin Components

Location: `app/Livewire/Admin/`

### Dashboard.php

Admin dashboard overview with metrics.

**Purpose**: Admin analytics and quick stats.

**Public Properties**:
```php
public array $userStats = [];
public array $revenueMetrics = [];
public array $activityGraphs = [];
```

**Usage**:
```php
Route::get('/admin', Dashboard::class)
    ->middleware('admin')
    ->name('admin.dashboard');
```

---

### Users.php

User management table with search and filters.

**Purpose**: Browse and manage all users.

**Public Properties**:
```php
use WithPagination;

public string $search = '';
public string $statusFilter = 'all';
public string $sortField = 'created_at';
public string $sortDirection = 'desc';
```

**Key Methods**:
- `sortBy($field)`: Change sort column
- `deleteUser($userId)`: Delete user account
- `suspendUser($userId)`: Suspend account

**Usage**:
```php
Route::get('/admin/users', Users::class)
    ->middleware('admin')
    ->name('admin.users');
```

---

### UserProfile.php

Admin view of user profile with moderation actions.

**Purpose**: View and moderate user profiles.

**Public Properties**:
```php
public User $user;
public $activityLog = [];
```

**Key Methods**:
- `verifyUser()`: Manually verify user
- `banUser()`: Ban user account
- `resetPassword()`: Force password reset
- `viewActivityLog()`: Display user activity

**Usage**:
```php
Route::get('/admin/users/{userId}', UserProfile::class)
    ->middleware('admin')
    ->name('admin.users.profile');
```

---

### Reports.php

User report queue and management.

**Purpose**: Review and act on user reports.

**Public Properties**:
```php
use WithPagination;

public string $priority = 'all'; // 'high', 'medium', 'low'
public string $status = 'pending';
```

**Key Methods**:
- `reviewReport($reportId)`: Open report details
- `approveReport($reportId)`: Take action on report
- `dismissReport($reportId)`: Dismiss false report

**Usage**:
```php
Route::get('/admin/reports', Reports::class)
    ->middleware('admin')
    ->name('admin.reports');
```

---

### Verification.php

Verification request queue.

**Purpose**: Review verification submissions.

**Public Properties**:
```php
public $pendingVerifications = [];
```

**Key Methods**:
- `approveVerification($requestId)`: Approve request
- `rejectVerification($requestId, $reason)`: Reject with reason
- `requestMoreInfo($requestId)`: Ask for additional documents

**Usage**:
```php
Route::get('/admin/verification', Verification::class)
    ->middleware('admin')
    ->name('admin.verification');
```

---

## Settings Components

Location: `app/Livewire/Settings/`

### Profile.php

Profile settings editor (same as Profile/BasicInfo but in settings context).

---

### Password.php

Password change form.

**Purpose**: Allow users to update password.

**Public Properties**:
```php
public string $current_password = '';
public string $password = '';
public string $password_confirmation = '';
public bool $showCurrentPassword = false;
public bool $showNewPassword = false;
```

**Key Methods**:
- `updatePassword()`: Validate and change password
- `togglePasswordVisibility($field)`: Show/hide password

**Usage**:
```blade
<livewire:settings.password />
```

**Features**:
- Current password verification
- Password strength indicator
- Confirmation field
- Show/hide password toggle
- Real-time validation

---

### Appearance.php

Theme and language settings.

**Purpose**: Customize app appearance.

**Public Properties**:
```php
public string $theme = 'light'; // 'light', 'dark', 'system'
public string $language = 'en';
public string $fontSize = 'medium';
```

**Key Methods**:
- `updateTheme($theme)`: Change theme
- `updateLanguage($language)`: Change language
- `updateFontSize($size)`: Adjust font size

**Events Dispatched**:
- `theme-changed`: When theme is updated
- `language-changed`: When language is changed

**Usage**:
```blade
<livewire:settings.appearance />
```

**Features**:
- Light/dark/system theme toggle
- Language selection (en, uz, ru)
- Font size adjustment
- Preview changes in real-time

**Theme Toggle Implementation**:
```php
public function updateTheme($theme)
{
    $this->theme = $theme;

    // Save to database for authenticated users
    if (auth()->check()) {
        auth()->user()->settings()->update(['theme' => $theme]);
    }

    // Save to cookie for guests
    cookie()->queue('theme', $theme, 525600); // 1 year

    // Dispatch event for JavaScript to update DOM
    $this->dispatch('theme-changed', theme: $theme);
}
```

---

### DeleteUserForm.php

Account deletion form with confirmation.

**Purpose**: Allow users to permanently delete account.

**Public Properties**:
```php
public string $password = '';
public string $reason = '';
public bool $confirmed = false;
```

**Key Methods**:
- `deleteAccount()`: Permanently delete account

**Validation Rules**:
```php
protected function rules(): array
{
    return [
        'password' => 'required|current_password',
        'reason' => 'required|string|min:10',
        'confirmed' => 'accepted',
    ];
}
```

**Usage**:
```blade
<livewire:settings.delete-user-form />
```

**Features**:
- Password confirmation
- Deletion reason (required)
- Confirmation checkbox
- Warning messages
- 30-day grace period explanation

---

## Reusable Components

Location: `app/Livewire/Components/`

### UnifiedSidebar.php

Main navigation sidebar for authenticated users.

**Purpose**: Provide consistent navigation across app.

**Public Properties**:
```php
public bool $collapsed = false;
public string $activeRoute = '';
public int $unreadMessages = 0;
```

**Key Methods**:
- `mount()`: Detect active route
- `toggleCollapse()`: Expand/collapse sidebar

**Events Listened**:
- `new-message`: Update unread count

**Usage**:
```blade
<livewire:components.unified-sidebar />
```

**Features**:
- Navigation links with icons
- Active state highlighting
- Unread message badge
- Collapse/expand animation
- Mobile responsive (drawer)

---

### LanguageSwitcher.php

Language selection dropdown.

**Purpose**: Allow users to switch app language.

**Public Properties**:
```php
public string $currentLanguage = 'en';
public array $availableLanguages = ['en', 'uz', 'ru'];
```

**Key Methods**:
- `switchLanguage($locale)`: Change active language

**Events Dispatched**:
- `language-changed`

**Usage**:
```blade
<livewire:components.language-switcher />
```

**Features**:
- Dropdown with flag icons
- Current language display
- Persist preference
- Reload page on change

---

### PanicButton.php

Emergency panic button component.

**Purpose**: Quick access to emergency features.

**Public Properties**:
```php
public bool $showConfirmation = false;
public bool $activated = false;
```

**Key Methods**:
- `activate()`: Trigger panic button
- `sendAlerts()`: Notify emergency contacts

**Usage**:
```blade
<livewire:components.panic-button />
```

**Features**:
- Always visible (fixed position)
- Confirmation modal
- GPS location capture
- Alert emergency contacts
- Notify admin
- Status indicator

---

### ThemeSwitcher.php

Theme toggle component (light/dark/system).

**Purpose**: Quick theme switching.

**Public Properties**:
```php
public string $theme = 'light';
```

**Key Methods**:
- `setTheme($theme)`: Update theme preference

**Events Dispatched**:
- `theme-changed`

**Usage**:
```blade
<livewire:theme-switcher />
```

**Features**:
- Three theme modes (light, dark, system)
- Icon indicators (sun, moon, monitor)
- Smooth transitions
- Persistent storage

---

## Component Lifecycle

### Livewire Component Lifecycle Hooks

1. **mount()**: Called once when component is initialized
   ```php
   public function mount($userId)
   {
       $this->user = User::findOrFail($userId);
       $this->loadData();
   }
   ```

2. **hydrate()**: Called before every update
   ```php
   public function hydrate()
   {
       $this->user = User::find($this->userId);
   }
   ```

3. **updating($propertyName, $value)**: Before property update
   ```php
   public function updatingSearch($value)
   {
       $this->resetPage(); // Reset pagination
   }
   ```

4. **updated($propertyName, $value)**: After property update
   ```php
   public function updatedMinAge($value)
   {
       $this->validateAge();
       $this->updateMatchCount();
   }
   ```

5. **render()**: Called on every update
   ```php
   public function render()
   {
       return view('livewire.component-name', [
           'items' => $this->getItems(),
       ]);
   }
   ```

6. **dehydrate()**: Called after every update
   ```php
   public function dehydrate()
   {
       // Clean up before sending to frontend
   }
   ```

### Lifecycle Example

```php
class UserProfile extends Component
{
    public User $user;
    public string $search = '';

    // 1. Called once on initialization
    public function mount($userId)
    {
        $this->user = User::findOrFail($userId);
    }

    // 2. Called before each update
    public function hydrate()
    {
        // Refresh user from database
        $this->user->refresh();
    }

    // 3. Before property updates
    public function updatingSearch($value)
    {
        // Reset pagination when searching
        $this->resetPage();
    }

    // 4. After property updates
    public function updatedSearch($value)
    {
        // Dispatch search event
        $this->dispatch('search-updated', search: $value);
    }

    // 5. Render on every update
    public function render()
    {
        return view('livewire.user-profile', [
            'posts' => $this->user->posts()
                ->where('title', 'like', "%{$this->search}%")
                ->paginate(10),
        ]);
    }
}
```

---

## Event System

### Dispatching Events

**From Livewire Component**:
```php
// Simple event
$this->dispatch('profile-updated');

// Event with data
$this->dispatch('match-created', matchId: $match->id);

// Event to specific component
$this->dispatch('refresh-sidebar')->to(Sidebar::class);

// Event to parent component
$this->dispatch('child-updated')->up();

// Event to all components
$this->dispatch('global-refresh')->to('*');
```

**From Blade Template**:
```blade
<button wire:click="$dispatch('open-modal', { userId: {{ $user->id }} })">
    Open Modal
</button>
```

### Listening to Events

**In Livewire Component**:
```php
use Livewire\Attributes\On;

class MyComponent extends Component
{
    // Listen to specific event
    #[On('profile-updated')]
    public function refreshProfile()
    {
        $this->loadProfile();
    }

    // Dynamic listeners
    protected function getListeners()
    {
        return [
            'profile-updated' => 'refreshProfile',
            "user-{$this->userId}-updated" => 'handleUserUpdate',
        ];
    }
}
```

**In JavaScript**:
```javascript
// Listen to Livewire event
document.addEventListener('livewire:dispatch', (event) => {
    if (event.detail.name === 'profile-updated') {
        console.log('Profile updated!', event.detail.data);
    }
});

// Or using Alpine.js
Alpine.data('myComponent', () => ({
    init() {
        Livewire.on('profile-updated', (data) => {
            console.log('Profile updated!', data);
        });
    }
}));
```

### WebSocket Events

**Broadcasting from Server**:
```php
// In service or controller
event(new NewMessageEvent($message));
```

**Listening in Livewire**:
```php
protected function getListeners()
{
    return [
        // Private channel
        "echo-private:chat.{$this->chatId},NewMessageEvent" => 'messageReceived',

        // Presence channel
        'echo:presence-online,here' => 'updateOnlineUsers',
        'echo:presence-online,joining' => 'userJoined',
        'echo:presence-online,leaving' => 'userLeft',
    ];
}

public function messageReceived($payload)
{
    $this->messages[] = $payload['message'];
    $this->dispatch('scroll-to-bottom');
}
```

---

## Best Practices

### 1. Component Naming

- Use descriptive names: `UserProfileCard` not `Card`
- Follow Laravel conventions: PascalCase for classes
- Organize by feature: `Profile/BasicInfo.php` not `BasicInfoProfile.php`

### 2. Properties

```php
// ✅ Good: Type-hinted public properties
public User $user;
public string $search = '';
public int $currentPage = 1;

// ❌ Bad: No type hints
public $user;
public $search;
```

### 3. Validation

```php
// ✅ Good: Inline validation
public function save()
{
    $this->validate([
        'email' => 'required|email',
        'name' => 'required|min:3',
    ]);

    // Save logic
}

// ✅ Better: Rules method
protected function rules(): array
{
    return [
        'email' => 'required|email',
        'name' => 'required|min:3',
    ];
}
```

### 4. Events

```php
// ✅ Good: Descriptive event names
$this->dispatch('profile-updated');
$this->dispatch('match-created');

// ❌ Bad: Generic names
$this->dispatch('update');
$this->dispatch('done');
```

### 5. Database Queries

```php
// ✅ Good: Eager loading
public function render()
{
    return view('livewire.user-list', [
        'users' => User::with('profile', 'photos')->paginate(20),
    ]);
}

// ❌ Bad: N+1 queries
public function render()
{
    return view('livewire.user-list', [
        'users' => User::paginate(20), // Will cause N+1 on profile access
    ]);
}
```

### 6. Loading States

```blade
<!-- ✅ Good: Show loading states -->
<button wire:click="save" wire:loading.attr="disabled">
    <span wire:loading.remove>Save</span>
    <span wire:loading>Saving...</span>
</button>

<!-- Show spinner on slow operations -->
<div wire:loading wire:target="loadProfiles">
    <div class="spinner"></div>
</div>
```

### 7. Debouncing

```blade
<!-- ✅ Good: Debounce search input -->
<input wire:model.live.debounce.500ms="search" />

<!-- ✅ Good: Lazy loading for large forms -->
<input wire:model.lazy="email" />
```

### 8. Component Size

- Keep components focused on single responsibility
- Extract reusable logic to services
- Maximum ~300 lines per component
- Split large components into smaller ones

### 9. Security

```php
// ✅ Good: Authorize actions
public function deleteUser($userId)
{
    $this->authorize('delete', User::findOrFail($userId));

    // Delete logic
}

// ✅ Good: Validate input
public function save()
{
    $this->validate();

    // Save logic
}
```

### 10. Testing

```php
// Example Livewire test
test('user can update profile', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BasicInfo::class)
        ->set('first_name', 'John')
        ->set('last_name', 'Doe')
        ->call('save')
        ->assertDispatched('profile-updated')
        ->assertHasNoErrors();

    expect($user->fresh()->first_name)->toBe('John');
});
```

---

## Troubleshooting

### Component Not Updating

**Problem**: Changes not reflecting in UI.

**Solutions**:
```php
// 1. Check if property is public
public string $search = ''; // ✅ Will update
private string $search = ''; // ❌ Won't update

// 2. Use wire:model.live for real-time updates
<input wire:model.live="search" /> // ✅ Updates immediately
<input wire:model="search" /> // ❌ Updates on blur/change

// 3. Force refresh if needed
$this->js('$wire.$refresh()');
```

### N+1 Query Problems

**Problem**: Too many database queries.

**Solutions**:
```php
// ✅ Eager load relationships
public function render()
{
    return view('livewire.component', [
        'users' => User::with('profile', 'photos', 'preferences')->get(),
    ]);
}

// ✅ Use query builder efficiently
$users = User::query()
    ->select('id', 'name', 'email') // Only needed columns
    ->with('profile:id,user_id,bio') // Specific columns
    ->paginate(20);
```

### Events Not Firing

**Problem**: Dispatched events not being received.

**Solutions**:
```php
// 1. Check listener registration
#[On('event-name')] // ✅ Correct attribute
public function handleEvent() {}

// 2. Check event name matches exactly
$this->dispatch('profile-updated'); // Must match listener

// 3. Check component is on page
// Components must be rendered to receive events
```

### File Upload Issues

**Problem**: File uploads not working.

**Solutions**:
```php
// 1. Use WithFileUploads trait
use WithFileUploads;

// 2. Set correct validation
$this->validate([
    'photo' => 'required|image|max:5120', // 5MB max
]);

// 3. Handle upload correctly
$path = $this->photo->store('photos', 'r2');

// 4. Check php.ini settings
// upload_max_filesize = 10M
// post_max_size = 10M
```

### WebSocket Not Connecting

**Problem**: Real-time features not working.

**Solutions**:
```bash
# 1. Start Reverb server
php artisan reverb:start

# 2. Check environment variables
REVERB_APP_KEY=your-key
REVERB_HOST=localhost
REVERB_PORT=8080

# 3. Verify Echo initialization
console.log(window.Echo); // Should not be undefined

# 4. Check channel authorization
// routes/channels.php
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats()->where('id', $chatId)->exists();
});
```

### Performance Issues

**Problem**: Slow component rendering.

**Solutions**:
```php
// 1. Use pagination
use WithPagination;
$items->paginate(20);

// 2. Lazy load components
<livewire:heavy-component lazy />

// 3. Cache expensive queries
Cache::remember("user-{$id}-profile", 3600, function () {
    return User::with('profile')->find($id);
});

// 4. Defer loading
<livewire:component wire:init="loadData" />

public function loadData()
{
    $this->data = $this->fetchExpensiveData();
}
```

---

**Last Updated**: September 2025

This catalog provides a comprehensive reference for all Livewire components in the YorYor dating application. Each component is designed to be reusable, maintainable, and follows Livewire best practices.

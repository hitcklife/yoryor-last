# YorYor Database Schema & Migrations

## Table of Contents
- [Overview](#overview)
- [Database Statistics](#database-statistics)
- [Schema Organization](#schema-organization)
- [Core Tables](#core-tables)
- [Profile Extension Tables](#profile-extension-tables)
- [Communication Tables](#communication-tables)
- [Matching System](#matching-system)
- [Subscription & Payment](#subscription--payment)
- [Safety & Moderation](#safety--moderation)
- [Key Relationships](#key-relationships)
- [Migration Management](#migration-management)
- [Indexing Strategy](#indexing-strategy)
- [Query Optimization](#query-optimization)
- [Best Practices](#best-practices)

---

## Overview

YorYor's database consists of **70+ tables** organized into logical domains. The schema is designed for high performance with proper indexing, foreign key constraints, and optimized for common query patterns.

**Database Architecture:**
- **Core Pattern**: User → Profile → Extended Profiles
- **Communication**: Chat → Messages → Reads
- **Matching**: Likes → Matches → Chats
- **Soft Deletes**: Enabled on key tables for data recovery

---

## Database Statistics

- **Total Tables:** 70+
- **Core User Tables:** 10
- **Profile Extension Tables:** 6
- **Communication Tables:** 5
- **Matching Tables:** 3
- **Subscription Tables:** 7
- **Safety & Moderation Tables:** 8
- **Matchmaker Tables:** 7
- **Verification Tables:** 2
- **System Tables:** 15+
- **Framework Tables:** 7

---

## Schema Organization

### By Domain

```
User Domain:
├── users                        # Authentication & basic info
├── profiles                     # Core profile data
├── user_settings                # User preferences
├── user_preferences             # Matching preferences
├── user_photos                  # Profile photos
├── user_activities              # Activity tracking
├── user_cultural_profiles       # Religious/cultural
├── user_career_profiles         # Education/career
├── user_physical_profiles       # Physical attributes
├── user_family_preferences      # Family expectations
└── user_location_preferences    # Location preferences

Communication Domain:
├── chats                        # Conversations
├── chat_users                   # Participants (pivot)
├── messages                     # Chat messages
├── message_reads                # Read receipts
└── calls                        # Video/voice calls

Matching Domain:
├── likes                        # User likes
├── dislikes                     # User passes
└── matches                      # Mutual matches

Content Domain:
├── user_stories                 # 24-hour stories
└── media                        # Generic media storage

Subscription Domain:
├── subscription_plans           # Available plans
├── plan_features                # Plan features
├── plan_pricing                 # Pricing tiers
├── user_subscriptions           # Active subscriptions
├── payment_transactions         # Payment history
├── user_usage_limits            # Usage tracking
└── user_monthly_usage           # Historical usage

Safety Domain:
├── user_blocks                  # Blocked users
├── user_reports                 # Basic reports
├── enhanced_user_reports        # Detailed reports
├── report_evidence              # Report attachments
├── user_safety_scores           # Safety ratings
├── panic_activations            # Panic button logs
├── emergency_contacts           # Emergency contacts
└── user_feedback                # User feedback

Matchmaker Domain:
├── matchmakers                  # Matchmaker profiles
├── matchmaker_services          # Services offered
├── matchmaker_clients           # Client relationships
├── matchmaker_consultations     # Scheduled consultations
├── matchmaker_introductions     # Introductions made
├── matchmaker_reviews           # Reviews
└── matchmaker_availability      # Schedule

Verification Domain:
├── verification_requests        # Verification submissions
└── user_verified_badges         # Earned badges

System Domain:
├── countries                    # Country data
├── otp_codes                    # OTP verification
├── device_tokens                # Push notification tokens
├── notifications                # In-app notifications
├── data_export_requests         # GDPR exports
├── family_members               # Family accounts
├── family_approvals             # Family approval system
└── user_prayer_times            # Prayer time preferences

RBAC Domain:
├── roles                        # User roles
├── permissions                  # System permissions
├── role_user                    # User roles (pivot)
└── permission_role              # Role permissions (pivot)
```

---

## Core Tables

### users

Primary authentication and user identity table.

**Location:** `database/migrations/2025_09_24_211011_create_users_table.php`

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE,
    password VARCHAR(255),

    -- OAuth
    google_id VARCHAR(255),
    facebook_id VARCHAR(255),
    provider VARCHAR(50),

    -- Status
    registration_completed BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_private BOOLEAN DEFAULT FALSE,

    -- 2FA
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,

    -- Timestamps
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    last_active_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    disabled_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_email (email),
    INDEX idx_phone (phone),
    INDEX idx_uuid (uuid),
    INDEX idx_active (last_active_at),
    INDEX idx_registration (registration_completed)
);
```

**Key Fields:**
- `uuid` - Unique identifier for public API references
- `registration_completed` - Tracks onboarding status
- `last_active_at` - For online/offline presence
- `two_factor_*` - 2FA with Google Authenticator

**Relationships:**
```php
// app/Models/User.php
public function profile(): HasOne
public function setting(): HasOne
public function preference(): HasOne
public function photos(): HasMany
public function chats(): BelongsToMany
public function matches(): BelongsToMany
```

### profiles

Core profile information.

**Location:** `database/migrations/2025_09_24_211017_create_profiles_table.php`

```sql
CREATE TABLE profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,

    -- Personal Info
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    gender ENUM('male', 'female', 'other') NOT NULL,
    date_of_birth DATE NOT NULL,
    age INT GENERATED ALWAYS AS (YEAR(CURDATE()) - YEAR(date_of_birth)),

    -- Professional
    occupation VARCHAR(150),
    profession VARCHAR(150),
    status VARCHAR(50),

    -- Location
    city VARCHAR(100),
    state VARCHAR(100),
    country_id BIGINT,
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),

    -- About
    bio TEXT,
    interests JSON,
    looking_for_relationship VARCHAR(100),

    -- Stats
    profile_views INT DEFAULT 0,
    profile_completed_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE SET NULL,

    INDEX idx_user (user_id),
    INDEX idx_gender_age (gender, age),
    INDEX idx_location (latitude, longitude),
    INDEX idx_country (country_id)
);
```

**Key Features:**
- Computed `age` field from `date_of_birth`
- Location stored as latitude/longitude for distance calculations
- JSON field for flexible interests storage

---

## Profile Extension Tables

These tables extend the profile with domain-specific information.

### user_cultural_profiles

Religious and cultural background.

```sql
CREATE TABLE user_cultural_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,

    religion VARCHAR(100),
    sect VARCHAR(100),
    religiosity_level ENUM('very_religious', 'moderately_religious', 'not_religious'),
    prayer_frequency ENUM('five_times', 'sometimes', 'rarely', 'never'),
    hijab_preference VARCHAR(50),
    halal_preference BOOLEAN,

    languages JSON,
    ethnicity VARCHAR(100),
    cultural_values TEXT,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### user_career_profiles

Education and career information.

```sql
CREATE TABLE user_career_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,

    education_level VARCHAR(100),
    field_of_study VARCHAR(150),
    university VARCHAR(200),
    graduation_year INT,

    employment_status VARCHAR(50),
    job_title VARCHAR(150),
    company VARCHAR(200),
    industry VARCHAR(100),
    income_range VARCHAR(50),

    career_goals TEXT,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### user_physical_profiles

Physical attributes.

```sql
CREATE TABLE user_physical_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,

    height INT,                          # in cm
    body_type VARCHAR(50),
    eye_color VARCHAR(50),
    hair_color VARCHAR(50),

    exercise_frequency VARCHAR(50),
    dietary_preferences VARCHAR(100),
    smoking VARCHAR(50),
    drinking VARCHAR(50),

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## Communication Tables

### chats

Conversations between users.

```sql
CREATE TABLE chats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    type ENUM('private', 'group') DEFAULT 'private',
    name VARCHAR(255),
    description TEXT,

    is_active BOOLEAN DEFAULT TRUE,
    last_activity_at TIMESTAMP,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_type (type),
    INDEX idx_last_activity (last_activity_at)
);
```

### chat_users (Pivot)

Chat participants.

```sql
CREATE TABLE chat_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,

    role ENUM('member', 'admin') DEFAULT 'member',
    is_muted BOOLEAN DEFAULT FALSE,

    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    last_read_at TIMESTAMP NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_chat_user (chat_id, user_id),
    INDEX idx_chat (chat_id),
    INDEX idx_user (user_id),
    INDEX idx_last_read (last_read_at)
);
```

**Key Features:**
- `last_read_at` - For unread message counts
- Unique constraint prevents duplicate participants

### messages

Chat messages with media support.

```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT NOT NULL,
    sender_id BIGINT NOT NULL,

    content TEXT,
    type ENUM('text', 'image', 'video', 'audio', 'file', 'call', 'system') DEFAULT 'text',

    media_url VARCHAR(500),
    media_type VARCHAR(100),
    media_size INT,

    call_id BIGINT NULL,

    is_edited BOOLEAN DEFAULT FALSE,
    edited_at TIMESTAMP NULL,

    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,

    INDEX idx_chat_sent (chat_id, sent_at),
    INDEX idx_sender (sender_id),
    INDEX idx_call (call_id)
);
```

**Optimizations:**
- Composite index `(chat_id, sent_at)` for pagination
- Soft deletes for message recovery

### message_reads

Read receipts for messages.

```sql
CREATE TABLE message_reads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,

    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_message_user (message_id, user_id),
    INDEX idx_message (message_id),
    INDEX idx_user (user_id)
);
```

**Query Pattern:**
```php
// Get unread message count
$unreadCount = Message::where('chat_id', $chatId)
    ->whereDoesntHave('reads', function ($query) use ($userId) {
        $query->where('user_id', $userId);
    })
    ->count();
```

---

## Matching System

### likes

One-way user likes.

```sql
CREATE TABLE likes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    liked_user_id BIGINT NOT NULL,

    source VARCHAR(50),                # 'discovery', 'search', 'matchmaker'

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_like (user_id, liked_user_id),
    INDEX idx_user (user_id),
    INDEX idx_liked (liked_user_id),
    INDEX idx_created (created_at)
);
```

### matches

Mutual matches (both users liked each other).

```sql
CREATE TABLE matches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    matched_user_id BIGINT NOT NULL,

    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_match (user_id, matched_user_id),
    INDEX idx_user (user_id),
    INDEX idx_matched (matched_user_id),
    INDEX idx_matched_at (matched_at)
);
```

**Match Creation Logic:**
```php
// When User B likes User A, check if User A already liked User B
if (Like::where('user_id', $userA->id)->where('liked_user_id', $userB->id)->exists()) {
    // Create mutual match
    Match::create([
        'user_id' => $userA->id,
        'matched_user_id' => $userB->id,
    ]);
    Match::create([
        'user_id' => $userB->id,
        'matched_user_id' => $userA->id,
    ]);

    // Create private chat
    $chat = Chat::create(['type' => 'private']);
    $chat->users()->attach([$userA->id, $userB->id]);
}
```

---

## Subscription & Payment

### subscription_plans

Available subscription tiers.

```sql
CREATE TABLE subscription_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(100) NOT NULL,              # 'Free', 'Premium', 'Premium Plus'
    slug VARCHAR(100) UNIQUE NOT NULL,       # 'free', 'premium', 'premium-plus'
    description TEXT,

    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
);
```

### plan_features

Features included in each plan.

```sql
CREATE TABLE plan_features (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_plan_id BIGINT NOT NULL,

    feature_key VARCHAR(100),                # 'unlimited_likes', 'see_who_liked_you'
    feature_name VARCHAR(255),               # 'Unlimited Likes'
    feature_value TEXT,                      # 'true', '100', 'unlimited'

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,
    INDEX idx_plan (subscription_plan_id),
    INDEX idx_key (feature_key)
);
```

### user_subscriptions

Active user subscriptions.

```sql
CREATE TABLE user_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    subscription_plan_id BIGINT NOT NULL,

    status ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'trial',

    starts_at TIMESTAMP,
    ends_at TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    trial_ends_at TIMESTAMP NULL,

    auto_renew BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id),

    INDEX idx_user (user_id),
    INDEX idx_plan (subscription_plan_id),
    INDEX idx_status (status),
    INDEX idx_ends (ends_at)
);
```

### user_usage_limits

Track current usage for limits.

```sql
CREATE TABLE user_usage_limits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE NOT NULL,

    likes_remaining INT DEFAULT 50,
    super_likes_remaining INT DEFAULT 5,
    messages_remaining INT DEFAULT -1,       # -1 = unlimited
    profile_views_remaining INT DEFAULT -1,

    reset_at TIMESTAMP,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

---

## Safety & Moderation

### user_blocks

Blocked users.

```sql
CREATE TABLE user_blocks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    blocked_user_id BIGINT NOT NULL,

    reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_block (user_id, blocked_user_id),
    INDEX idx_user (user_id),
    INDEX idx_blocked (blocked_user_id)
);
```

**Query Pattern:**
```php
// Exclude blocked users from discovery
User::whereDoesntHave('blockedBy', function ($query) use ($userId) {
    $query->where('user_id', $userId);
})->whereDoesntHave('blocking', function ($query) use ($userId) {
    $query->where('blocked_user_id', $userId);
});
```

### enhanced_user_reports

Detailed reporting system.

```sql
CREATE TABLE enhanced_user_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    reporter_id BIGINT NOT NULL,
    reported_user_id BIGINT NOT NULL,

    category VARCHAR(100),               # 'harassment', 'fake_profile', etc.
    sub_category VARCHAR(100),
    description TEXT,

    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'under_review', 'action_taken', 'dismissed') DEFAULT 'pending',

    admin_notes TEXT,
    action_taken TEXT,
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_reporter (reporter_id),
    INDEX idx_reported (reported_user_id),
    INDEX idx_status (status),
    INDEX idx_severity (severity)
);
```

### panic_activations

Emergency panic button logs.

```sql
CREATE TABLE panic_activations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,

    trigger_reason VARCHAR(255),
    location_lat DECIMAL(10, 7),
    location_lng DECIMAL(10, 7),

    status ENUM('active', 'cancelled', 'resolved') DEFAULT 'active',

    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,

    admin_notes TEXT,

    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_activated (activated_at)
);
```

---

## Key Relationships

### User Relationships

```php
// app/Models/User.php

// One-to-One
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

// One-to-Many
public function photos(): HasMany
{
    return $this->hasMany(UserPhoto::class);
}

public function sentMessages(): HasMany
{
    return $this->hasMany(Message::class, 'sender_id');
}

// Many-to-Many
public function chats(): BelongsToMany
{
    return $this->belongsToMany(Chat::class, 'chat_users')
        ->withPivot('role', 'last_read_at', 'is_muted')
        ->withTimestamps();
}

public function matches(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'matches', 'user_id', 'matched_user_id')
        ->withPivot('matched_at')
        ->withTimestamps();
}

// Custom relationships
public function blockedUsers(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_blocks', 'user_id', 'blocked_user_id')
        ->withTimestamps();
}

public function blockedBy(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'user_blocks', 'blocked_user_id', 'user_id')
        ->withTimestamps();
}
```

### Chat Relationships

```php
// app/Models/Chat.php

public function users(): BelongsToMany
{
    return $this->belongsToMany(User::class, 'chat_users')
        ->withPivot('role', 'last_read_at', 'joined_at', 'left_at')
        ->withTimestamps();
}

public function messages(): HasMany
{
    return $this->hasMany(Message::class)->orderBy('sent_at');
}

public function latestMessage(): HasOne
{
    return $this->hasOne(Message::class)->latestOfMany('sent_at');
}
```

### Match Relationships

```php
// app/Models/MatchModel.php

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function matchedUser(): BelongsTo
{
    return $this->belongsTo(User::class, 'matched_user_id');
}

// Helper method
public function otherUser(User $user): User
{
    return $this->user_id === $user->id
        ? $this->matchedUser
        : $this->user;
}
```

---

## Migration Management

### Migration Order

**Location:** `database/migrations/`

Migrations run chronologically by timestamp:

```
2025_09_24_211011_create_users_table.php
2025_09_24_211016_create_countries_table.php
2025_09_24_211017_create_profiles_table.php
2025_09_24_211017_create_otp_codes_table.php
...
2025_09_24_999999_add_foreign_key_constraints.php  ← ALWAYS LAST
```

**Critical Rule:** Foreign key constraints are added in a separate migration that runs LAST.

### Creating Migrations

```bash
# Create new migration
php artisan make:migration create_table_name

# Create migration with specific timestamp
php artisan make:migration create_table_name --path=database/migrations

# Migration template
php artisan make:migration add_column_to_table_name --table=table_name
```

### Migration Best Practices

```php
// Good migration structure
public function up()
{
    Schema::create('table_name', function (Blueprint $table) {
        // Primary key
        $table->id();

        // Foreign keys (no constraints yet)
        $table->foreignId('user_id');

        // Columns
        $table->string('name');
        $table->text('description')->nullable();

        // Indexes (add early for performance)
        $table->index('user_id');
        $table->index('created_at');

        // Timestamps
        $table->timestamps();
        $table->softDeletes();
    });
}

public function down()
{
    Schema::dropIfExists('table_name');
}
```

### Foreign Key Constraints Migration

```php
// database/migrations/2025_09_24_999999_add_foreign_key_constraints.php
public function up()
{
    Schema::table('profiles', function (Blueprint $table) {
        $table->foreign('user_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
    });

    Schema::table('messages', function (Blueprint $table) {
        $table->foreign('chat_id')
            ->references('id')
            ->on('chats')
            ->onDelete('cascade');

        $table->foreign('sender_id')
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
    });
}
```

---

## Indexing Strategy

### Primary Indexes

Every table has:
- Primary key (`id`)
- Unique constraints where needed
- Timestamps

### Foreign Key Indexes

```sql
-- Always index foreign keys
INDEX idx_user_id (user_id)
INDEX idx_chat_id (chat_id)
```

### Query Optimization Indexes

```sql
-- Composite index for common queries
INDEX idx_user_gender_age (user_id, gender, age);

-- Covering index for chat messages
INDEX idx_chat_sent (chat_id, sent_at);

-- Partial index for active users
INDEX idx_active_users (last_active_at) WHERE is_active = TRUE;
```

### Common Query Patterns

**Discovery Query:**
```sql
-- Requires: idx_gender_age, idx_location
SELECT * FROM profiles
WHERE gender = 'female'
  AND age BETWEEN 25 AND 35
  AND ST_Distance_Sphere(
      point(longitude, latitude),
      point($userLng, $userLat)
  ) / 1000 <= 50
LIMIT 20;
```

**Unread Messages:**
```sql
-- Requires: idx_chat_sent, idx_message_reads
SELECT COUNT(*) FROM messages m
WHERE m.chat_id = ?
  AND m.sent_at > (
      SELECT last_read_at FROM chat_users
      WHERE chat_id = ? AND user_id = ?
  );
```

---

## Query Optimization

### Use Eager Loading

```php
// Bad: N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->profile->name;  // Query for each user
}

// Good: 2 queries total
$users = User::with('profile')->get();
foreach ($users as $user) {
    echo $user->profile->name;
}
```

### Use Query Scopes

```php
// app/Models/User.php
public function scopeActive($query)
{
    return $query->where('is_active', true)
        ->whereNull('disabled_at');
}

public function scopeOnline($query)
{
    return $query->where('last_active_at', '>', now()->subMinutes(5));
}

// Usage
$onlineUsers = User::active()->online()->get();
```

### Cache Expensive Queries

```php
// Cache discovery profiles
$profiles = Cache::remember("discovery-{$userId}", 900, function () use ($userId) {
    return User::with('profile', 'photos')
        ->active()
        ->matchingPreferences($userId)
        ->limit(50)
        ->get();
});
```

### Use Database Transactions

```php
DB::transaction(function () use ($data) {
    $user = User::create($data['user']);
    $user->profile()->create($data['profile']);
    $user->setting()->create($data['settings']);
});
```

---

## Best Practices

### 1. Always Use Migrations

Never modify database directly in production:

```bash
# Development
php artisan migrate

# Production
php artisan migrate --force
```

### 2. Use Soft Deletes

For recoverable data:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}
```

### 3. Define Relationships

Always define both sides:

```php
// User.php
public function photos(): HasMany
{
    return $this->hasMany(UserPhoto::class);
}

// UserPhoto.php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### 4. Use Fillable or Guarded

```php
protected $fillable = ['name', 'email', 'password'];

// Or
protected $guarded = ['id', 'created_at'];
```

### 5. Cast Attributes

```php
protected $casts = [
    'interests' => 'array',
    'is_active' => 'boolean',
    'date_of_birth' => 'date',
    'last_active_at' => 'datetime',
];
```

### 6. Hide Sensitive Data

```php
protected $hidden = [
    'password',
    'two_factor_secret',
    'remember_token',
];
```

### 7. Add Indexes

```php
Schema::table('messages', function (Blueprint $table) {
    $table->index('chat_id');
    $table->index(['chat_id', 'sent_at']);
});
```

### 8. Use Database Seeding

```php
// database/seeders/UserSeeder.php
User::factory()
    ->count(50)
    ->has(Profile::factory())
    ->has(UserPhoto::factory()->count(3))
    ->create();
```

---

## Common Database Tasks

### Check Migration Status

```bash
php artisan migrate:status
```

### Rollback Last Migration

```bash
php artisan migrate:rollback
```

### Reset Database (Development Only)

```bash
php artisan migrate:fresh --seed
```

### Generate Model with Migration

```bash
php artisan make:model UserPreference -m
```

### Inspect Database

```bash
# Using Tinker
php artisan tinker
> \DB::table('users')->count();
> User::with('profile')->first();

# Using raw SQL
> \DB::select('SHOW TABLES');
> \DB::select('DESCRIBE users');
```

---

**The database schema is the foundation of YorYor. Understanding these relationships and patterns is crucial for effective development.**

*Last Updated: October 2025*

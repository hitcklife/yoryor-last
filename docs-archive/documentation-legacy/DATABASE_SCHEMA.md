# YorYor Database Schema Documentation

## Overview

The YorYor database consists of **70+ tables** organized into logical groups. The schema is designed for high performance with proper indexing, foreign key constraints, and optimized queries.

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
- **Framework Tables:** 7 (Laravel system tables)

---

## Table of Contents

1. [Core User Tables](#core-user-tables)
2. [Profile Extension Tables](#profile-extension-tables)
3. [Communication Tables](#communication-tables)
4. [Matching System Tables](#matching-system-tables)
5. [Content & Media Tables](#content--media-tables)
6. [Subscription & Payment Tables](#subscription--payment-tables)
7. [Safety & Moderation Tables](#safety--moderation-tables)
8. [Matchmaker System Tables](#matchmaker-system-tables)
9. [Verification System Tables](#verification-system-tables)
10. [RBAC Tables](#rbac-tables)
11. [System & Support Tables](#system--support-tables)
12. [Laravel Framework Tables](#laravel-framework-tables)
13. [Relationships Diagram](#relationships-diagram)
14. [Indexing Strategy](#indexing-strategy)

---

## Core User Tables

### 1. `users`
Primary user authentication and basic info.

```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) UNIQUE,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(20) UNIQUE,
    password VARCHAR(255),
    google_id VARCHAR(255),
    facebook_id VARCHAR(255),
    provider VARCHAR(50),
    avatar VARCHAR(255),

    -- Status fields
    registration_completed BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_private BOOLEAN DEFAULT FALSE,

    -- 2FA fields
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
    INDEX idx_registration (registration_completed),
    INDEX idx_active (last_active_at),
    INDEX idx_uuid (uuid)
);
```

**Relationships:**
- `hasOne` → profiles
- `hasOne` → user_preferences
- `hasOne` → user_settings
- `hasMany` → user_photos
- `hasMany` → messages
- `belongsToMany` → chats (via chat_users)
- `belongsToMany` → matches
- `belongsToMany` → roles

### 2. `profiles`
Core profile information.

```sql
CREATE TABLE profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    -- Basic info
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    gender ENUM('male', 'female', 'other'),
    date_of_birth DATE,
    age INT,

    -- Professional
    occupation VARCHAR(150),
    profession VARCHAR(150),
    status VARCHAR(50),

    -- Location
    city VARCHAR(100),
    state VARCHAR(100),
    province VARCHAR(100),
    country_id BIGINT,
    country_code VARCHAR(3),
    latitude DECIMAL(10, 7),
    longitude DECIMAL(10, 7),

    -- Personal
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
    INDEX idx_gender (gender),
    INDEX idx_age (age),
    INDEX idx_location (latitude, longitude),
    INDEX idx_country (country_id)
);
```

### 3. `user_settings`
User preferences and settings.

```sql
CREATE TABLE user_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    -- Notification settings
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    message_notifications BOOLEAN DEFAULT TRUE,
    match_notifications BOOLEAN DEFAULT TRUE,
    like_notifications BOOLEAN DEFAULT TRUE,

    -- Privacy settings
    show_online_status BOOLEAN DEFAULT TRUE,
    show_last_seen BOOLEAN DEFAULT TRUE,
    show_read_receipts BOOLEAN DEFAULT TRUE,
    profile_visibility ENUM('public', 'matches_only', 'private') DEFAULT 'public',
    photo_visibility ENUM('public', 'matches_only', 'private') DEFAULT 'public',

    -- Discovery settings
    distance_unit ENUM('km', 'miles') DEFAULT 'km',
    show_me ENUM('everyone', 'gender_preference') DEFAULT 'gender_preference',

    -- Security settings
    require_2fa BOOLEAN DEFAULT FALSE,

    -- UI preferences
    theme VARCHAR(20) DEFAULT 'system',
    language VARCHAR(5) DEFAULT 'en',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 4. `user_preferences`
Matching and discovery preferences.

```sql
CREATE TABLE user_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    -- Basic preferences
    gender_preference ENUM('male', 'female', 'both'),
    min_age INT DEFAULT 18,
    max_age INT DEFAULT 99,
    search_radius INT DEFAULT 50,

    -- Relationship preferences
    relationship_goal VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 5. `user_photos`
Profile photos and media.

```sql
CREATE TABLE user_photos (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    url VARCHAR(500),
    thumbnail_url VARCHAR(500),
    file_path VARCHAR(500),
    file_size INT,
    mime_type VARCHAR(100),
    width INT,
    height INT,

    is_profile_photo BOOLEAN DEFAULT FALSE,
    order INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    verification_status ENUM('pending', 'approved', 'rejected'),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_profile_photo (user_id, is_profile_photo),
    INDEX idx_order (user_id, order)
);
```

### 6. `user_activities`
User activity tracking.

```sql
CREATE TABLE user_activities (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    activity_type ENUM('login', 'profile_view', 'like', 'message', 'match', 'call'),
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    metadata JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_type (activity_type),
    INDEX idx_created (created_at)
);
```

---

## Profile Extension Tables

### 7. `user_cultural_profiles`
Cultural and religious background.

```sql
CREATE TABLE user_cultural_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    religion VARCHAR(100),
    sect VARCHAR(100),
    religiosity_level ENUM('very_religious', 'moderately_religious', 'not_religious'),
    prayer_frequency ENUM('five_times', 'sometimes', 'rarely', 'never'),
    hijab_preference VARCHAR(50),
    halal_preference BOOLEAN,

    languages JSON,
    ethnicity VARCHAR(100),
    cultural_values TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 8. `user_career_profiles`
Education and career information.

```sql
CREATE TABLE user_career_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

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

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 9. `user_physical_profiles`
Physical attributes and preferences.

```sql
CREATE TABLE user_physical_profiles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    height INT,
    body_type VARCHAR(50),
    eye_color VARCHAR(50),
    hair_color VARCHAR(50),

    exercise_frequency VARCHAR(50),
    dietary_preferences VARCHAR(100),
    smoking VARCHAR(50),
    drinking VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 10. `user_family_preferences`
Family background and expectations.

```sql
CREATE TABLE user_family_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    marital_status VARCHAR(50),
    has_children BOOLEAN,
    num_children INT,
    wants_children VARCHAR(50),

    family_values TEXT,
    family_involvement VARCHAR(100),
    living_situation VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 11. `user_location_preferences`
Location and relocation preferences.

```sql
CREATE TABLE user_location_preferences (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    willing_to_relocate BOOLEAN DEFAULT FALSE,
    preferred_countries JSON,
    preferred_cities JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 12. `user_prayer_times`
Islamic prayer time preferences.

```sql
CREATE TABLE user_prayer_times (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    fajr_enabled BOOLEAN DEFAULT TRUE,
    dhuhr_enabled BOOLEAN DEFAULT TRUE,
    asr_enabled BOOLEAN DEFAULT TRUE,
    maghrib_enabled BOOLEAN DEFAULT TRUE,
    isha_enabled BOOLEAN DEFAULT TRUE,

    reminder_minutes_before INT DEFAULT 15,
    notification_sound VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

---

## Communication Tables

### 13. `chats`
Chat conversations.

```sql
CREATE TABLE chats (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    type ENUM('private', 'group') DEFAULT 'private',
    name VARCHAR(255),
    description TEXT,

    is_active BOOLEAN DEFAULT TRUE,
    last_activity_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    INDEX idx_type (type),
    INDEX idx_last_activity (last_activity_at)
);
```

### 14. `chat_users` (Pivot Table)
Chat participants.

```sql
CREATE TABLE chat_users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT,
    user_id BIGINT,

    role ENUM('member', 'admin') DEFAULT 'member',
    is_muted BOOLEAN DEFAULT FALSE,

    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    left_at TIMESTAMP NULL,
    last_read_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_chat_user (chat_id, user_id),
    INDEX idx_chat (chat_id),
    INDEX idx_user (user_id),
    INDEX idx_last_read (last_read_at)
);
```

### 15. `messages`
Chat messages.

```sql
CREATE TABLE messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    chat_id BIGINT,
    sender_id BIGINT,

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

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (call_id) REFERENCES calls(id) ON DELETE SET NULL,

    INDEX idx_chat (chat_id),
    INDEX idx_sender (sender_id),
    INDEX idx_sent (sent_at),
    INDEX idx_call (call_id)
);
```

### 16. `message_reads`
Message read receipts.

```sql
CREATE TABLE message_reads (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message_id BIGINT,
    user_id BIGINT,

    read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_message_user (message_id, user_id),
    INDEX idx_message (message_id),
    INDEX idx_user (user_id)
);
```

### 17. `calls`
Video and voice calls.

```sql
CREATE TABLE calls (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    initiator_id BIGINT,
    receiver_id BIGINT,

    type ENUM('video', 'audio') DEFAULT 'video',
    status ENUM('initiated', 'ringing', 'answered', 'ended', 'rejected', 'missed') DEFAULT 'initiated',

    meeting_id VARCHAR(255),
    channel_name VARCHAR(255),
    agora_token TEXT,

    started_at TIMESTAMP NULL,
    answered_at TIMESTAMP NULL,
    ended_at TIMESTAMP NULL,

    duration INT DEFAULT 0,

    end_reason VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (initiator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_initiator (initiator_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (status),
    INDEX idx_started (started_at)
);
```

---

## Matching System Tables

### 18. `matches`
Mutual matches (both users liked each other).

```sql
CREATE TABLE matches (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    matched_user_id BIGINT,

    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (matched_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_match (user_id, matched_user_id),
    INDEX idx_user (user_id),
    INDEX idx_matched (matched_user_id),
    INDEX idx_matched_at (matched_at)
);
```

### 19. `likes`
User likes (swipe right).

```sql
CREATE TABLE likes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    liked_user_id BIGINT,

    source VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_like (user_id, liked_user_id),
    INDEX idx_user (user_id),
    INDEX idx_liked (liked_user_id),
    INDEX idx_created (created_at)
);
```

### 20. `dislikes`
User dislikes (swipe left).

```sql
CREATE TABLE dislikes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    disliked_user_id BIGINT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (disliked_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_dislike (user_id, disliked_user_id),
    INDEX idx_user (user_id),
    INDEX idx_disliked (disliked_user_id)
);
```

---

## Content & Media Tables

### 21. `user_stories`
24-hour ephemeral stories.

```sql
CREATE TABLE user_stories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    media_url VARCHAR(500),
    thumbnail_url VARCHAR(500),
    type ENUM('image', 'video') DEFAULT 'image',

    caption TEXT,
    duration INT DEFAULT 24,

    status ENUM('active', 'expired', 'deleted') DEFAULT 'active',
    views_count INT DEFAULT 0,

    expires_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_expires (expires_at)
);
```

### 22. `media`
Generic media storage (Spatie Media Library).

```sql
CREATE TABLE media (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    model_type VARCHAR(255),
    model_id BIGINT,

    uuid CHAR(36),
    collection_name VARCHAR(255),
    name VARCHAR(255),
    file_name VARCHAR(255),
    mime_type VARCHAR(255),
    disk VARCHAR(255),
    conversions_disk VARCHAR(255),
    size BIGINT,

    manipulations JSON,
    custom_properties JSON,
    generated_conversions JSON,
    responsive_images JSON,

    order_column INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_model (model_type, model_id),
    INDEX idx_uuid (uuid)
);
```

---

## Subscription & Payment Tables

### 23. `subscription_plans`
Available subscription tiers.

```sql
CREATE TABLE subscription_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(100),
    slug VARCHAR(100) UNIQUE,
    description TEXT,

    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE,

    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
);
```

### 24. `plan_features`
Features included in plans.

```sql
CREATE TABLE plan_features (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_plan_id BIGINT,

    feature_key VARCHAR(100),
    feature_name VARCHAR(255),
    feature_value TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,

    INDEX idx_plan (subscription_plan_id),
    INDEX idx_key (feature_key)
);
```

### 25. `plan_pricing`
Pricing tiers for plans.

```sql
CREATE TABLE plan_pricing (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    subscription_plan_id BIGINT,

    duration ENUM('monthly', 'quarterly', 'yearly'),
    price DECIMAL(10, 2),
    currency VARCHAR(3) DEFAULT 'USD',

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE,

    INDEX idx_plan (subscription_plan_id)
);
```

### 26. `user_subscriptions`
Active user subscriptions.

```sql
CREATE TABLE user_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    subscription_plan_id BIGINT,

    status ENUM('active', 'cancelled', 'expired', 'trial') DEFAULT 'trial',

    starts_at TIMESTAMP,
    ends_at TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    trial_ends_at TIMESTAMP NULL,

    auto_renew BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plans(id) ON DELETE RESTRICT,

    INDEX idx_user (user_id),
    INDEX idx_plan (subscription_plan_id),
    INDEX idx_status (status),
    INDEX idx_ends (ends_at)
);
```

### 27. `payment_transactions`
Payment history.

```sql
CREATE TABLE payment_transactions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    user_subscription_id BIGINT NULL,

    amount DECIMAL(10, 2),
    currency VARCHAR(3) DEFAULT 'USD',

    status ENUM('pending', 'completed', 'failed', 'refunded'),
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255) UNIQUE,

    gateway_response JSON,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_subscription_id) REFERENCES user_subscriptions(id) ON DELETE SET NULL,

    INDEX idx_user (user_id),
    INDEX idx_subscription (user_subscription_id),
    INDEX idx_status (status),
    INDEX idx_transaction (transaction_id)
);
```

### 28. `user_usage_limits`
Current usage tracking for limits.

```sql
CREATE TABLE user_usage_limits (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    likes_remaining INT DEFAULT 50,
    super_likes_remaining INT DEFAULT 5,
    messages_remaining INT DEFAULT -1,
    profile_views_remaining INT DEFAULT -1,

    reset_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
);
```

### 29. `user_monthly_usage`
Historical monthly usage stats.

```sql
CREATE TABLE user_monthly_usage (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    month DATE,

    likes_sent INT DEFAULT 0,
    messages_sent INT DEFAULT 0,
    profile_views INT DEFAULT 0,
    matches_made INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_month (user_id, month),
    INDEX idx_user (user_id),
    INDEX idx_month (month)
);
```

---

## Safety & Moderation Tables

### 30. `user_blocks`
Blocked users.

```sql
CREATE TABLE user_blocks (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    blocked_user_id BIGINT,

    reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_block (user_id, blocked_user_id),
    INDEX idx_user (user_id),
    INDEX idx_blocked (blocked_user_id)
);
```

### 31. `user_reports`
Basic user reports.

```sql
CREATE TABLE user_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    reporter_id BIGINT,
    reported_user_id BIGINT,

    reason VARCHAR(255),
    description TEXT,

    status ENUM('pending', 'reviewing', 'resolved', 'dismissed') DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_reporter (reporter_id),
    INDEX idx_reported (reported_user_id),
    INDEX idx_status (status)
);
```

### 32. `enhanced_user_reports`
Detailed reporting system.

```sql
CREATE TABLE enhanced_user_reports (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    reporter_id BIGINT,
    reported_user_id BIGINT,

    category VARCHAR(100),
    sub_category VARCHAR(100),
    description TEXT,

    severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    status ENUM('pending', 'under_review', 'action_taken', 'dismissed') DEFAULT 'pending',

    admin_notes TEXT,
    action_taken TEXT,
    reviewed_by BIGINT NULL,
    reviewed_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_reporter (reporter_id),
    INDEX idx_reported (reported_user_id),
    INDEX idx_status (status),
    INDEX idx_severity (severity)
);
```

### 33. `report_evidence`
Evidence attachments for reports.

```sql
CREATE TABLE report_evidence (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    enhanced_user_report_id BIGINT,

    evidence_type ENUM('screenshot', 'message', 'media', 'other'),
    file_path VARCHAR(500),
    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (enhanced_user_report_id) REFERENCES enhanced_user_reports(id) ON DELETE CASCADE,

    INDEX idx_report (enhanced_user_report_id)
);
```

### 34. `user_safety_scores`
Automated safety scoring.

```sql
CREATE TABLE user_safety_scores (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    score INT DEFAULT 100,

    reports_received INT DEFAULT 0,
    blocks_received INT DEFAULT 0,
    inappropriate_content_flags INT DEFAULT 0,

    last_incident_at TIMESTAMP NULL,
    last_calculated_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_score (score)
);
```

### 35. `panic_activations`
Panic button activation logs.

```sql
CREATE TABLE panic_activations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    trigger_reason VARCHAR(255),
    location_lat DECIMAL(10, 7),
    location_lng DECIMAL(10, 7),

    status ENUM('active', 'cancelled', 'resolved') DEFAULT 'active',

    activated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,

    admin_notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_activated (activated_at)
);
```

### 36. `emergency_contacts`
User emergency contacts.

```sql
CREATE TABLE emergency_contacts (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    name VARCHAR(255),
    relationship VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(255),

    is_primary BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_primary (user_id, is_primary)
);
```

### 37. `user_feedback`
General user feedback.

```sql
CREATE TABLE user_feedback (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    type ENUM('bug', 'feature_request', 'complaint', 'compliment', 'other'),
    subject VARCHAR(255),
    message TEXT,

    status ENUM('new', 'acknowledged', 'resolved', 'dismissed') DEFAULT 'new',

    admin_response TEXT,
    responded_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_type (type),
    INDEX idx_status (status)
);
```

---

## Matchmaker System Tables

### 38. `matchmakers`
Professional matchmaker profiles.

```sql
CREATE TABLE matchmakers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNIQUE,

    company_name VARCHAR(255),
    bio TEXT,
    specialization JSON,
    languages JSON,

    years_experience INT,
    success_rate DECIMAL(5, 2),
    total_matches INT DEFAULT 0,

    hourly_rate DECIMAL(10, 2),
    consultation_fee DECIMAL(10, 2),

    is_verified BOOLEAN DEFAULT FALSE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured)
);
```

### 39. `matchmaker_services`
Services offered by matchmakers.

```sql
CREATE TABLE matchmaker_services (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,

    service_name VARCHAR(255),
    description TEXT,
    price DECIMAL(10, 2),
    duration_minutes INT,

    is_active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id)
);
```

### 40. `matchmaker_clients`
Client-matchmaker relationships.

```sql
CREATE TABLE matchmaker_clients (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,
    user_id BIGINT,

    status ENUM('active', 'paused', 'completed', 'cancelled') DEFAULT 'active',

    hired_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### 41. `matchmaker_consultations`
Scheduled consultations.

```sql
CREATE TABLE matchmaker_consultations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,
    user_id BIGINT,

    scheduled_at TIMESTAMP,
    duration_minutes INT DEFAULT 60,

    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',

    notes TEXT,
    meeting_link VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id),
    INDEX idx_user (user_id),
    INDEX idx_scheduled (scheduled_at)
);
```

### 42. `matchmaker_introductions`
Matchmaker-made introductions.

```sql
CREATE TABLE matchmaker_introductions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,
    user1_id BIGINT,
    user2_id BIGINT,

    message TEXT,

    user1_response ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
    user2_response ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id),
    INDEX idx_users (user1_id, user2_id)
);
```

### 43. `matchmaker_reviews`
Client reviews of matchmakers.

```sql
CREATE TABLE matchmaker_reviews (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,
    user_id BIGINT,

    rating INT CHECK (rating >= 1 AND rating <= 5),
    review TEXT,

    is_verified BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id),
    INDEX idx_rating (rating)
);
```

### 44. `matchmaker_availability`
Matchmaker schedule availability.

```sql
CREATE TABLE matchmaker_availability (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    matchmaker_id BIGINT,

    day_of_week TINYINT,
    start_time TIME,
    end_time TIME,

    is_available BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (matchmaker_id) REFERENCES matchmakers(id) ON DELETE CASCADE,

    INDEX idx_matchmaker (matchmaker_id)
);
```

---

## Verification System Tables

### 45. `verification_requests`
User verification submissions.

```sql
CREATE TABLE verification_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    verification_type ENUM('identity', 'photo', 'employment', 'education', 'income'),

    status ENUM('pending', 'under_review', 'approved', 'rejected') DEFAULT 'pending',

    documents JSON,
    notes TEXT,

    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    reviewed_by BIGINT NULL,

    rejection_reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_type (verification_type)
);
```

### 46. `user_verified_badges`
Earned verification badges.

```sql
CREATE TABLE user_verified_badges (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    badge_type ENUM('identity', 'photo', 'employment', 'education', 'income'),

    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_badge (user_id, badge_type),
    INDEX idx_user (user_id),
    INDEX idx_type (badge_type)
);
```

---

## RBAC Tables

### 47. `roles`
User roles.

```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(100) UNIQUE,
    slug VARCHAR(100) UNIQUE,
    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug)
);
```

### 48. `permissions`
System permissions.

```sql
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(100) UNIQUE,
    slug VARCHAR(100) UNIQUE,
    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_slug (slug)
);
```

### 49. `role_user` (Pivot)
User role assignments.

```sql
CREATE TABLE role_user (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    role_id BIGINT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,

    UNIQUE KEY unique_user_role (user_id, role_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role_id)
);
```

### 50. `permission_role` (Pivot)
Role permission assignments.

```sql
CREATE TABLE permission_role (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    permission_id BIGINT,
    role_id BIGINT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,

    UNIQUE KEY unique_permission_role (permission_id, role_id),
    INDEX idx_permission (permission_id),
    INDEX idx_role (role_id)
);
```

---

## System & Support Tables

### 51. `countries`
Country reference data.

```sql
CREATE TABLE countries (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    name VARCHAR(255),
    code VARCHAR(3) UNIQUE,
    dial_code VARCHAR(10),

    flag_emoji VARCHAR(10),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_code (code)
);
```

### 52. `otp_codes`
OTP verification codes.

```sql
CREATE TABLE otp_codes (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,

    identifier VARCHAR(255),
    code VARCHAR(10),
    type ENUM('email', 'phone'),

    expires_at TIMESTAMP,
    verified_at TIMESTAMP NULL,

    attempts INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_identifier (identifier),
    INDEX idx_code (code),
    INDEX idx_expires (expires_at)
);
```

### 53. `device_tokens`
Push notification device tokens.

```sql
CREATE TABLE device_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    token TEXT,
    device_type VARCHAR(50),
    device_name VARCHAR(255),

    is_active BOOLEAN DEFAULT TRUE,

    last_used_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_active (is_active)
);
```

### 54. `notifications`
In-app notifications.

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255),
    notifiable_type VARCHAR(255),
    notifiable_id BIGINT,

    data TEXT,

    read_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_notifiable (notifiable_type, notifiable_id),
    INDEX idx_read (read_at)
);
```

### 55. `data_export_requests`
GDPR data export requests.

```sql
CREATE TABLE data_export_requests (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',

    file_path VARCHAR(500),
    expires_at TIMESTAMP NULL,

    completed_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### 56. `family_members`
Family member accounts.

```sql
CREATE TABLE family_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,

    name VARCHAR(255),
    relationship VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),

    has_account BOOLEAN DEFAULT FALSE,
    linked_user_id BIGINT NULL,

    can_view_profile BOOLEAN DEFAULT FALSE,
    can_approve_matches BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (linked_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_user (user_id),
    INDEX idx_linked (linked_user_id)
);
```

### 57. `family_approvals`
Family match approval system.

```sql
CREATE TABLE family_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    match_id BIGINT,
    family_member_id BIGINT,

    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',

    comments TEXT,

    responded_at TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (family_member_id) REFERENCES family_members(id) ON DELETE CASCADE,

    INDEX idx_user (user_id),
    INDEX idx_match (match_id),
    INDEX idx_family (family_member_id),
    INDEX idx_status (status)
);
```

---

## Laravel Framework Tables

### 58. `sessions`
User sessions (database driver).

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    payload TEXT,
    last_activity INT,

    INDEX idx_user (user_id),
    INDEX idx_last_activity (last_activity)
);
```

### 59. `cache`
Cache storage (database driver).

```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT,
    expiration INT,

    INDEX idx_expiration (expiration)
);
```

### 60. `jobs`
Queue jobs.

```sql
CREATE TABLE jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255),
    payload TEXT,
    attempts TINYINT,
    reserved_at INT NULL,
    available_at INT,
    created_at INT,

    INDEX idx_queue (queue),
    INDEX idx_reserved (reserved_at)
);
```

### 61. `failed_jobs`
Failed queue jobs.

```sql
CREATE TABLE failed_jobs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(255) UNIQUE,
    connection TEXT,
    queue TEXT,
    payload TEXT,
    exception TEXT,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_uuid (uuid)
);
```

### 62. `password_reset_tokens`
Password reset tokens.

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255),
    created_at TIMESTAMP,

    INDEX idx_token (token)
);
```

### 63. `personal_access_tokens`
Sanctum API tokens.

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255),
    tokenable_id BIGINT,
    name VARCHAR(255),
    token VARCHAR(64) UNIQUE,
    abilities TEXT,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_tokenable (tokenable_type, tokenable_id),
    INDEX idx_token (token)
);
```

### 64-70. Laravel Pulse, Telescope Tables
Additional monitoring and debugging tables (7+ tables for Pulse and Telescope).

---

## Relationships Diagram

### Core Entity Relationships

```
users (1) ─────< (1) profiles
users (1) ─────< (1) user_preferences
users (1) ─────< (1) user_settings
users (1) ─────< (1) user_cultural_profiles
users (1) ─────< (1) user_career_profiles
users (1) ─────< (1) user_physical_profiles
users (1) ─────< (1) user_family_preferences
users (1) ─────< (1) user_location_preferences

users (1) ─────< (Many) user_photos
users (1) ─────< (Many) user_stories
users (1) ─────< (Many) messages
users (1) ─────< (Many) likes (sent)
users (1) ─────< (Many) likes (received)

users (Many) >───────< (Many) chats (via chat_users)
users (Many) >───────< (Many) matches

chats (1) ─────< (Many) messages
chats (1) ─────< (Many) chat_users

messages (1) ─────< (Many) message_reads

users (1) ─────< (Many) calls (as initiator)
users (1) ─────< (Many) calls (as receiver)

users (1) ─────< (1) matchmakers
matchmakers (1) ─────< (Many) matchmaker_services
matchmakers (1) ─────< (Many) matchmaker_clients
matchmakers (1) ─────< (Many) matchmaker_consultations

users (1) ─────< (Many) verification_requests
users (1) ─────< (Many) user_verified_badges

subscription_plans (1) ─────< (Many) user_subscriptions
user_subscriptions (1) ─────< (Many) payment_transactions

users (1) ─────< (Many) user_blocks (blocker)
users (1) ─────< (Many) user_blocks (blocked)
users (1) ─────< (Many) user_reports

users (Many) >───────< (Many) roles (via role_user)
roles (Many) >───────< (Many) permissions (via permission_role)
```

---

## Indexing Strategy

### Primary Indexes
- Every table has a primary key (`id`)
- Unique constraints on email, phone, UUIDs
- Composite unique keys on pivot tables

### Foreign Key Indexes
- All foreign keys are indexed
- Cascading deletes configured where appropriate
- Soft deletes enabled on key tables

### Query Optimization Indexes
```sql
-- User discovery
INDEX idx_user_discovery (gender, age, city, is_private);

-- Matching queries
INDEX idx_matching (user_id, liked_user_id, created_at);

-- Chat queries
INDEX idx_chat_activity (chat_id, last_activity_at);
INDEX idx_unread_messages (chat_id, user_id, read_at);

-- Presence queries
INDEX idx_online_status (last_active_at, is_active);

-- Subscription queries
INDEX idx_active_subscriptions (user_id, status, ends_at);

-- Safety queries
INDEX idx_reports_pending (reported_user_id, status);
```

### Composite Indexes
```sql
-- Multi-column indexes for common queries
INDEX idx_user_match_search (user_id, gender, age, city);
INDEX idx_chat_user_activity (chat_id, user_id, last_read_at);
INDEX idx_subscription_status (user_id, status, ends_at);
```

---

## Migration Structure

Migrations are organized chronologically:

```
database/migrations/
├── 2025_09_24_211011_create_users_table.php
├── 2025_09_24_211016_create_countries_table.php
├── 2025_09_24_211017_create_profiles_table.php
├── ... (60+ migration files)
└── 2025_09_24_999999_add_foreign_key_constraints.php
```

**Migration Execution Order:**
1. System tables (countries, cache, sessions)
2. Core user tables (users, profiles)
3. Profile extension tables
4. Communication tables (chats, messages)
5. Matching tables (likes, matches)
6. Subscription tables
7. Advanced feature tables (matchmaker, verification)
8. Foreign key constraints (last)

---

**Last Updated:** 2025-09-30
**Database Version:** 1.0.0
**Total Tables:** 70+
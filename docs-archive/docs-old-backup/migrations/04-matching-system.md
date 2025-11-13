# Matching System - Consolidated Migrations

This document contains consolidated migration files for the matching and interaction system.

## 1. Matches Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();

            // Match participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('matched_user_id')->constrained('users')->onDelete('cascade');

            // Match details
            $table->timestamp('matched_at')->default(now());

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('matched_user_id');
            $table->index(['user_id', 'matched_at']);
            $table->index(['matched_user_id', 'matched_at']);

            // Ensure unique matches
            $table->unique(['user_id', 'matched_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
```

## 2. Likes Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // Like participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('liked_user_id')->constrained('users')->onDelete('cascade');

            // Like details
            $table->timestamp('liked_at')->default(now());

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('liked_user_id');
            $table->index(['user_id', 'liked_at']);
            $table->index(['liked_user_id', 'created_at']);

            // Ensure unique likes
            $table->unique(['user_id', 'liked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
```

## 3. Dislikes Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dislikes', function (Blueprint $table) {
            $table->id();

            // Dislike participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('disliked_user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('disliked_user_id');
            $table->index(['user_id', 'created_at']);

            // Ensure unique dislikes
            $table->unique(['user_id', 'disliked_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dislikes');
    }
};
```

## 4. User Photos Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Photo URLs
            $table->string('original_url', 500);
            $table->string('thumbnail_url', 500);
            $table->string('medium_url', 500);

            // Photo properties
            $table->boolean('is_profile_photo')->default(false);
            $table->unsignedTinyInteger('order')->default(0);
            $table->boolean('is_private')->default(false);
            $table->boolean('is_verified')->default(false);

            // Moderation
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('rejection_reason')->nullable();

            // Metadata
            $table->json('metadata')->nullable();
            $table->timestamp('uploaded_at')->default(now());

            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'is_profile_photo', 'status']);
            $table->index(['user_id', 'order', 'status']);
            $table->index(['status', 'uploaded_at']);
            $table->index('is_verified');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_photos');
    }
};
```

## 5. User Stories Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_stories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Story content
            $table->string('media_url');
            $table->string('thumbnail_url')->nullable();
            $table->enum('type', ['image', 'video'])->default('image');
            $table->text('caption')->nullable();

            // Story lifecycle
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'expired'])->default('active');

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status', 'expires_at']);
            $table->index(['status', 'expires_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stories');
    }
};
```

## 6. User Activities Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Activity details
            $table->enum('activity_type', [
                'login',
                'logout',
                'swipe_right',
                'swipe_left',
                'message_sent',
                'profile_view',
                'photo_upload',
                'match_made',
                'profile_updated'
            ]);

            // Additional data
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->default(now());

            // Extensive indexing for analytics
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index('created_at');
            $table->index(['user_id', 'activity_type', 'created_at']);
            $table->index(['activity_type', 'user_id']);
            $table->index(['created_at', 'activity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
```

## 7. Device Tokens Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Token
            $table->string('token')->unique();

            // Device information
            $table->string('device_name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_name')->nullable();
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->enum('device_type', ['PHONE', 'TABLET', 'DESKTOP', 'OTHER'])->default('PHONE');
            $table->boolean('is_device')->default(true);
            $table->string('manufacturer')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('token');
            $table->index(['user_id', 'device_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
```

## Performance Considerations

These tables include comprehensive indexing strategies:

1. **Matches**: Indexed for both user perspectives and chronological queries
2. **Likes/Dislikes**: Indexed for checking existing interactions and discovery algorithms
3. **User Photos**: Multiple indexes for profile display and moderation workflows
4. **User Stories**: Time-based indexes for expiration handling
5. **User Activities**: Extensive indexing for analytics and user behavior tracking
6. **Device Tokens**: Indexed for push notification delivery

## Migration Dependencies

These tables depend on the users table being created first. The recommended migration order is:

1. Users table (prerequisite)
2. Matches
3. Likes
4. Dislikes
5. User Photos
6. User Stories
7. User Activities
8. Device Tokens
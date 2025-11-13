#!/usr/bin/env php
<?php

$migrations = [
    // Remaining Core Tables
    '/01_core/2025_09_24_211017_create_sessions_table.php' => <<<'PHP'
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
PHP,

    '/01_core/2025_09_24_211017_create_password_reset_tokens_table.php' => <<<'PHP'
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
PHP,

    '/01_core/2025_09_24_211017_create_personal_access_tokens_table.php' => <<<'PHP'
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });
PHP,

    // Chat System - MUST BE IN ORDER!
    '/03_chat/2025_09_24_211244_create_calls_table.php' => <<<'PHP'
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name')->unique();

            // Call participants
            $table->foreignId('caller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            // Call details
            $table->enum('type', ['video', 'voice'])->default('video');
            $table->enum('status', [
                'initiated',
                'ongoing',
                'completed',
                'missed',
                'rejected'
            ])->default('initiated');

            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['caller_id', 'created_at']);
            $table->index(['receiver_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index(['type', 'status']);
        });
PHP,

    '/03_chat/2025_09_24_211244_create_chats_table.php' => <<<'PHP'
        Schema::create('chats', function (Blueprint $table) {
            $table->id();

            // Chat properties
            $table->string('type')->default('private');
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            // Activity tracking
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('message_count')->default(0);
            $table->string('last_message_type')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['type', 'is_active']);
            $table->index('last_activity_at');
            $table->index(['is_active', 'last_activity_at']);
            $table->index(['type', 'is_active', 'last_activity_at']);
        });
PHP,

    '/03_chat/2025_09_24_211244_create_messages_table.php' => <<<'PHP'
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // Reply functionality
            $table->foreignId('reply_to_message_id')
                  ->nullable()
                  ->constrained('messages')
                  ->onDelete('set null');

            // Call integration
            $table->foreignId('call_id')
                  ->nullable()
                  ->constrained('calls')
                  ->onDelete('set null');

            // Message content
            $table->text('content')->nullable();
            $table->string('message_type')->default('text');

            // Media support
            $table->json('media_data')->nullable();
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();

            // Status tracking
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('sent_at')->default(now());

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['chat_id', 'sent_at']);
            $table->index(['sender_id', 'sent_at']);
            $table->index(['chat_id', 'message_type']);
            $table->index(['chat_id', 'deleted_at', 'sent_at']);
            $table->index(['call_id', 'sent_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['message_type', 'created_at']);
        });
PHP,

    '/03_chat/2025_09_24_211244_create_chat_users_table.php' => <<<'PHP'
        Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // User settings
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_read_at')->nullable();

            // Participation tracking
            $table->timestamp('joined_at')->default(now());
            $table->timestamp('left_at')->nullable();
            $table->string('role')->default('member');

            $table->timestamps();

            // Unique constraint
            $table->unique(['chat_id', 'user_id']);

            // Performance indexes
            $table->index(['user_id', 'is_muted']);
            $table->index(['chat_id', 'left_at']);
            $table->index(['user_id', 'left_at', 'last_read_at']);
            $table->index(['chat_id', 'left_at', 'role']);
        });
PHP,

    '/03_chat/2025_09_24_211244_create_message_reads_table.php' => <<<'PHP'
        Schema::create('message_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('read_at')->default(now());

            // Unique constraint
            $table->unique(['message_id', 'user_id']);

            // Performance indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['message_id', 'read_at']);
        });
PHP,

    '/03_chat/2025_09_24_211245_create_media_table.php' => <<<'PHP'
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');

            // Media details
            $table->string('media_url');
            $table->enum('media_type', ['photo', 'video', 'voice', 'other']);

            $table->timestamp('uploaded_at')->default(now());
            $table->timestamps();

            // Indexes
            $table->index(['message_id', 'media_type']);
            $table->index('uploaded_at');
        });
PHP,

    '/03_chat/2025_09_24_211245_create_notifications_table.php' => <<<'PHP'
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['notifiable_type', 'notifiable_id']);
            $table->index('read_at');
            $table->index(['type', 'created_at']);
        });
PHP,

    // Matching System
    '/04_matching/2025_09_24_211245_create_matches_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211245_create_likes_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211245_create_dislikes_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211246_create_user_photos_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211246_create_user_stories_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211246_create_user_activities_table.php' => <<<'PHP'
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
PHP,

    '/04_matching/2025_09_24_211246_create_device_tokens_table.php' => <<<'PHP'
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
PHP,
];

// Process each migration file
$basePath = '/Users/khurshidjumaboev/Desktop/yoryor/yoryor-last/database/migrations';

foreach ($migrations as $path => $schema) {
    $fullPath = $basePath . $path;

    if (!file_exists($fullPath)) {
        echo "❌ File not found: $fullPath\n";
        continue;
    }

    $content = file_get_contents($fullPath);

    // Replace the Schema::create block
    $pattern = '/Schema::create\([^{]*\{[^}]*\}\);/s';
    $newContent = preg_replace($pattern, $schema, $content, 1);

    if ($newContent !== $content) {
        file_put_contents($fullPath, $newContent);
        echo "✅ Updated: " . basename($path) . "\n";
    } else {
        echo "⚠️  No changes: " . basename($path) . "\n";
    }
}

echo "\n✨ Migration population complete!\n";
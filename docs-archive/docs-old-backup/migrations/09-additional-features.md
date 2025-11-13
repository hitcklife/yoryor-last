# Additional Features - Consolidated Migrations

This document contains consolidated migration files for additional features including AI compatibility, verification, family approval, and prayer times.

## 1. AI Compatibility Tables Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Personality Assessments
        Schema::create('ai_personality_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Big Five Personality Traits (0-100 scale)
            $table->integer('openness')->nullable();
            $table->integer('conscientiousness')->nullable();
            $table->integer('extraversion')->nullable();
            $table->integer('agreeableness')->nullable();
            $table->integer('neuroticism')->nullable();

            // Additional traits
            $table->jsonb('personality_profile')->nullable();
            $table->jsonb('interests_analysis')->nullable();
            $table->jsonb('communication_style')->nullable();

            // Assessment metadata
            $table->timestamp('assessed_at')->nullable();
            $table->integer('confidence_score')->nullable(); // 0-100

            $table->timestamps();
        });

        // AI Compatibility Scores
        Schema::create('ai_compatibility_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');

            // Compatibility metrics
            $table->decimal('overall_score', 5, 2); // 0-100
            $table->decimal('personality_match', 5, 2)->nullable();
            $table->decimal('interests_match', 5, 2)->nullable();
            $table->decimal('values_match', 5, 2)->nullable();
            $table->decimal('lifestyle_match', 5, 2)->nullable();
            $table->decimal('communication_match', 5, 2)->nullable();

            // Detailed analysis
            $table->jsonb('compatibility_breakdown')->nullable();
            $table->jsonb('strengths')->nullable();
            $table->jsonb('challenges')->nullable();
            $table->text('ai_recommendation')->nullable();

            // Caching
            $table->timestamp('calculated_at');
            $table->timestamp('expires_at');

            $table->timestamps();

            // Indexes
            $table->unique(['user1_id', 'user2_id']);
            $table->index(['user1_id', 'overall_score']);
            $table->index(['user2_id', 'overall_score']);
            $table->index('expires_at');
        });

        // AI Conversation Starters
        Schema::create('ai_conversation_starters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user1_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user2_id')->constrained('users')->onDelete('cascade');

            // Suggestions
            $table->jsonb('icebreakers'); // Array of conversation starters
            $table->jsonb('common_interests');
            $table->jsonb('discussion_topics');

            // Usage tracking
            $table->jsonb('used_starters')->nullable();
            $table->integer('effectiveness_score')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['user1_id', 'user2_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversation_starters');
        Schema::dropIfExists('ai_compatibility_scores');
        Schema::dropIfExists('ai_personality_assessments');
    }
};
```

## 2. Verification System Tables Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verification Requests
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Verification type
            $table->enum('type', [
                'photo',
                'government_id',
                'education',
                'employment',
                'social_media',
                'phone'
            ]);

            // Documents
            $table->jsonb('documents')->nullable(); // URLs to uploaded documents
            $table->jsonb('metadata')->nullable(); // Additional verification data

            // Status
            $table->enum('status', [
                'pending',
                'under_review',
                'approved',
                'rejected',
                'expired'
            ])->default('pending');

            // Review details
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Validity
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'type', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('expires_at');
        });

        // Verified Badges
        Schema::create('user_verified_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Badge details
            $table->enum('badge_type', [
                'photo_verified',
                'id_verified',
                'education_verified',
                'employment_verified',
                'social_verified',
                'phone_verified',
                'premium_member',
                'trusted_member'
            ]);

            // Verification reference
            $table->foreignId('verification_request_id')
                  ->nullable()
                  ->constrained('verification_requests');

            // Badge status
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_active']);
            $table->index(['badge_type', 'is_active']);
            $table->unique(['user_id', 'badge_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verified_badges');
        Schema::dropIfExists('verification_requests');
    }
};
```

## 3. Family Approval System Tables Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Family Members
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Family member details
            $table->string('name', 100);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->enum('relationship', [
                'father',
                'mother',
                'brother',
                'sister',
                'guardian',
                'other'
            ]);

            // Permissions
            $table->boolean('can_approve_matches')->default(false);
            $table->boolean('can_view_matches')->default(false);
            $table->boolean('receives_notifications')->default(false);

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->string('verification_code', 6)->nullable();
            $table->timestamp('verified_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'can_approve_matches']);
        });

        // Family Approvals
        Schema::create('family_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained('family_members');

            // Approval details
            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'expired'
            ])->default('pending');

            $table->text('comments')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['family_member_id', 'status']);
            $table->unique(['user_id', 'match_user_id', 'family_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_approvals');
        Schema::dropIfExists('family_members');
    }
};
```

## 4. Prayer Time Tables Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // User Prayer Times
        Schema::create('user_prayer_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Prayer preferences
            $table->boolean('observes_prayers')->default(false);
            $table->enum('calculation_method', [
                'MWL',     // Muslim World League
                'ISNA',    // Islamic Society of North America
                'Egypt',   // Egyptian General Authority
                'Makkah',  // Umm al-Qura, Makkah
                'Karachi', // University of Islamic Sciences, Karachi
                'Tehran',  // Institute of Geophysics, University of Tehran
                'Jafari'   // Shia Ithna Ashari
            ])->default('MWL');

            // Notification settings
            $table->boolean('notify_fajr')->default(false);
            $table->boolean('notify_dhuhr')->default(false);
            $table->boolean('notify_asr')->default(false);
            $table->boolean('notify_maghrib')->default(false);
            $table->boolean('notify_isha')->default(false);

            // Reminder timing (minutes before prayer)
            $table->integer('reminder_minutes')->default(10);

            // Location for calculation
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone', 50)->nullable();

            $table->timestamps();
        });

        // Prayer Time Cache
        Schema::create('prayer_time_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');

            // Prayer times
            $table->time('fajr');
            $table->time('sunrise');
            $table->time('dhuhr');
            $table->time('asr');
            $table->time('maghrib');
            $table->time('isha');

            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prayer_time_cache');
        Schema::dropIfExists('user_prayer_times');
    }
};
```

## 5. Data Export Requests Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_export_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Request details
            $table->enum('format', ['json', 'csv', 'pdf'])->default('json');
            $table->jsonb('data_types')->nullable(); // ['profile', 'messages', 'matches', etc.]

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'expired'
            ])->default('pending');

            // File details
            $table->string('file_url')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->timestamp('expires_at')->nullable();

            // Processing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_export_requests');
    }
};
```

## 6. Laravel System Tables Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cache table
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        // Cache locks
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Jobs table
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        // Job batches
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // Failed jobs
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
```

## Migration Dependencies

These tables should be created in this order:

1. AI compatibility tables (no dependencies)
2. Verification requests → User verified badges
3. Family members → Family approvals
4. User prayer times → Prayer time cache
5. Data export requests (depends on users)
6. Laravel system tables (no dependencies)
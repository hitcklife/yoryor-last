# Safety and Moderation System - Consolidated Migrations

This document contains consolidated migration files for safety, moderation, and reporting features.

## 1. User Blocks Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();

            // Block participants
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade');

            // Block details
            $table->string('reason')->nullable();

            $table->timestamps();

            // Ensure unique blocks
            $table->unique(['blocker_id', 'blocked_id']);

            // Indexes for performance
            $table->index(['blocker_id', 'created_at']);
            $table->index(['blocked_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};
```

## 2. User Reports Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            // Report participants
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_id')->constrained('users')->onDelete('cascade');

            // Report details
            $table->string('reason');
            $table->text('description')->nullable();

            // Report status
            $table->enum('status', [
                'pending',
                'reviewing',
                'resolved',
                'dismissed'
            ])->default('pending');

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes for moderation workflow
            $table->index(['reporter_id', 'created_at']);
            $table->index(['reported_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
```

## 3. Enhanced User Reports Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enhanced_user_reports', function (Blueprint $table) {
            $table->id();

            // Report participants
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade');

            // Report categorization
            $table->enum('category', [
                'fake_profile',
                'inappropriate_content',
                'harassment',
                'spam',
                'underage',
                'violence',
                'hate_speech',
                'scam',
                'other'
            ]);

            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');

            // Report details
            $table->text('description');
            $table->json('context_data')->nullable(); // Screenshots, message IDs, etc.

            // Report handling
            $table->enum('status', [
                'pending',
                'under_review',
                'action_taken',
                'dismissed',
                'escalated'
            ])->default('pending');

            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            // Actions taken
            $table->enum('action_taken', [
                'none',
                'warning',
                'content_removed',
                'temporary_ban',
                'permanent_ban',
                'account_deleted'
            ])->nullable();

            $table->timestamp('action_taken_at')->nullable();

            $table->timestamps();

            // Indexes for moderation dashboard
            $table->index(['status', 'severity', 'created_at']);
            $table->index(['reported_user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enhanced_user_reports');
    }
};
```

## 4. Report Evidence Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('enhanced_user_reports')->onDelete('cascade');

            // Evidence type and content
            $table->enum('type', ['screenshot', 'message', 'profile', 'photo', 'video', 'call_recording']);
            $table->string('file_url')->nullable();
            $table->json('content')->nullable(); // For text-based evidence
            $table->text('description')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['report_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_evidence');
    }
};
```

## 5. Emergency Contacts Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Contact information
            $table->string('name');
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->enum('relationship', [
                'parent',
                'sibling',
                'spouse',
                'friend',
                'other'
            ]);

            // Contact preferences
            $table->boolean('is_primary')->default(false);
            $table->boolean('can_receive_alerts')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emergency_contacts');
    }
};
```

## 6. User Emergency Contacts Table Migration (Enhanced)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Contact details
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->string('relationship', 50);

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_code', 6)->nullable();

            // Permissions
            $table->boolean('can_track_location')->default(false);
            $table->boolean('notify_on_panic')->default(true);
            $table->boolean('notify_on_date')->default(false);

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index(['user_id', 'is_verified']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_emergency_contacts');
    }
};
```

## 7. Panic Activations Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panic_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Activation details
            $table->enum('trigger_type', ['manual', 'shake', 'voice', 'timer']);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_address')->nullable();

            // Context
            $table->foreignId('date_user_id')->nullable()->constrained('users');
            $table->string('date_location')->nullable();
            $table->timestamp('date_started_at')->nullable();

            // Response
            $table->boolean('contacts_notified')->default(false);
            $table->boolean('authorities_notified')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index('resolved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panic_activations');
    }
};
```

## 8. User Safety Scores Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_safety_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Safety metrics
            $table->integer('overall_score')->default(100); // 0-100
            $table->integer('profile_completeness')->default(0);
            $table->integer('verification_level')->default(0);
            $table->integer('report_count')->default(0);
            $table->integer('block_count')->default(0);
            $table->integer('positive_interactions')->default(0);

            // Risk factors
            $table->boolean('has_suspicious_activity')->default(false);
            $table->boolean('rapid_location_changes')->default(false);
            $table->boolean('multiple_device_logins')->default(false);

            // Last calculations
            $table->timestamp('last_calculated_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('overall_score');
            $table->index('has_suspicious_activity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_safety_scores');
    }
};
```

## 9. User Feedback Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Feedback categorization
            $table->enum('type', ['bug', 'feature', 'complaint', 'suggestion', 'other']);
            $table->string('subject');
            $table->text('message');

            // Feedback status
            $table->enum('status', [
                'pending',
                'acknowledged',
                'in_progress',
                'resolved',
                'closed'
            ])->default('pending');

            // Response
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // App version, device info, etc.

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
```

## Migration Dependencies

These tables should be created in this order:

1. user_blocks
2. user_reports
3. enhanced_user_reports
4. report_evidence (depends on enhanced_user_reports)
5. emergency_contacts / user_emergency_contacts
6. panic_activations
7. user_safety_scores
8. user_feedback

## Safety System Features

This safety system provides:

1. **User Blocking**: Simple blocking mechanism
2. **Reporting System**: Multi-level reporting with evidence support
3. **Emergency Contacts**: Verified emergency contacts with permissions
4. **Panic Button**: Location-based panic activation system
5. **Safety Scoring**: Automated safety score calculation
6. **Feedback System**: User feedback and complaint management
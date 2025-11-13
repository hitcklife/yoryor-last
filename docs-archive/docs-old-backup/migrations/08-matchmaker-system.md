# Matchmaker Professional System - Consolidated Migrations

This document contains consolidated migration files for the professional matchmaker system.

## 1. Matchmakers Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Business information
            $table->string('business_name')->nullable();
            $table->text('bio');
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();

            // Expertise
            $table->jsonb('specializations')->nullable(); // ['age_groups', 'religions', 'professions']
            $table->jsonb('languages')->nullable();
            $table->integer('years_experience')->default(0);

            // Performance metrics
            $table->integer('successful_matches')->default(0);
            $table->integer('total_clients')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0); // Percentage
            $table->decimal('rating', 3, 2)->default(0); // 0-5 stars
            $table->integer('reviews_count')->default(0);

            // Verification
            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected'
            ])->default('pending');
            $table->timestamp('verified_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('verification_status');
            $table->index('is_active');
            $table->index(['is_active', 'rating']);
            $table->index('success_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmakers');
    }
};
```

## 2. Matchmaker Services Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');

            // Service details
            $table->string('name', 100);
            $table->text('description');
            $table->enum('type', [
                'basic',
                'premium',
                'vip',
                'custom'
            ]);

            // Pricing
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_period', [
                'one_time',
                'monthly',
                'quarterly',
                'yearly'
            ]);

            // Service scope
            $table->integer('duration_days');
            $table->integer('max_introductions');
            $table->jsonb('features')->nullable(); // ['profile_review', 'coaching', 'date_planning']

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'is_active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_services');
    }
};
```

## 3. Matchmaker Clients Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('matchmaker_services');

            // Contract details
            $table->date('contract_start');
            $table->date('contract_end');
            $table->enum('status', [
                'active',
                'paused',
                'completed',
                'terminated'
            ])->default('active');

            // Client preferences
            $table->jsonb('match_preferences')->nullable();
            $table->text('special_requirements')->nullable();

            // Progress tracking
            $table->integer('introductions_made')->default(0);
            $table->integer('successful_dates')->default(0);
            $table->boolean('found_match')->default(false);

            // Notes
            $table->text('matchmaker_notes')->nullable();
            $table->text('client_feedback')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('contract_end');
            $table->unique(['client_id', 'matchmaker_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_clients');
    }
};
```

## 4. Matchmaker Introductions Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_introductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('introduced_to_id')->constrained('users')->onDelete('cascade');

            // Introduction details
            $table->decimal('compatibility_score', 5, 2)->nullable(); // 0-100
            $table->text('introduction_message')->nullable();
            $table->jsonb('compatibility_reasons')->nullable();

            // Response tracking
            $table->enum('client_response', [
                'pending',
                'interested',
                'not_interested',
                'met'
            ])->default('pending');

            $table->enum('introduced_response', [
                'pending',
                'interested',
                'not_interested',
                'met'
            ])->default('pending');

            // Outcome
            $table->boolean('date_arranged')->default(false);
            $table->date('date_scheduled')->nullable();
            $table->enum('outcome', [
                'pending',
                'successful',
                'no_chemistry',
                'client_declined',
                'match_declined',
                'ongoing'
            ])->nullable();

            $table->text('feedback')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'created_at']);
            $table->index(['client_id', 'client_response']);
            $table->index(['introduced_to_id', 'introduced_response']);
            $table->index('date_arranged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_introductions');
    }
};
```

## 5. Matchmaker Reviews Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');

            // Review details
            $table->integer('rating'); // 1-5 stars
            $table->text('review');

            // Review categories
            $table->integer('communication_rating')->nullable();
            $table->integer('match_quality_rating')->nullable();
            $table->integer('professionalism_rating')->nullable();
            $table->integer('value_rating')->nullable();

            // Status
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_visible')->default(true);

            // Response
            $table->text('matchmaker_response')->nullable();
            $table->timestamp('response_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'is_visible']);
            $table->index(['client_id', 'created_at']);
            $table->index('rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_reviews');
    }
};
```

## 6. Matchmaker Availability Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');

            // Availability schedule
            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday'
            ]);

            $table->time('start_time');
            $table->time('end_time');

            // Timezone
            $table->string('timezone', 50)->default('UTC');

            // Status
            $table->boolean('is_available')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'day_of_week']);
            $table->unique(['matchmaker_id', 'day_of_week', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_availability');
    }
};
```

## 7. Matchmaker Consultations Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matchmaker_consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Consultation details
            $table->enum('type', ['initial', 'follow_up', 'coaching']);
            $table->datetime('scheduled_at');
            $table->integer('duration_minutes')->default(30);

            // Meeting details
            $table->enum('meeting_type', ['video', 'phone', 'in_person']);
            $table->string('meeting_link')->nullable();
            $table->string('meeting_location')->nullable();

            // Status
            $table->enum('status', [
                'scheduled',
                'confirmed',
                'completed',
                'cancelled',
                'no_show'
            ])->default('scheduled');

            // Notes
            $table->text('agenda')->nullable();
            $table->text('notes')->nullable();
            $table->text('action_items')->nullable();

            // Outcome
            $table->boolean('converted_to_client')->default(false);

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'scheduled_at']);
            $table->index(['user_id', 'status']);
            $table->index('scheduled_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matchmaker_consultations');
    }
};
```

## Features JSON Structures

### Specializations (matchmakers table)
```json
{
  "age_groups": ["25-35", "35-45", "45+"],
  "religions": ["muslim", "christian", "jewish"],
  "professions": ["medical", "tech", "business"],
  "communities": ["uzbek", "russian", "american"]
}
```

### Service Features (matchmaker_services table)
```json
{
  "profile_review": true,
  "photo_consultation": true,
  "personal_coaching": true,
  "date_planning": true,
  "background_checks": true,
  "personality_assessment": true,
  "exclusive_events": false,
  "concierge_service": false
}
```

### Match Preferences (matchmaker_clients table)
```json
{
  "age_range": {"min": 25, "max": 35},
  "height_preference": {"min": 165, "max": 185},
  "education_level": ["bachelors", "masters", "phd"],
  "religion": "muslim",
  "lifestyle": "modern",
  "location": ["new_york", "los_angeles"],
  "must_haves": ["family_oriented", "career_focused"],
  "deal_breakers": ["smoking", "previous_marriage"]
}
```

## Migration Dependencies

These tables should be created in this order:

1. matchmakers
2. matchmaker_services (depends on matchmakers)
3. matchmaker_clients (depends on matchmakers, users, matchmaker_services)
4. matchmaker_introductions (depends on matchmakers, users)
5. matchmaker_reviews (depends on matchmakers, users)
6. matchmaker_availability (depends on matchmakers)
7. matchmaker_consultations (depends on matchmakers, users)

After creating these tables, update the users table to add the foreign key constraint for `assigned_matchmaker_id`.
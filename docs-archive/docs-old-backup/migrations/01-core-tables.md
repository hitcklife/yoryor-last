# Core Database Tables - Consolidated Migrations

This document contains consolidated migration files for core tables, merging all modifications into single comprehensive migrations.

## 1. Users Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Authentication fields
            $table->string('email', 255)->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password', 60)->nullable(); // Nullable for social login
            $table->string('remember_token')->nullable();

            // Verification fields
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Social login fields
            $table->string('avatar')->nullable();
            $table->string('provider')->nullable();
            $table->string('google_id', 100)->nullable()->index();
            $table->string('facebook_id', 100)->nullable()->index();

            // User status fields
            $table->boolean('registration_completed')->default(false);
            $table->boolean('is_private')->default(false);
            $table->timestamp('disabled_at')->nullable()->index();

            // Activity tracking
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_currently_online')->default(false);
            $table->decimal('engagement_score', 8, 2)->default(0);
            $table->string('last_activity_type')->nullable();

            // Preferences
            $table->string('language_preference', 10)->default('en');

            // Matchmaker system
            $table->boolean('is_matchmaker')->default(false);
            $table->boolean('prefers_matchmaker')->default(false);
            $table->unsignedBigInteger('assigned_matchmaker_id')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for performance
            $table->index(['registration_completed', 'disabled_at', 'is_private'], 'users_active_profiles_index');
            $table->index(['last_active_at', 'registration_completed'], 'users_activity_index');
            $table->index(['is_currently_online', 'last_active_at'], 'users_online_status');
            $table->index(['engagement_score', 'is_currently_online'], 'users_engagement_online');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

## 2. Countries Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique();
            $table->string('flag')->nullable();
            $table->string('phone_code', 10);
            $table->string('phone_template')->nullable();

            // Localization fields
            $table->string('timezone')->nullable();
            $table->enum('time_format', ['12', '24'])->default('24');
            $table->json('unit_measurements')->nullable(); // {distance: 'km', temperature: 'celsius'}

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
```

## 3. Profiles Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Basic information
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->enum('gender', ['male', 'female', 'non-binary', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();

            // Location
            $table->string('city', 85)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('country_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Profile content
            $table->text('bio')->nullable();
            $table->json('interests')->nullable();
            $table->enum('looking_for_relationship', ['casual', 'serious', 'friendship', 'open'])
                  ->default('open');

            // Professional
            $table->string('occupation', 100)->nullable();
            $table->string('profession', 100)->nullable();
            $table->string('status', 50)->nullable();

            // Profile metrics
            $table->integer('profile_views')->default(0);
            $table->timestamp('profile_completed_at')->nullable();

            $table->timestamps();

            // Indexes for matching and search
            $table->index(['gender', 'age', 'city'], 'profiles_matching_index');
            $table->index(['latitude', 'longitude', 'gender'], 'profiles_location_index');
            $table->index(['looking_for_relationship', 'age'], 'profiles_intent_index');
            $table->index('profile_completed_at');
            $table->index('profile_views');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
```

## 4. OTP Codes Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['phone', 'used', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
```

## 5. Sessions Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
```

## 6. Password Reset Tokens Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};
```

## 7. Personal Access Tokens Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
```

## Foreign Key Constraints

After creating the matchmaker tables, add this migration to set up the foreign key:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('assigned_matchmaker_id')
                  ->references('id')
                  ->on('matchmakers')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_matchmaker_id']);
        });
    }
};
```
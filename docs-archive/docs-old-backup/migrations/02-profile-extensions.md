# Extended Profile Tables - Consolidated Migrations

This document contains consolidated migration files for all extended profile tables.

## 1. User Cultural Profiles Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_cultural_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Language preferences
            $table->json('native_languages')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->string('preferred_communication_language', 50)->nullable();

            // Religious preferences
            $table->enum('religion', [
                'muslim',
                'christian',
                'secular',
                'other',
                'prefer_not_to_say'
            ])->nullable();

            $table->enum('religiousness_level', [
                'very_religious',
                'moderately_religious',
                'not_religious',
                'prefer_not_to_say'
            ])->nullable();

            // Cultural identity
            $table->string('ethnicity', 100)->nullable();
            $table->string('uzbek_region', 100)->nullable();

            // Lifestyle preferences
            $table->enum('lifestyle_type', [
                'traditional',
                'modern',
                'mix_of_both'
            ])->nullable();

            $table->enum('gender_role_views', [
                'traditional',
                'modern',
                'flexible'
            ])->nullable();

            // Cultural practices
            $table->string('traditional_clothing_comfort')->nullable(); // Changed from boolean
            $table->enum('uzbek_cuisine_knowledge', [
                'expert',
                'good',
                'basic',
                'learning'
            ])->nullable();

            $table->enum('cultural_events_participation', [
                'very_active',
                'active',
                'sometimes',
                'rarely'
            ])->nullable();

            // Religious practices
            $table->boolean('halal_lifestyle')->nullable();
            $table->string('quran_reading')->nullable(); // Changed from boolean

            $table->timestamps();

            // Indexes for matching
            $table->index(['religion', 'religiousness_level']);
            $table->index(['lifestyle_type', 'gender_role_views']);
            $table->index('uzbek_region');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_cultural_profiles');
    }
};
```

## 2. User Family Preferences Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_family_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Family values
            $table->enum('family_importance', [
                'very_important',
                'important',
                'somewhat_important',
                'not_important'
            ])->nullable();

            // Children preferences
            $table->enum('wants_children', [
                'yes',
                'no',
                'maybe',
                'have_and_want_more',
                'have_and_dont_want_more'
            ])->nullable();

            $table->unsignedTinyInteger('number_of_children_wanted')->nullable();

            // Living arrangements
            $table->boolean('living_with_family')->nullable();
            $table->boolean('family_approval_important')->nullable();

            // Marriage timeline
            $table->enum('marriage_timeline', [
                'within_1_year',
                '1_2_years',
                '2_5_years',
                'someday',
                'never'
            ])->nullable();

            // Previous relationships
            $table->unsignedTinyInteger('previous_marriages')->default(0);

            // Work preferences
            $table->enum('homemaker_preference', [
                'yes',
                'no',
                'flexible',
                'both_work'
            ])->nullable();

            $table->timestamps();

            // Indexes
            $table->index('family_importance');
            $table->index('wants_children');
            $table->index('marriage_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_family_preferences');
    }
};
```

## 3. User Career Profiles Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_career_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Education
            $table->enum('education_level', [
                'high_school',
                'bachelors',
                'masters',
                'phd',
                'vocational',
                'other'
            ])->nullable();

            $table->string('university_name', 200)->nullable();

            // Financial information
            $table->enum('income_range', [
                'prefer_not_to_say',
                'under_25k',
                '25k_50k',
                '50k_75k',
                '75k_100k',
                '100k_plus'
            ])->nullable();

            $table->boolean('owns_property')->nullable();
            $table->text('financial_goals')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('education_level');
            $table->index('income_range');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_career_profiles');
    }
};
```

## 4. User Physical Profiles Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_physical_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Physical attributes
            $table->unsignedSmallInteger('height')->nullable(); // in centimeters
            $table->decimal('weight', 5, 2)->nullable(); // in kg

            // Fitness
            $table->enum('fitness_level', [
                'very_active',
                'active',
                'moderate',
                'sedentary'
            ])->nullable();

            // Health preferences
            $table->json('dietary_restrictions')->nullable();

            // Lifestyle habits
            $table->enum('smoking_status', [
                'never',
                'socially',
                'regularly',
                'trying_to_quit'
            ])->nullable();

            $table->enum('drinking_status', [
                'never',
                'socially',
                'regularly',
                'only_special_occasions'
            ])->nullable();

            $table->timestamps();

            // Indexes
            $table->index('height');
            $table->index('fitness_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_physical_profiles');
    }
};
```

## 5. User Location Preferences Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_location_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Immigration status
            $table->enum('immigration_status', [
                'citizen',
                'permanent_resident',
                'work_visa',
                'student',
                'other'
            ])->nullable();

            $table->unsignedTinyInteger('years_in_current_country')->nullable();

            // Future plans
            $table->enum('plans_to_return_uzbekistan', [
                'yes',
                'no',
                'maybe',
                'for_visits'
            ])->nullable();

            $table->enum('uzbekistan_visit_frequency', [
                'yearly',
                'every_few_years',
                'rarely',
                'never'
            ])->nullable();

            // Relocation preferences
            $table->boolean('willing_to_relocate')->nullable();
            $table->json('relocation_countries')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('immigration_status');
            $table->index('willing_to_relocate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_location_preferences');
    }
};
```

## 6. User Preferences Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Search preferences
            $table->unsignedSmallInteger('search_radius')->default(25);
            $table->string('country', 2)->nullable();
            $table->json('preferred_genders')->nullable();
            $table->json('hobbies_interests')->nullable();

            // Age preferences
            $table->unsignedTinyInteger('min_age')->nullable()->default(18);
            $table->unsignedTinyInteger('max_age')->nullable()->default(35);

            // Language preferences
            $table->json('languages_spoken')->nullable();

            // Matching preferences
            $table->json('deal_breakers')->nullable();
            $table->json('must_haves')->nullable();

            // Display preferences
            $table->enum('distance_unit', ['km', 'miles'])->default('km');
            $table->boolean('show_me_globally')->default(false);

            // Notification preferences
            $table->json('notification_preferences')->nullable();

            $table->timestamps();

            // Indexes for search
            $table->index(['search_radius', 'country']);
            $table->index(['min_age', 'max_age']);
            $table->index('show_me_globally');

            // Constraints
            $table->check('min_age >= 18');
            $table->check('max_age <= 120');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
```
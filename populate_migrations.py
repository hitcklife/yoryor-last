#!/usr/bin/env python3

import os
import re
from pathlib import Path

# Migration schemas
migrations = {
    # Core tables
    "01_core/.*create_otp_codes_table": '''        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->index();
            $table->string('code', 6);
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['phone', 'used', 'expires_at']);
        });''',

    "01_core/.*create_sessions_table": '''        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });''',

    "01_core/.*create_password_reset_tokens_table": '''        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });''',

    "01_core/.*create_personal_access_tokens_table": '''        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['tokenable_type', 'tokenable_id']);
        });''',

    # Profile extensions
    "02_profiles/.*create_user_cultural_profiles_table": '''        Schema::create('user_cultural_profiles', function (Blueprint $table) {
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
            $table->string('traditional_clothing_comfort')->nullable();
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
            $table->string('quran_reading')->nullable();

            $table->timestamps();

            // Indexes for matching
            $table->index(['religion', 'religiousness_level']);
            $table->index(['lifestyle_type', 'gender_role_views']);
            $table->index('uzbek_region');
        });''',

    "02_profiles/.*create_user_family_preferences_table": '''        Schema::create('user_family_preferences', function (Blueprint $table) {
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
        });''',

    "02_profiles/.*create_user_career_profiles_table": '''        Schema::create('user_career_profiles', function (Blueprint $table) {
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
        });''',

    "02_profiles/.*create_user_physical_profiles_table": '''        Schema::create('user_physical_profiles', function (Blueprint $table) {
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
        });''',

    "02_profiles/.*create_user_location_preferences_table": '''        Schema::create('user_location_preferences', function (Blueprint $table) {
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
        });''',

    "02_profiles/.*create_user_preferences_table": '''        Schema::create('user_preferences', function (Blueprint $table) {
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
        });''',
}

def update_migration_file(file_path, schema):
    """Update a migration file with the proper schema"""
    with open(file_path, 'r') as f:
        content = f.read()

    # Find the up() method and replace its Schema::create content
    pattern = r"(Schema::create\('[^']+', function \(Blueprint \$table\) \{)[^}]*(        \}\);)"
    replacement = schema

    # Use a simpler approach - replace between function (Blueprint $table) { and });
    pattern = r"Schema::create\([^)]+\)[^{]*\{[^}]*\}\);"
    new_content = re.sub(pattern, schema, content, count=1)

    with open(file_path, 'w') as f:
        f.write(new_content)

    print(f"✓ Updated: {file_path}")

def main():
    base_path = Path("/Users/khurshidjumaboev/Desktop/yoryor/yoryor-last/database/migrations")

    for pattern, schema in migrations.items():
        # Find matching files
        for migration_file in base_path.glob(pattern + ".php"):
            if migration_file.exists():
                update_migration_file(migration_file, schema)

    print("\n✅ All migrations have been populated!")

if __name__ == "__main__":
    main()
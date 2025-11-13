#!/usr/bin/env php
<?php

$migrations = [
    // Profile Extensions
    '/02_profiles/2025_09_24_211242_create_user_cultural_profiles_table.php' => <<<'PHP'
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
        });
PHP,

    '/02_profiles/2025_09_24_211243_create_user_family_preferences_table.php' => <<<'PHP'
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
PHP,

    '/02_profiles/2025_09_24_211243_create_user_career_profiles_table.php' => <<<'PHP'
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
PHP,

    '/02_profiles/2025_09_24_211243_create_user_physical_profiles_table.php' => <<<'PHP'
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
PHP,

    '/02_profiles/2025_09_24_211243_create_user_location_preferences_table.php' => <<<'PHP'
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
PHP,

    '/02_profiles/2025_09_24_211243_create_user_preferences_table.php' => <<<'PHP'
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
        });
PHP,

    // User Settings (comprehensive)
    '/05_settings/2025_09_24_211246_create_user_settings_table.php' => <<<'PHP'
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Account Management
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('marketing_emails_enabled')->default(false);

            // Notification Settings
            $table->boolean('notify_matches')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_likes')->default(true);
            $table->boolean('notify_super_likes')->default(true);
            $table->boolean('notify_visitors')->default(false);
            $table->boolean('notify_new_features')->default(true);
            $table->boolean('notify_marketing')->default(false);
            $table->boolean('push_notifications_enabled')->default(true);
            $table->boolean('in_app_sounds_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);
            $table->string('quiet_hours_start')->nullable();
            $table->string('quiet_hours_end')->nullable();

            // Privacy Settings
            $table->boolean('profile_visible')->default(true);
            $table->string('profile_visibility_level')->default('everyone');
            $table->boolean('show_online_status')->default(true);
            $table->boolean('show_distance')->default(true);
            $table->boolean('show_age')->default(true);
            $table->string('age_display_type')->default('exact');
            $table->boolean('show_last_active')->default(false);
            $table->boolean('allow_messages_from_matches')->default(true);
            $table->boolean('allow_messages_from_all')->default(false);
            $table->boolean('show_read_receipts')->default(true);
            $table->boolean('prevent_screenshots')->default(false);
            $table->boolean('hide_from_contacts')->default(false);
            $table->boolean('incognito_mode')->default(false);

            // Discovery Settings
            $table->boolean('show_me_on_discovery')->default(true);
            $table->boolean('global_mode')->default(false);
            $table->boolean('recently_active_only')->default(true);
            $table->boolean('verified_profiles_only')->default(false);
            $table->boolean('hide_already_seen_profiles')->default(true);
            $table->boolean('smart_photos')->default(true);
            $table->integer('min_age')->default(18);
            $table->integer('max_age')->default(35);
            $table->integer('max_distance')->default(25);
            $table->json('looking_for_preferences')->nullable();
            $table->json('interest_preferences')->nullable();

            // Data Privacy Settings
            $table->boolean('share_analytics_data')->default(true);
            $table->boolean('share_location_data')->default(true);
            $table->boolean('personalized_ads_enabled')->default(true);
            $table->boolean('data_for_improvements')->default(true);
            $table->boolean('share_with_partners')->default(false);

            // Security Settings
            $table->boolean('photo_verification_enabled')->default(false);
            $table->boolean('id_verification_enabled')->default(false);
            $table->boolean('phone_verification_enabled')->default(true);
            $table->boolean('social_media_verification_enabled')->default(false);
            $table->boolean('login_alerts_enabled')->default(true);
            $table->boolean('block_screenshots')->default(false);
            $table->boolean('hide_from_facebook')->default(true);

            // Appearance Settings
            $table->enum('theme_preference', ['light', 'dark', 'system'])->default('system');

            $table->timestamps();

            // Performance indexes
            $table->index('profile_visible');
            $table->index('show_me_on_discovery');
            $table->index('incognito_mode');
        });
PHP,

    // Subscription System
    '/06_subscription/2025_09_24_211247_create_subscription_plans_table.php' => <<<'PHP'
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            // Plan details
            $table->string('name', 50);
            $table->enum('tier', ['free', 'basic', 'gold', 'platinum']);

            // Plan limits
            $table->integer('swipes_per_day')->default(10);
            $table->integer('video_calls_per_month')->default(0);
            $table->integer('voice_calls_per_month')->default(0);
            $table->integer('max_call_duration_minutes')->default(0);

            // Features
            $table->json('features')->nullable();

            // Plan management
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('tier');
            $table->index('is_active');
            $table->index('sort_order');
        });
PHP,

    '/06_subscription/2025_09_24_211247_create_plan_pricing_table.php' => <<<'PHP'
        Schema::create('plan_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');

            // Pricing details
            $table->string('country_code', 2);
            $table->string('currency', 3);
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();

            $table->timestamps();

            // Unique constraint for one price per plan per country
            $table->unique(['plan_id', 'country_code']);

            // Indexes
            $table->index('country_code');
            $table->index(['plan_id', 'country_code']);
        });
PHP,

    '/06_subscription/2025_09_24_211247_create_user_subscriptions_table.php' => <<<'PHP'
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');

            // Payment provider
            $table->enum('payment_provider', ['stripe', 'payme', 'click', 'manual']);
            $table->string('provider_subscription_id')->nullable();

            // Subscription status
            $table->enum('status', [
                'active',
                'canceled',
                'expired',
                'past_due',
                'trialing'
            ])->default('active');

            // Billing periods
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');

            // Cancellation and trial
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('current_period_end');
            $table->index('provider_subscription_id');
        });
PHP,

    '/06_subscription/2025_09_24_211247_create_payment_transactions_table.php' => <<<'PHP'
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions');

            // Payment provider
            $table->enum('provider', ['stripe', 'payme', 'click']);
            $table->string('provider_transaction_id')->unique();

            // Transaction details
            $table->enum('type', ['subscription', 'one_time', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);

            // Transaction status
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'refunded'
            ]);

            // Additional data
            $table->json('provider_data')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('provider_transaction_id');
            $table->index('created_at');
        });
PHP,

    '/06_subscription/2025_09_24_211247_create_user_usage_limits_table.php' => <<<'PHP'
        Schema::create('user_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');

            // Daily usage tracking
            $table->integer('swipes_used')->default(0);
            $table->integer('likes_used')->default(0);
            $table->integer('video_calls_used')->default(0);
            $table->integer('voice_calls_used')->default(0);

            // Call minutes tracking
            $table->integer('video_minutes_used')->default(0);
            $table->integer('voice_minutes_used')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per day
            $table->unique(['user_id', 'date']);

            // Indexes
            $table->index('date');
            $table->index(['user_id', 'date']);
        });
PHP,

    '/06_subscription/2025_09_24_211248_create_user_monthly_usage_table.php' => <<<'PHP'
        Schema::create('user_monthly_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Period
            $table->integer('year');
            $table->integer('month');

            // Monthly totals
            $table->integer('video_calls_count')->default(0);
            $table->integer('voice_calls_count')->default(0);
            $table->integer('video_minutes_total')->default(0);
            $table->integer('voice_minutes_total')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per month
            $table->unique(['user_id', 'year', 'month']);

            // Indexes
            $table->index(['year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
PHP,

    // Safety System
    '/07_safety/2025_09_24_211248_create_user_blocks_table.php' => <<<'PHP'
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
PHP,

    '/07_safety/2025_09_24_211248_create_user_reports_table.php' => <<<'PHP'
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
PHP,

    '/07_safety/2025_09_24_211248_create_emergency_contacts_table.php' => <<<'PHP'
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
PHP,

    '/07_safety/2025_09_24_211248_create_user_feedback_table.php' => <<<'PHP'
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
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('status');
        });
PHP,

    // Matchmaker System
    '/08_matchmaker/2025_09_24_211249_create_matchmakers_table.php' => <<<'PHP'
        Schema::create('matchmakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Business information
            $table->string('business_name')->nullable();
            $table->text('bio');
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();

            // Expertise
            $table->json('specializations')->nullable();
            $table->json('languages')->nullable();
            $table->integer('years_experience')->default(0);

            // Performance metrics
            $table->integer('successful_matches')->default(0);
            $table->integer('total_clients')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
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
PHP,

    '/08_matchmaker/2025_09_24_211249_create_matchmaker_services_table.php' => <<<'PHP'
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
            $table->json('features')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'is_active']);
            $table->index('type');
        });
PHP,

    '/08_matchmaker/2025_09_24_211249_create_matchmaker_clients_table.php' => <<<'PHP'
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
            $table->json('match_preferences')->nullable();
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
        });
PHP,

    // Auth and Roles
    '/10_auth/2025_09_24_211249_create_roles_table.php' => <<<'PHP'
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();

            // Index for name lookups
            $table->index('name');
        });
PHP,

    '/10_auth/2025_09_24_211249_create_permissions_table.php' => <<<'PHP'
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();

            // Index for name lookups
            $table->index('name');
        });
PHP,

    '/10_auth/2025_09_24_211250_create_role_user_table.php' => <<<'PHP'
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Composite primary key
            $table->primary(['role_id', 'user_id']);

            // Indexes for reverse lookups
            $table->index('user_id');
            $table->index('role_id');
        });
PHP,

    '/10_auth/2025_09_24_211250_create_permission_role_table.php' => <<<'PHP'
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');

            // Composite primary key
            $table->primary(['permission_id', 'role_id']);

            // Indexes for reverse lookups
            $table->index('role_id');
            $table->index('permission_id');
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

echo "\n✨ All remaining migrations populated!\n";
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};

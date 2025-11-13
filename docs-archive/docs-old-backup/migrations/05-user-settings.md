# User Settings and Configuration - Consolidated Migrations

This document contains the comprehensive user settings table migration that consolidates all user preferences and settings.

## User Settings Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // ============================================
            // ACCOUNT MANAGEMENT SETTINGS
            // ============================================

            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('marketing_emails_enabled')->default(false);

            // ============================================
            // NOTIFICATION SETTINGS
            // ============================================

            // Notification Types
            $table->boolean('notify_matches')->default(true);
            $table->boolean('notify_messages')->default(true);
            $table->boolean('notify_likes')->default(true);
            $table->boolean('notify_super_likes')->default(true);
            $table->boolean('notify_visitors')->default(false);
            $table->boolean('notify_new_features')->default(true);
            $table->boolean('notify_marketing')->default(false);

            // Notification Delivery
            $table->boolean('push_notifications_enabled')->default(true);
            $table->boolean('in_app_sounds_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);

            // Quiet Hours
            $table->string('quiet_hours_start')->nullable();
            $table->string('quiet_hours_end')->nullable();

            // ============================================
            // PRIVACY SETTINGS
            // ============================================

            // Profile Visibility
            $table->boolean('profile_visible')->default(true);
            $table->string('profile_visibility_level')->default('everyone');
            $table->boolean('show_online_status')->default(true);
            $table->boolean('show_distance')->default(true);
            $table->boolean('show_age')->default(true);
            $table->string('age_display_type')->default('exact'); // 'exact' or 'range'
            $table->boolean('show_last_active')->default(false);

            // Messaging Privacy
            $table->boolean('allow_messages_from_matches')->default(true);
            $table->boolean('allow_messages_from_all')->default(false);
            $table->boolean('show_read_receipts')->default(true);

            // Advanced Privacy
            $table->boolean('prevent_screenshots')->default(false);
            $table->boolean('hide_from_contacts')->default(false);
            $table->boolean('incognito_mode')->default(false);

            // ============================================
            // DISCOVERY SETTINGS
            // ============================================

            // Visibility in Discovery
            $table->boolean('show_me_on_discovery')->default(true);
            $table->boolean('global_mode')->default(false);

            // Discovery Filters
            $table->boolean('recently_active_only')->default(true);
            $table->boolean('verified_profiles_only')->default(false);
            $table->boolean('hide_already_seen_profiles')->default(true);
            $table->boolean('smart_photos')->default(true);

            // Discovery Preferences
            $table->integer('min_age')->default(18);
            $table->integer('max_age')->default(35);
            $table->integer('max_distance')->default(25); // in km or miles
            $table->json('looking_for_preferences')->nullable();
            $table->json('interest_preferences')->nullable();

            // ============================================
            // DATA PRIVACY SETTINGS
            // ============================================

            $table->boolean('share_analytics_data')->default(true);
            $table->boolean('share_location_data')->default(true);
            $table->boolean('personalized_ads_enabled')->default(true);
            $table->boolean('data_for_improvements')->default(true);
            $table->boolean('share_with_partners')->default(false);

            // ============================================
            // SECURITY SETTINGS
            // ============================================

            // Verification Options
            $table->boolean('photo_verification_enabled')->default(false);
            $table->boolean('id_verification_enabled')->default(false);
            $table->boolean('phone_verification_enabled')->default(true);
            $table->boolean('social_media_verification_enabled')->default(false);

            // Security Alerts
            $table->boolean('login_alerts_enabled')->default(true);
            $table->boolean('block_screenshots')->default(false);
            $table->boolean('hide_from_facebook')->default(true);

            // ============================================
            // APPEARANCE SETTINGS
            // ============================================

            $table->enum('theme_preference', ['light', 'dark', 'system'])->default('system');

            $table->timestamps();

            // Performance indexes
            $table->index('profile_visible');
            $table->index('show_me_on_discovery');
            $table->index('incognito_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
```

## Settings Categories Breakdown

### 1. Account Management Settings
- Two-factor authentication
- Email notification preferences
- Marketing communications opt-in

### 2. Notification Settings
- **Types**: Matches, messages, likes, super likes, visitors, new features, marketing
- **Delivery**: Push notifications, in-app sounds, vibration
- **Quiet Hours**: Customizable do-not-disturb periods

### 3. Privacy Settings
- **Visibility**: Profile visibility, online status, distance, age display
- **Messaging**: Control who can message, read receipts
- **Advanced**: Screenshot prevention, hide from contacts, incognito mode

### 4. Discovery Settings
- **Visibility**: Show on discovery, global mode
- **Filters**: Recently active only, verified profiles, hide seen profiles
- **Preferences**: Age range, distance, looking for, interests

### 5. Data Privacy Settings
- Analytics data sharing
- Location data sharing
- Personalized ads
- Data for improvements
- Partner sharing

### 6. Security Settings
- **Verification**: Photo, ID, phone, social media verification
- **Alerts**: Login alerts, screenshot blocking
- **Privacy**: Hide from Facebook friends

### 7. Appearance Settings
- Theme preference (light/dark/system)

## Default Values

The migration sets sensible defaults that:
- Enable essential notifications (matches, messages, likes)
- Maintain privacy while allowing discovery
- Enable basic security features
- Follow privacy-conscious defaults for data sharing
- Use system theme by default

## Performance Considerations

Indexes are added for frequently queried settings:
- `profile_visible`: For filtering visible profiles
- `show_me_on_discovery`: For discovery algorithm
- `incognito_mode`: For special browsing mode

## Usage Notes

1. **Single Table Design**: All settings are in one table for simplicity and performance
2. **JSON Fields**: Used for complex preferences that may vary
3. **Boolean Defaults**: Most settings default to user-friendly values
4. **Extensibility**: Easy to add new settings as columns

## Related Tables

This settings table works in conjunction with:
- `user_preferences`: For matching preferences
- `user_cultural_profiles`: For cultural settings
- `user_family_preferences`: For family-related settings
- `user_location_preferences`: For location settings
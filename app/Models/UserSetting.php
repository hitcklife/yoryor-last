<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        // Account Management
        'two_factor_enabled',
        'email_notifications_enabled',
        'marketing_emails_enabled',

        // Notification Settings
        'notify_matches',
        'notify_messages',
        'notify_likes',
        'notify_super_likes',
        'notify_visitors',
        'notify_new_features',
        'notify_marketing',
        'push_notifications_enabled',
        'in_app_sounds_enabled',
        'vibration_enabled',
        'quiet_hours_start',
        'quiet_hours_end',

        // Privacy Settings
        'profile_visible',
        'profile_visibility_level',
        'show_online_status',
        'show_distance',
        'show_age',
        'age_display_type',
        'show_last_active',
        'allow_messages_from_matches',
        'allow_messages_from_all',
        'show_read_receipts',
        'prevent_screenshots',
        'hide_from_contacts',
        'incognito_mode',

        // Discovery Settings
        'show_me_on_discovery',
        'global_mode',
        'recently_active_only',
        'verified_profiles_only',
        'hide_already_seen_profiles',
        'smart_photos',
        'min_age',
        'max_age',
        'max_distance',
        'looking_for_preferences',
        'interest_preferences',

        // Data Privacy Settings
        'share_analytics_data',
        'share_location_data',
        'personalized_ads_enabled',
        'data_for_improvements',
        'share_with_partners',

        // Security Settings
        'photo_verification_enabled',
        'id_verification_enabled',
        'phone_verification_enabled',
        'social_media_verification_enabled',
        'login_alerts_enabled',
        'block_screenshots',
        'hide_from_facebook',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'two_factor_enabled' => 'boolean',
        'email_notifications_enabled' => 'boolean',
        'marketing_emails_enabled' => 'boolean',
        'notify_matches' => 'boolean',
        'notify_messages' => 'boolean',
        'notify_likes' => 'boolean',
        'notify_super_likes' => 'boolean',
        'notify_visitors' => 'boolean',
        'notify_new_features' => 'boolean',
        'notify_marketing' => 'boolean',
        'push_notifications_enabled' => 'boolean',
        'in_app_sounds_enabled' => 'boolean',
        'vibration_enabled' => 'boolean',
        'profile_visible' => 'boolean',
        'show_online_status' => 'boolean',
        'show_distance' => 'boolean',
        'show_age' => 'boolean',
        'show_last_active' => 'boolean',
        'allow_messages_from_matches' => 'boolean',
        'allow_messages_from_all' => 'boolean',
        'show_read_receipts' => 'boolean',
        'prevent_screenshots' => 'boolean',
        'hide_from_contacts' => 'boolean',
        'incognito_mode' => 'boolean',
        'show_me_on_discovery' => 'boolean',
        'global_mode' => 'boolean',
        'recently_active_only' => 'boolean',
        'verified_profiles_only' => 'boolean',
        'hide_already_seen_profiles' => 'boolean',
        'smart_photos' => 'boolean',
        'share_analytics_data' => 'boolean',
        'share_location_data' => 'boolean',
        'personalized_ads_enabled' => 'boolean',
        'data_for_improvements' => 'boolean',
        'share_with_partners' => 'boolean',
        'photo_verification_enabled' => 'boolean',
        'id_verification_enabled' => 'boolean',
        'phone_verification_enabled' => 'boolean',
        'social_media_verification_enabled' => 'boolean',
        'login_alerts_enabled' => 'boolean',
        'block_screenshots' => 'boolean',
        'hide_from_facebook' => 'boolean',
        'looking_for_preferences' => 'array',
        'interest_preferences' => 'array',
    ];

    /**
     * Get the user that owns the settings.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

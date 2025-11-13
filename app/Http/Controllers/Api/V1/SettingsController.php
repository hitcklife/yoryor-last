<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UserSetting;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }
    /**
     * Get all user settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_settings:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $settings = $user->settings;

                    if (!$settings) {
                        // Create default settings if none exist
                        $settings = UserSetting::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    return response()->json([
                        'status' => 'success',
                        'data' => $settings
                    ]);
                },
                ["user_{$user->id}_settings"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            // Account Management
            'two_factor_enabled' => 'sometimes|boolean',
            'email_notifications_enabled' => 'sometimes|boolean',
            'marketing_emails_enabled' => 'sometimes|boolean',

            // Notification Settings
            'notify_matches' => 'sometimes|boolean',
            'notify_messages' => 'sometimes|boolean',
            'notify_likes' => 'sometimes|boolean',
            'notify_super_likes' => 'sometimes|boolean',
            'notify_visitors' => 'sometimes|boolean',
            'notify_new_features' => 'sometimes|boolean',
            'notify_marketing' => 'sometimes|boolean',
            'push_notifications_enabled' => 'sometimes|boolean',
            'in_app_sounds_enabled' => 'sometimes|boolean',
            'vibration_enabled' => 'sometimes|boolean',
            'quiet_hours_start' => 'sometimes|nullable|string',
            'quiet_hours_end' => 'sometimes|nullable|string',

            // Privacy Settings
            'profile_visible' => 'sometimes|boolean',
            'profile_visibility_level' => 'sometimes|string',
            'show_online_status' => 'sometimes|boolean',
            'show_distance' => 'sometimes|boolean',
            'show_age' => 'sometimes|boolean',
            'age_display_type' => 'sometimes|string',
            'show_last_active' => 'sometimes|boolean',
            'allow_messages_from_matches' => 'sometimes|boolean',
            'allow_messages_from_all' => 'sometimes|boolean',
            'show_read_receipts' => 'sometimes|boolean',
            'prevent_screenshots' => 'sometimes|boolean',
            'hide_from_contacts' => 'sometimes|boolean',
            'incognito_mode' => 'sometimes|boolean',

            // Discovery Settings
            'show_me_on_discovery' => 'sometimes|boolean',
            'global_mode' => 'sometimes|boolean',
            'recently_active_only' => 'sometimes|boolean',
            'verified_profiles_only' => 'sometimes|boolean',
            'hide_already_seen_profiles' => 'sometimes|boolean',
            'smart_photos' => 'sometimes|boolean',
            'min_age' => 'sometimes|integer|min:18|max:100',
            'max_age' => 'sometimes|integer|min:18|max:100',
            'max_distance' => 'sometimes|integer|min:1',
            'looking_for_preferences' => 'sometimes|array',
            'interest_preferences' => 'sometimes|array',

            // Data Privacy Settings
            'share_analytics_data' => 'sometimes|boolean',
            'share_location_data' => 'sometimes|boolean',
            'personalized_ads_enabled' => 'sometimes|boolean',
            'data_for_improvements' => 'sometimes|boolean',
            'share_with_partners' => 'sometimes|boolean',

            // Security Settings
            'photo_verification_enabled' => 'sometimes|boolean',
            'id_verification_enabled' => 'sometimes|boolean',
            'phone_verification_enabled' => 'sometimes|boolean',
            'social_media_verification_enabled' => 'sometimes|boolean',
            'login_alerts_enabled' => 'sometimes|boolean',
            'block_screenshots' => 'sometimes|boolean',
            'hide_from_facebook' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $user->settings;
        if (!$settings) {
            $settings = new UserSetting(['user_id' => $user->id]);
        }

        // Update settings with validated data
        $settings->fill($validator->validated());
        $settings->save();

        // Clear user settings cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully',
            'data' => $settings
        ]);
    }

    /**
     * Get notification settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getNotificationSettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_notification_settings:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $settings = $user->settings;

                    if (!$settings) {
                        // Create default settings if none exist
                        $settings = UserSetting::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    // Extract only notification-related settings
                    $notificationSettings = [
                        'notify_matches' => $settings->notify_matches,
                        'notify_messages' => $settings->notify_messages,
                        'notify_likes' => $settings->notify_likes,
                        'notify_super_likes' => $settings->notify_super_likes,
                        'notify_visitors' => $settings->notify_visitors,
                        'notify_new_features' => $settings->notify_new_features,
                        'notify_marketing' => $settings->notify_marketing,
                        'push_notifications_enabled' => $settings->push_notifications_enabled,
                        'in_app_sounds_enabled' => $settings->in_app_sounds_enabled,
                        'vibration_enabled' => $settings->vibration_enabled,
                        'quiet_hours_start' => $settings->quiet_hours_start,
                        'quiet_hours_end' => $settings->quiet_hours_end,
                        'email_notifications_enabled' => $settings->email_notifications_enabled,
                        'marketing_emails_enabled' => $settings->marketing_emails_enabled,
                    ];

                    return response()->json([
                        'status' => 'success',
                        'data' => $notificationSettings
                    ]);
                },
                ["user_{$user->id}_settings"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get notification settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update notification settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'notify_matches' => 'sometimes|boolean',
            'notify_messages' => 'sometimes|boolean',
            'notify_likes' => 'sometimes|boolean',
            'notify_super_likes' => 'sometimes|boolean',
            'notify_visitors' => 'sometimes|boolean',
            'notify_new_features' => 'sometimes|boolean',
            'notify_marketing' => 'sometimes|boolean',
            'push_notifications_enabled' => 'sometimes|boolean',
            'in_app_sounds_enabled' => 'sometimes|boolean',
            'vibration_enabled' => 'sometimes|boolean',
            'quiet_hours_start' => 'sometimes|nullable|string',
            'quiet_hours_end' => 'sometimes|nullable|string',
            'email_notifications_enabled' => 'sometimes|boolean',
            'marketing_emails_enabled' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $user->settings;
        if (!$settings) {
            $settings = new UserSetting(['user_id' => $user->id]);
        }

        // Update settings with validated data
        $settings->fill($validator->validated());
        $settings->save();

        // Clear user settings cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification settings updated successfully',
            'data' => $validator->validated()
        ]);
    }

    /**
     * Get privacy settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPrivacySettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_privacy_settings:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $settings = $user->settings;

                    if (!$settings) {
                        // Create default settings if none exist
                        $settings = UserSetting::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    // Extract only privacy-related settings
                    $privacySettings = [
                        'profile_visible' => $settings->profile_visible,
                        'profile_visibility_level' => $settings->profile_visibility_level,
                        'show_online_status' => $settings->show_online_status,
                        'show_distance' => $settings->show_distance,
                        'show_age' => $settings->show_age,
                        'age_display_type' => $settings->age_display_type,
                        'show_last_active' => $settings->show_last_active,
                        'allow_messages_from_matches' => $settings->allow_messages_from_matches,
                        'allow_messages_from_all' => $settings->allow_messages_from_all,
                        'show_read_receipts' => $settings->show_read_receipts,
                        'prevent_screenshots' => $settings->prevent_screenshots,
                        'hide_from_contacts' => $settings->hide_from_contacts,
                        'incognito_mode' => $settings->incognito_mode,
                    ];

                    return response()->json([
                        'status' => 'success',
                        'data' => $privacySettings
                    ]);
                },
                ["user_{$user->id}_settings"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get privacy settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update privacy settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePrivacySettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'profile_visible' => 'sometimes|boolean',
            'profile_visibility_level' => 'sometimes|string',
            'show_online_status' => 'sometimes|boolean',
            'show_distance' => 'sometimes|boolean',
            'show_age' => 'sometimes|boolean',
            'age_display_type' => 'sometimes|string',
            'show_last_active' => 'sometimes|boolean',
            'allow_messages_from_matches' => 'sometimes|boolean',
            'allow_messages_from_all' => 'sometimes|boolean',
            'show_read_receipts' => 'sometimes|boolean',
            'prevent_screenshots' => 'sometimes|boolean',
            'hide_from_contacts' => 'sometimes|boolean',
            'incognito_mode' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $user->settings;
        if (!$settings) {
            $settings = new UserSetting(['user_id' => $user->id]);
        }

        // Update settings with validated data
        $settings->fill($validator->validated());
        $settings->save();

        // Clear user settings cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Privacy settings updated successfully',
            'data' => $validator->validated()
        ]);
    }

    /**
     * Get discovery settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDiscoverySettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_discovery_settings:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $settings = $user->settings;

                    if (!$settings) {
                        // Create default settings if none exist
                        $settings = UserSetting::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    // Extract only discovery-related settings
                    $discoverySettings = [
                        'show_me_on_discovery' => $settings->show_me_on_discovery,
                        'global_mode' => $settings->global_mode,
                        'recently_active_only' => $settings->recently_active_only,
                        'verified_profiles_only' => $settings->verified_profiles_only,
                        'hide_already_seen_profiles' => $settings->hide_already_seen_profiles,
                        'smart_photos' => $settings->smart_photos,
                        'min_age' => $settings->min_age,
                        'max_age' => $settings->max_age,
                        'max_distance' => $settings->max_distance,
                        'looking_for_preferences' => $settings->looking_for_preferences,
                        'interest_preferences' => $settings->interest_preferences,
                    ];

                    return response()->json([
                        'status' => 'success',
                        'data' => $discoverySettings
                    ]);
                },
                ["user_{$user->id}_settings"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get discovery settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update discovery settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDiscoverySettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'show_me_on_discovery' => 'sometimes|boolean',
            'global_mode' => 'sometimes|boolean',
            'recently_active_only' => 'sometimes|boolean',
            'verified_profiles_only' => 'sometimes|boolean',
            'hide_already_seen_profiles' => 'sometimes|boolean',
            'smart_photos' => 'sometimes|boolean',
            'min_age' => 'sometimes|integer|min:18|max:100',
            'max_age' => 'sometimes|integer|min:18|max:100',
            'max_distance' => 'sometimes|integer|min:1',
            'looking_for_preferences' => 'sometimes|array',
            'interest_preferences' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $user->settings;
        if (!$settings) {
            $settings = new UserSetting(['user_id' => $user->id]);
        }

        // Update settings with validated data
        $settings->fill($validator->validated());
        $settings->save();

        // Clear user settings cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Discovery settings updated successfully',
            'data' => $validator->validated()
        ]);
    }

    /**
     * Get security settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSecuritySettings(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            return $this->cacheService->remember(
                "user_security_settings:{$user->id}",
                CacheService::TTL_MEDIUM,
                function() use ($user) {
                    $settings = $user->settings;

                    if (!$settings) {
                        // Create default settings if none exist
                        $settings = UserSetting::create([
                            'user_id' => $user->id,
                        ]);
                    }

                    // Extract only security-related settings
                    $securitySettings = [
                        'two_factor_enabled' => $settings->two_factor_enabled,
                        'photo_verification_enabled' => $settings->photo_verification_enabled,
                        'id_verification_enabled' => $settings->id_verification_enabled,
                        'phone_verification_enabled' => $settings->phone_verification_enabled,
                        'social_media_verification_enabled' => $settings->social_media_verification_enabled,
                        'login_alerts_enabled' => $settings->login_alerts_enabled,
                        'block_screenshots' => $settings->block_screenshots,
                        'hide_from_facebook' => $settings->hide_from_facebook,
                    ];

                    return response()->json([
                        'status' => 'success',
                        'data' => $securitySettings
                    ]);
                },
                ["user_{$user->id}_settings"]
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get security settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update security settings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSecuritySettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'two_factor_enabled' => 'sometimes|boolean',
            'photo_verification_enabled' => 'sometimes|boolean',
            'id_verification_enabled' => 'sometimes|boolean',
            'phone_verification_enabled' => 'sometimes|boolean',
            'social_media_verification_enabled' => 'sometimes|boolean',
            'login_alerts_enabled' => 'sometimes|boolean',
            'block_screenshots' => 'sometimes|boolean',
            'hide_from_facebook' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Get or create settings
        $settings = $user->settings;
        if (!$settings) {
            $settings = new UserSetting(['user_id' => $user->id]);
        }

        // Update settings with validated data
        $settings->fill($validator->validated());
        $settings->save();

        // Clear user settings cache
        $this->cacheService->invalidateUserCaches($user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Security settings updated successfully',
            'data' => $validator->validated()
        ]);
    }
}

<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\Dislike;
use App\Models\Like;

class SettingsPage extends Component
{
    // Account Settings
    public $firstName = '';
    public $lastName = '';
    public $email = '';
    public $phone = '';
    public $language_preference = 'en';
    public $currentPassword = '';
    public $newPassword = '';
    public $confirmPassword = '';

    // Discovery Preferences
    public $showMe = 'all';
    public $ageMin = 18;
    public $ageMax = 35;
    public $distance = 25;
    public $location = '';
    public $onlyVerified = false;
    public $onlyWithPhotos = true;

    // Privacy Settings
    public $profileVisibility = 'everyone';
    public $showDistance = true;
    public $showAge = true;
    public $showOnlineStatus = true;
    public $showLastActive = false;
    public $allowMessages = 'matches';
    public $incognitoMode = false;
    public $ageDisplayType = 'exact';
    public $readReceipts = true;
    public $preventScreenshots = false;
    public $hideFromContacts = false;

    // Enhanced Notification Settings
    public $emailNotifications = true;
    public $pushNotifications = true;
    public $newMatches = true;
    public $newMessages = true;
    public $profileViews = false;
    public $likes = true;
    public $superLikes = true;
    public $promotions = false;
    public $inAppSounds = true;
    public $vibration = true;
    public $quietHoursEnabled = false;
    public $quietHoursStart = '22:00';
    public $quietHoursEnd = '08:00';
    
    // Enhanced Discovery Settings
    public $recentlyActiveOnly = true;
    public $smartPhotos = true;
    public $globalMode = false;
    
    // Data Privacy Settings
    public $shareAnalyticsData = true;
    public $shareLocationData = true;
    public $personalizedAds = true;
    public $dataForImprovements = true;
    
    // Security Settings
    public $twoFactorEnabled = false;
    public $loginAlerts = true;
    
    // Theme Settings
    public $themePreference = 'system';

    protected $rules = [
        'firstName' => 'required|string|max:50',
        'lastName' => 'required|string|max:50',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'language_preference' => 'required|in:en,uz,ru',
        'currentPassword' => 'nullable|string|min:8',
        'newPassword' => 'nullable|string|min:8|confirmed',
        'ageMin' => 'required|integer|min:18|max:100',
        'ageMax' => 'required|integer|min:18|max:100|gte:ageMin',
        'distance' => 'required|integer|min:1|max:500',
    ];

    public function mount()
    {
        $user = Auth::user();
        
        // Load current user data
        $this->firstName = $user->profile?->first_name ?? '';
        $this->lastName = $user->profile?->last_name ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->language_preference = $user->language_preference ?? 'en';
        $this->location = $user->profile?->city ?? '';

        // Load user settings
        $settings = $user->userSetting;
        
        if ($settings) {
            // Discovery preferences
            $this->showMe = $settings->show_me_gender ?? 'all';
            $this->ageMin = $settings->min_age ?? 18;
            $this->ageMax = $settings->max_age ?? 35;
            $this->distance = $settings->max_distance ?? 25;
            $this->onlyVerified = $settings->verified_profiles_only ?? false;
            $this->onlyWithPhotos = $settings->only_with_photos ?? true;
            $this->recentlyActiveOnly = $settings->recently_active_only ?? true;
            $this->smartPhotos = $settings->smart_photos ?? true;
            $this->globalMode = $settings->global_mode ?? false;

            // Privacy settings  
            $this->profileVisibility = $settings->profile_visibility_level ?? 'everyone';
            $this->showDistance = $settings->show_distance ?? true;
            $this->showAge = $settings->show_age ?? true;
            $this->showOnlineStatus = $settings->show_online_status ?? true;
            $this->showLastActive = $settings->show_last_active ?? false;
            $this->allowMessages = $settings->allow_messages_from_matches ? 'matches' : ($settings->allow_messages_from_all ? 'everyone' : 'nobody');
            $this->incognitoMode = $settings->incognito_mode ?? false;
            $this->ageDisplayType = $settings->age_display_type ?? 'exact';
            $this->readReceipts = $settings->show_read_receipts ?? true;
            $this->preventScreenshots = $settings->prevent_screenshots ?? false;
            $this->hideFromContacts = $settings->hide_from_contacts ?? false;

            // Enhanced notification settings
            $this->emailNotifications = $settings->email_notifications_enabled ?? true;
            $this->pushNotifications = $settings->push_notifications_enabled ?? true;
            $this->newMatches = $settings->notify_matches ?? true;
            $this->newMessages = $settings->notify_messages ?? true;
            $this->profileViews = $settings->notify_visitors ?? false;
            $this->likes = $settings->notify_likes ?? true;
            $this->superLikes = $settings->notify_super_likes ?? true;
            $this->promotions = $settings->notify_marketing ?? false;
            $this->inAppSounds = $settings->in_app_sounds_enabled ?? true;
            $this->vibration = $settings->vibration_enabled ?? true;
            $this->quietHoursEnabled = $settings->quiet_hours_enabled ?? false;
            $this->quietHoursStart = $settings->quiet_hours_start ?? '22:00';
            $this->quietHoursEnd = $settings->quiet_hours_end ?? '08:00';
            
            // Data privacy settings
            $this->shareAnalyticsData = $settings->share_analytics_data ?? true;
            $this->shareLocationData = $settings->share_location_data ?? true;
            $this->personalizedAds = $settings->personalized_ads_enabled ?? true;
            $this->dataForImprovements = $settings->data_for_improvements ?? true;
            
            // Security settings
            $this->twoFactorEnabled = $settings->two_factor_enabled ?? false;
            $this->loginAlerts = $settings->login_alerts_enabled ?? true;
            
            // Theme settings
            $this->themePreference = $settings->theme_preference ?? 'system';
        }
    }

    public function updateAccount()
    {
        $this->validate([
            'firstName' => 'required|string|max:50',
            'lastName' => 'required|string|max:50',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'phone' => 'nullable|string|max:20',
            'language_preference' => 'required|in:en,uz,ru',
        ]);

        $user = Auth::user();
        $user->update([
            'email' => $this->email,
            'phone' => $this->phone,
            'language_preference' => $this->language_preference,
        ]);

        // Update profile information
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
            ]
        );

        // Update session locale if changed
        if (session('locale') !== $this->language_preference) {
            session(['locale' => $this->language_preference]);
        }

        session()->flash('message', 'Account settings updated successfully!');
    }

    public function changePassword()
    {
        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->newPassword)
        ]);

        // Clear password fields
        $this->currentPassword = '';
        $this->newPassword = '';
        $this->confirmPassword = '';

        session()->flash('message', 'Password changed successfully!');
    }

    public function updateDiscoveryPreferences()
    {
        $this->validate([
            'ageMin' => 'required|integer|min:18|max:100',
            'ageMax' => 'required|integer|min:18|max:100|gte:ageMin',
            'distance' => 'required|integer|min:1|max:500',
        ]);

        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'show_me_gender' => $this->showMe,
                'min_age' => $this->ageMin,
                'max_age' => $this->ageMax,
                'max_distance' => $this->distance,
                'verified_profiles_only' => $this->onlyVerified,
                'recently_active_only' => $this->recentlyActiveOnly,
                'smart_photos' => $this->smartPhotos,
                'global_mode' => $this->globalMode,
            ]
        );

        session()->flash('message', 'Discovery preferences updated!');
    }

    public function updatePrivacySettings()
    {
        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'profile_visibility_level' => $this->profileVisibility,
                'show_distance' => $this->showDistance,
                'show_age' => $this->showAge,
                'show_online_status' => $this->showOnlineStatus,
                'show_last_active' => $this->showLastActive,
                'allow_messages_from_matches' => $this->allowMessages === 'matches',
                'allow_messages_from_all' => $this->allowMessages === 'everyone',
                'incognito_mode' => $this->incognitoMode,
                'age_display_type' => $this->ageDisplayType,
                'show_read_receipts' => $this->readReceipts,
                'prevent_screenshots' => $this->preventScreenshots,
                'hide_from_contacts' => $this->hideFromContacts,
            ]
        );

        session()->flash('message', 'Privacy settings updated!');
    }

    public function updateNotificationSettings()
    {
        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email_notifications_enabled' => $this->emailNotifications,
                'push_notifications_enabled' => $this->pushNotifications,
                'notify_matches' => $this->newMatches,
                'notify_messages' => $this->newMessages,
                'notify_visitors' => $this->profileViews,
                'notify_likes' => $this->likes,
                'notify_super_likes' => $this->superLikes,
                'notify_marketing' => $this->promotions,
                'in_app_sounds_enabled' => $this->inAppSounds,
                'vibration_enabled' => $this->vibration,
                'quiet_hours_enabled' => $this->quietHoursEnabled,
                'quiet_hours_start' => $this->quietHoursEnabled ? $this->quietHoursStart : null,
                'quiet_hours_end' => $this->quietHoursEnabled ? $this->quietHoursEnd : null,
            ]
        );

        session()->flash('message', 'Notification preferences updated!');
    }

    public function updateDataPrivacySettings()
    {
        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'share_analytics_data' => $this->shareAnalyticsData,
                'share_location_data' => $this->shareLocationData,
                'personalized_ads_enabled' => $this->personalizedAds,
                'data_for_improvements' => $this->dataForImprovements,
            ]
        );

        session()->flash('message', 'Data privacy settings updated!');
    }

    public function updateSecuritySettings()
    {
        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'two_factor_enabled' => $this->twoFactorEnabled,
                'login_alerts_enabled' => $this->loginAlerts,
            ]
        );

        session()->flash('message', 'Security settings updated!');
    }

    public function updateThemePreference()
    {
        $this->validate([
            'themePreference' => 'required|in:light,dark,system',
        ]);

        $user = Auth::user();
        
        $user->userSetting()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'theme_preference' => $this->themePreference,
            ]
        );

        // Update the HTML class based on theme preference
        if ($this->themePreference === 'dark') {
            $this->dispatch('theme-changed', theme: 'dark');
        } elseif ($this->themePreference === 'light') {
            $this->dispatch('theme-changed', theme: 'light');
        } else {
            // System theme - let the browser handle it
            $this->dispatch('theme-changed', theme: 'system');
        }

        session()->flash('message', 'Theme preference updated!');
    }

    public function startPhotoVerification()
    {
        // Implementation for starting photo verification process
        session()->flash('message', 'Photo verification process started. Please follow the instructions sent to your email.');
    }

    public function unblockUser($userId)
    {
        $user = Auth::user();
        
        // Remove the UserBlock record
        $user->blockedUsers()->where('blocked_id', $userId)->delete();
        
        session()->flash('message', 'User has been unblocked successfully.');
    }

    public function clearAllMatches()
    {
        $user = Auth::user();
        
        // Delete all likes given and received
        Like::where('user_id', $user->id)->orWhere('liked_user_id', $user->id)->delete();
        
        // Delete all dislikes given
        Dislike::where('user_id', $user->id)->delete();

        session()->flash('message', 'All matches and likes cleared!');
    }

    public function deleteAccount()
    {
        $user = Auth::user();
        
        // Soft delete the user account
        $user->update(['disabled_at' => now()]);
        
        // Log out the user
        Auth::logout();
        
        return redirect()->route('home');
    }

    public function exportData()
    {
        // Implementation for data export
        session()->flash('message', 'Data export initiated. You will receive an email shortly.');
    }

    public function restoreAccount()
    {
        $user = Auth::user();
        $user->update(['disabled_at' => null]);
        
        session()->flash('message', 'Account restored successfully!');
    }

    public function render()
    {
        // Load blocked users with necessary relationships
        $user = Auth::user();
        $user->load([
            'blockedUsers.blocked.profile',
            'blockedUsers.blocked.profilePhoto'
        ]);
        
        return view('livewire.pages.settings-page')->layout('layouts.app');
    }
}

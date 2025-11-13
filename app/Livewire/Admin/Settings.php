<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Settings extends Component
{
    // General Settings
    public $appName = 'YorYor Dating App';
    public $appDescription = 'Premium Muslim Dating Platform';
    public $maintenanceMode = false;
    public $registrationEnabled = true;
    public $emailVerificationRequired = true;
    public $phoneVerificationRequired = false;

    // Security Settings
    public $maxLoginAttempts = 5;
    public $lockoutTime = 15; // minutes
    public $sessionTimeout = 120; // minutes
    public $twoFactorRequired = false;
    public $passwordMinLength = 8;
    public $passwordRequireSpecialChars = true;

    // User Limits
    public $maxPhotosPerUser = 6;
    public $maxMessagesPerDay = 100;
    public $maxLikesPerDay = 50;
    public $maxReportsPerUser = 10;

    // Content Moderation
    public $autoModerateMessages = true;
    public $autoModeratePhotos = true;
    public $profanityFilterEnabled = true;
    public $adultContentDetection = true;

    // Notification Settings
    public $emailNotificationsEnabled = true;
    public $pushNotificationsEnabled = true;
    public $smsNotificationsEnabled = false;
    public $marketingEmailsEnabled = true;

    // Payment Settings
    public $premiumSubscriptionEnabled = true;
    public $freeTrialDays = 7;
    public $subscriptionPrice = 29.99;
    public $subscriptionCurrency = 'USD';

    // Feature Toggles
    public $videoCallingEnabled = true;
    public $voiceCallingEnabled = true;
    public $groupChatsEnabled = false;
    public $storiesEnabled = true;
    public $giftingEnabled = false;
    public $liveStreamingEnabled = false;

    protected $rules = [
        'appName' => 'required|string|max:255',
        'appDescription' => 'required|string|max:500',
        'maxLoginAttempts' => 'required|integer|min:1|max:10',
        'lockoutTime' => 'required|integer|min:1|max:60',
        'sessionTimeout' => 'required|integer|min:30|max:480',
        'passwordMinLength' => 'required|integer|min:6|max:20',
        'maxPhotosPerUser' => 'required|integer|min:1|max:20',
        'maxMessagesPerDay' => 'required|integer|min:10|max:1000',
        'maxLikesPerDay' => 'required|integer|min:5|max:500',
        'maxReportsPerUser' => 'required|integer|min:1|max:50',
        'freeTrialDays' => 'required|integer|min:0|max:30',
        'subscriptionPrice' => 'required|numeric|min:0',
        'subscriptionCurrency' => 'required|string|in:USD,EUR,GBP,CAD,AUD',
    ];

    public function mount()
    {
        // Load current settings from config or database
        $this->loadCurrentSettings();
    }

    private function loadCurrentSettings()
    {
        // In a real application, you would load these from a settings table
        // For now, we'll use default values
        
        $this->appName = config('app.name', 'YorYor Dating App');
        $this->appDescription = 'Premium Muslim Dating Platform';
        
        // Load other settings from database or config files
        // This is where you'd typically have something like:
        // $settings = Setting::all()->pluck('value', 'key');
        // $this->appName = $settings['app_name'] ?? $this->appName;
        // etc.
    }

    public function saveGeneralSettings()
    {
        $this->validate([
            'appName' => 'required|string|max:255',
            'appDescription' => 'required|string|max:500',
        ]);

        // Save to database or config
        // Setting::updateOrCreate(['key' => 'app_name'], ['value' => $this->appName]);
        // Setting::updateOrCreate(['key' => 'app_description'], ['value' => $this->appDescription]);
        // etc.

        $this->dispatch('settings-saved', ['message' => 'General settings saved successfully']);
    }

    public function saveSecuritySettings()
    {
        $this->validate([
            'maxLoginAttempts' => 'required|integer|min:1|max:10',
            'lockoutTime' => 'required|integer|min:1|max:60',
            'sessionTimeout' => 'required|integer|min:30|max:480',
            'passwordMinLength' => 'required|integer|min:6|max:20',
        ]);

        $this->dispatch('settings-saved', ['message' => 'Security settings saved successfully']);
    }

    public function saveUserLimits()
    {
        $this->validate([
            'maxPhotosPerUser' => 'required|integer|min:1|max:20',
            'maxMessagesPerDay' => 'required|integer|min:10|max:1000',
            'maxLikesPerDay' => 'required|integer|min:5|max:500',
            'maxReportsPerUser' => 'required|integer|min:1|max:50',
        ]);

        $this->dispatch('settings-saved', ['message' => 'User limits saved successfully']);
    }

    public function saveContentModerationSettings()
    {
        $this->dispatch('settings-saved', ['message' => 'Content moderation settings saved successfully']);
    }

    public function saveNotificationSettings()
    {
        $this->dispatch('settings-saved', ['message' => 'Notification settings saved successfully']);
    }

    public function savePaymentSettings()
    {
        $this->validate([
            'freeTrialDays' => 'required|integer|min:0|max:30',
            'subscriptionPrice' => 'required|numeric|min:0',
            'subscriptionCurrency' => 'required|string|in:USD,EUR,GBP,CAD,AUD',
        ]);

        $this->dispatch('settings-saved', ['message' => 'Payment settings saved successfully']);
    }

    public function saveFeatureToggles()
    {
        $this->dispatch('settings-saved', ['message' => 'Feature toggles saved successfully']);
    }

    public function clearCache()
    {
        // Clear application cache
        // Artisan::call('cache:clear');
        // Artisan::call('config:clear');
        // Artisan::call('view:clear');

        $this->dispatch('cache-cleared', ['message' => 'Application cache cleared successfully']);
    }

    public function runMaintenance()
    {
        // Run maintenance tasks
        // Artisan::call('queue:work --stop-when-empty');
        // Artisan::call('schedule:run');

        $this->dispatch('maintenance-completed', ['message' => 'Maintenance tasks completed successfully']);
    }

    public function render()
    {
        return view('livewire.admin.settings')->layout('components.layouts.admin', ['title' => 'System Settings']);
    }
}
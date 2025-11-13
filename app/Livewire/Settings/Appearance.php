<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Cookie;

class Appearance extends Component
{
    public $currentTheme = 'system';

    public function mount()
    {
        // Get theme from user settings first, then cookie, then default to 'system'
        if (auth()->check() && auth()->user()->userSetting?->theme_preference) {
            $this->currentTheme = auth()->user()->userSetting->theme_preference;
        } else {
            $this->currentTheme = request()->cookie('theme', 'system');
        }
    }

    public function setTheme($theme)
    {
        $this->currentTheme = $theme;
        
        // Store theme preference in cookie for 1 year
        Cookie::queue('theme', $theme, 525600);
        
        // Store in user settings if authenticated
        if (auth()->check()) {
            $userSetting = auth()->user()->userSetting;
            if (!$userSetting) {
                $userSetting = auth()->user()->userSetting()->create([]);
            }
            $userSetting->update(['theme_preference' => $theme]);
        }
        
        // Dispatch browser event to update theme
        $this->dispatch('theme-changed', theme: $theme);
        
        // Show success message
        session()->flash('success', 'Theme updated successfully!');
    }

    public function render()
    {
        return view('livewire.settings.appearance');
    }
}

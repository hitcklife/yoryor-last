<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageSwitcher extends Component
{
    public $currentLocale;
    public $availableLocales;
    
    public function mount()
    {
        $this->currentLocale = App::getLocale();
        $this->availableLocales = [
            'en' => [
                'name' => 'English',
                'flag' => '🇺🇸',
                'native' => 'English'
            ],
            'uz' => [
                'name' => 'Uzbek',
                'flag' => '🇺🇿',
                'native' => 'O\'zbekcha'
            ],
            'ru' => [
                'name' => 'Russian',
                'flag' => '🇷🇺',
                'native' => 'Русский'
            ]
        ];
    }
    
    public function switchLanguage($locale)
    {
        // Validate locale
        if (!in_array($locale, config('app.available_locales', ['en']))) {
            return;
        }
        
        // Update user preference if authenticated
        if (auth()->check()) {
            auth()->user()->update(['language_preference' => $locale]);
        }
        
        // Set cookie for guests
        cookie()->queue('locale', $locale, 525600); // 1 year
        
        // Store in session
        Session::put('locale', $locale);
        
        // Refresh the page to apply new locale
        return redirect()->to(request()->fullUrl());
    }
    
    public function render()
    {
        return view('livewire.components.language-switcher');
    }
}

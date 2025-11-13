<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Header extends Component
{
    public $currentLocale;
    public $mobileMenuOpen = false;
    public $languageDropdownOpen = false;
    
    public function mount()
    {
        $this->currentLocale = app()->getLocale();
    }
    
    public function switchLanguage($locale)
    {
        if (in_array($locale, ['en', 'uz', 'ru'])) {
            Session::put('locale', $locale);
            App::setLocale($locale);
            $this->currentLocale = $locale;
            
            // Redirect to current page with updated locale
            return redirect()->to(request()->fullUrl());
        }
    }
    
    public function toggleMobileMenu()
    {
        $this->mobileMenuOpen = !$this->mobileMenuOpen;
    }
    
    public function toggleLanguageDropdown()
    {
        $this->languageDropdownOpen = !$this->languageDropdownOpen;
    }
    
    public function getLanguages()
    {
        return [
            'en' => ['name' => 'English', 'flag' => '🇺🇸', 'code' => 'EN'],
            'uz' => ['name' => 'O\'zbekcha', 'flag' => '🇺🇿', 'code' => 'UZ'],
            'ru' => ['name' => 'Русский', 'flag' => '🇷🇺', 'code' => 'RU'],
        ];
    }
    
    public function render()
    {
        return view('livewire.components.header', [
            'languages' => $this->getLanguages(),
            'currentLanguage' => $this->getLanguages()[$this->currentLocale] ?? $this->getLanguages()['en']
        ]);
    }
}
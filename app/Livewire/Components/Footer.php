<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Footer extends Component
{
    public $newsletterEmail = '';
    public $newsletterStatus = '';
    public $newsletterMessage = '';
    public $downloadClicks = [];
    
    public function mount()
    {
        $this->downloadClicks = [
            'ios' => 0,
            'android' => 0
        ];
    }
    
    public function subscribeNewsletter()
    {
        $this->validate([
            'newsletterEmail' => 'required|email'
        ], [
            'newsletterEmail.required' => __('messages.email_required'),
            'newsletterEmail.email' => __('messages.email_invalid')
        ]);
        
        // Here you could integrate with your newsletter service (Mailchimp, ConvertKit, etc.)
        // For now, we'll simulate a successful subscription
        
        try {
            // Simulate API call delay
            sleep(1);
            
            $this->newsletterStatus = 'success';
            $this->newsletterMessage = __('messages.newsletter_success');
            $this->newsletterEmail = '';
            
            // Dispatch browser event for analytics
            $this->dispatch('newsletter-subscribed', ['email' => $this->newsletterEmail]);
            
        } catch (\Exception $e) {
            $this->newsletterStatus = 'error';
            $this->newsletterMessage = __('messages.newsletter_error');
        }
    }
    
    public function trackDownload($platform)
    {
        // Track download clicks
        $this->downloadClicks[$platform]++;
        
        // Dispatch analytics event
        $this->dispatch('app-download-clicked', [
            'platform' => $platform,
            'total_clicks' => $this->downloadClicks[$platform]
        ]);
        
        // You could also log to database or analytics service here
        \Log::info("App download clicked", [
            'platform' => $platform,
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip()
        ]);
    }
    
    public function getSocialLinks()
    {
        return [
            'twitter' => '#',
            'facebook' => '#', 
            'instagram' => '#',
            'linkedin' => '#'
        ];
    }
    
    public function render()
    {
        return view('livewire.components.footer', [
            'socialLinks' => $this->getSocialLinks(),
            'currentYear' => date('Y')
        ]);
    }
}
<?php

namespace App\Livewire;

use Livewire\Attributes\Validate;
use Livewire\Component;

class NewsletterSignup extends Component
{
    #[Validate('required|email|max:255')]
    public string $email = '';

    public bool $isSubmitted = false;
    public string $message = '';

    public function subscribe()
    {
        $this->validate();

        try {
            // Here you would typically save to database or send to email service
            // For demo purposes, we'll just simulate success

            $this->isSubmitted = true;
            $this->message = 'Thank you! You\'ll be notified when YorYor launches.';
            $this->email = '';

            // Auto-hide success message after 5 seconds
            $this->dispatch('hide-message')->delay(5000);

        } catch (\Exception $e) {
            $this->addError('email', 'Something went wrong. Please try again.');
        }
    }

    public function hideMessage()
    {
        $this->isSubmitted = false;
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.newsletter-signup');
    }
}

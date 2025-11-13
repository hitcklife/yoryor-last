<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;

class ComingSoon extends Component
{
    #[Validate('required|email')]
    public $email = '';
    
    public $showSuccess = false;

    public function subscribe()
    {
        $this->validate();
        
        // Here you would save the email to database or send to mailing service
        // For now just show success message
        
        $this->showSuccess = true;
        $this->email = '';
        
        // Hide success message after 3 seconds
        $this->dispatch('hide-success');
    }

    public function render()
    {
        return view('livewire.coming-soon')->layout('components.layouts.coming-soon');
    }
}

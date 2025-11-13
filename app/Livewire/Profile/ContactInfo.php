<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ContactInfo extends Component
{
    public $email = '';
    public $phone = '';
    public $hasEmail = false;
    public $hasPhone = false;
    public $isLoading = false;
    public $errorMessage = '';

    protected function rules()
    {
        $userId = Auth::id();
        
        return [
            'email' => 'nullable|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|min:10|unique:users,phone,' . $userId,
        ];
    }

    protected $messages = [
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'This email is already registered.',
        'phone.min' => 'Please enter a valid phone number.',
        'phone.unique' => 'This phone number is already registered.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // Check if basic info is completed
        if (!$user->profile?->first_name) {
            return redirect()->route('onboard.basic-info')
                ->with('error', 'Please complete your basic information first.');
        }

        // Check what contact info already exists
        $this->hasEmail = !empty($user->email);
        $this->hasPhone = !empty($user->phone);
        
        // Pre-populate with existing data if available
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        
        // If both email and phone exist, skip to next step
        if ($this->hasEmail && $this->hasPhone) {
            return redirect()->route('onboard.about-you');
        }
    }

    public function submit()
    {
        $this->validate();
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $user = Auth::user();
            
            // Update missing contact info
            $updates = [];
            
            // Only update email if it was missing and now provided
            if (!$this->hasEmail && !empty($this->email)) {
                $updates['email'] = $this->email;
            }
            
            // Only update phone if it was missing and now provided
            if (!$this->hasPhone && !empty($this->phone)) {
                $updates['phone'] = $this->phone;
            }
            
            if (!empty($updates)) {
                $user->update($updates);
            }
            
            // Redirect to next step (about you)
            return redirect()->route('onboard.about-you')
                ->with('success', 'Contact information saved! Tell us about yourself.');
                
        } catch (\Exception $e) {
            Log::error('Contact info update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your contact information. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function skip()
    {
        // Skip contact info and go to next step
        return redirect()->route('onboard.about-you')
            ->with('info', 'Contact information skipped.');
    }

    public function render()
    {
        return view('livewire.profile.contact-info')
            ->layout('components.layouts.onboarding', [
                'title' => 'Contact Information - YorYor',
                'currentStep' => 2,
                'totalSteps' => 8
            ]);
    }
}
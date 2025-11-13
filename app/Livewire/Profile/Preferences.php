<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Preferences extends Component
{
    public $lookingFor = '';
    public $bio = '';
    public $isLoading = false;
    public $errorMessage = '';

    protected $rules = [
        'lookingFor' => 'required|in:casual,serious,friendship,open',
        'bio' => 'nullable|string|min:20|max:500',
    ];

    protected $messages = [
        'lookingFor.required' => 'Please select what you are looking for.',
        'bio.min' => 'Bio must be at least 20 characters if provided.',
        'bio.max' => 'Bio must not exceed 500 characters.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // REMOVED validation check - just let user proceed

        // Pre-populate with existing data if available
        if ($user->profile) {
            $this->lookingFor = $user->profile->looking_for_relationship ?? '';
            $this->bio = $user->profile->bio ?? '';
        }
    }

    public function submit()
    {
        $this->validate();
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $user = Auth::user();
            
            // Update profile
            $user->profile->update([
                'looking_for_relationship' => $this->lookingFor,
                'bio' => $this->bio,
            ]);
            
            // Redirect to next step (interests)
            return redirect()->route('onboard.interests')
                ->with('success', 'Preferences saved! Now select your interests.');
                
        } catch (\Exception $e) {
            Log::error('Preferences update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your preferences. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedLookingFor()
    {
        // Trigger reactivity when looking for changes
    }

    public function updatedBio()
    {
        // Trigger reactivity when bio changes
    }

    public function getCanContinueProperty()
    {
        return !empty($this->lookingFor) && 
               (empty($this->bio) || strlen($this->bio) >= 20);
    }

    public function render()
    {
        return view('livewire.profile.preferences')
            ->layout('components.layouts.onboarding', [
                'title' => 'Your Preferences - YorYor',
                'currentStep' => 4,
                'totalSteps' => 8
            ]);
    }
}
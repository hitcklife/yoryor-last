<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AboutYou extends Component
{
    public $relationshipStatus = '';
    public $occupationType = '';
    public $profession = '';
    public $isLoading = false;
    public $errorMessage = '';

    protected $rules = [
        'relationshipStatus' => 'required|in:single,married,divorced,widowed,separated',
        'occupationType' => 'required|in:employee,student,business,unemployed',
        'profession' => 'nullable|string|max:100',
    ];

    protected $messages = [
        'relationshipStatus.required' => 'Please select your relationship status.',
        'occupationType.required' => 'Please select your occupation type.',
        'profession.max' => 'Profession must not exceed 100 characters.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // Pre-populate with existing data from PROFILE table if it exists
        if ($user->profile) {
            $this->relationshipStatus = $user->profile->status ?? '';
            $this->occupationType = $user->profile->occupation ?? '';
            $this->profession = $user->profile->profession ?? '';
        }
    }

    public function submit()
    {
        $this->validate();
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $user = Auth::user();
            
            // Debug log to see what values we're trying to save
            \Log::info('AboutYou form values', [
                'relationshipStatus' => $this->relationshipStatus,
                'occupationType' => $this->occupationType,
                'profession' => $this->profession,
            ]);
            
            // Update PROFILE table with EXISTING columns
            $user->profile->update([
                'status' => $this->relationshipStatus,  // Use existing 'status' column
                'occupation' => $this->occupationType,   // Use existing 'occupation' column  
                'profession' => $this->profession,      // This one already exists
            ]);
            
            // Redirect to next step (preferences)
            return redirect()->route('onboard.preferences')
                ->with('success', 'About you information saved! Tell us your preferences.');
                
        } catch (\Exception $e) {
            Log::error('About you update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your information. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedRelationshipStatus()
    {
        // Trigger reactivity when relationship status changes
    }

    public function updatedOccupationType()
    {
        // Trigger reactivity when occupation type changes
    }

    public function updatedProfession()
    {
        // Trigger reactivity when profession changes
    }

    public function getCanContinueProperty()
    {
        return !empty($this->relationshipStatus) && 
               !empty($this->occupationType);
    }

    public function render()
    {
        return view('livewire.profile.about-you')
            ->layout('components.layouts.onboarding', [
                'title' => 'About You - YorYor',
                'currentStep' => 3,
                'totalSteps' => 8
            ]);
    }
}
<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Details extends Component
{
    public $bio = '';
    public $interests = [];
    public $profession = '';
    public $city = '';
    public $isLoading = false;
    public $errorMessage = '';

    public $availableInterests = [
        'Travel', 'Photography', 'Music', 'Sports', 'Reading', 'Cooking',
        'Movies', 'Dancing', 'Art', 'Fitness', 'Nature', 'Technology',
        'Fashion', 'Food', 'Gaming', 'Hiking', 'Yoga', 'Shopping',
        'Learning', 'Business', 'Writing', 'Animals', 'Volunteering',
        'Family', 'Career', 'Adventure'
    ];

    protected $rules = [
        'bio' => 'nullable|string|max:500',
        'interests' => 'array|max:8',
        'interests.*' => 'string|max:50',
        'profession' => 'nullable|string|max:100',
        'city' => 'nullable|string|max:100',
    ];

    protected $messages = [
        'bio.max' => 'Bio must not exceed 500 characters.',
        'interests.max' => 'Please select maximum 8 interests.',
        'interests.*.max' => 'Each interest must not exceed 50 characters.',
        'profession.max' => 'Profession must not exceed 100 characters.',
        'city.max' => 'City must not exceed 100 characters.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user()->load(['profile', 'photos']);

        // Check if previous steps are completed
        if (!$user->profile?->first_name) {
            return redirect()->route('onboard.basic-info')
                ->with('error', 'Please complete your basic information first.');
        }

        if ($user->photos->count() < 2) {
            return redirect()->route('onboard.photos')
                ->with('error', 'Please add at least 2 photos first.');
        }

        // Pre-populate with existing data if available
        if ($user->profile) {
            $this->bio = $user->profile->bio ?? '';
            $this->profession = $user->profile->profession ?? '';
            $this->city = $user->profile->city ?? '';
            $this->interests = $this->parseJsonField($user->profile->interests);
        }
    }
    
    private function parseJsonField($field)
    {
        if (is_null($field)) {
            return [];
        }
        
        if (is_array($field)) {
            return $field;
        }
        
        if (is_string($field)) {
            $decoded = json_decode($field, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return [];
    }

    public function toggleInterest($interest)
    {
        if (in_array($interest, $this->interests)) {
            $this->interests = array_values(array_diff($this->interests, [$interest]));
        } else {
            if (count($this->interests) < 8) {
                $this->interests[] = $interest;
            }
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
                'bio' => $this->bio,
                'interests' => $this->interests,
                'profession' => $this->profession,
                'city' => $this->city,
                'profile_completed_at' => now(),
            ]);

            // Mark registration as completed
            $user->update([
                'registration_completed' => true,
                'profile_completed_at' => now(),
            ]);

            // Redirect to dashboard
            return redirect()->route('dashboard')
                ->with('success', 'Welcome to YorYor! Your profile is now complete.');
                
        } catch (\Exception $e) {
            Log::error('Profile details update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your profile. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function skipForNow()
    {
        try {
            $user = Auth::user();
            
            // Mark registration as completed even without details
            $user->update([
                'registration_completed' => true,
                'profile_completed_at' => now(),
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Welcome to YorYor! You can complete your profile anytime.');
                
        } catch (\Exception $e) {
            Log::error('Profile skip failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.profile.details')
            ->layout('components.layouts.onboarding', [
                'title' => 'Complete your profile - YorYor',
                'currentStep' => 9,
                'totalSteps' => 9
            ]);
    }
}
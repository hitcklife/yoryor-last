<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Interests extends Component
{
    public $selectedInterests = [];
    public $isLoading = false;
    public $errorMessage = '';

    public $availableInterests = [
        'gaming' => 'Gaming',
        'dancing' => 'Dancing', 
        'music' => 'Music',
        'movies' => 'Movies',
        'reading' => 'Reading',
        'sports' => 'Sports',
        'cooking' => 'Cooking',
        'travel' => 'Travel',
        'photography' => 'Photography',
        'art' => 'Art',
        'technology' => 'Technology',
        'fitness' => 'Fitness',
        'nature' => 'Nature',
        'nightlife' => 'Nightlife',
        'pets' => 'Pets',
        'volunteering' => 'Volunteering'
    ];

    protected function rules()
    {
        return [
            'selectedInterests' => 'required|array|min:1|max:8',
            'selectedInterests.*' => 'required|string|in:' . implode(',', array_keys($this->availableInterests)),
        ];
    }

    protected $messages = [
        'selectedInterests.required' => 'Please select at least one interest.',
        'selectedInterests.min' => 'Please select at least one interest.',
        'selectedInterests.max' => 'You can select up to 8 interests.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // REMOVED validation - just let user proceed
        
        // Pre-populate with existing data if available
        if ($user->profile && $user->profile->interests) {
            $this->selectedInterests = $this->parseJsonField($user->profile->interests);
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
        if (in_array($interest, $this->selectedInterests)) {
            $this->selectedInterests = array_values(array_filter($this->selectedInterests, function($item) use ($interest) {
                return $item !== $interest;
            }));
        } else {
            if (count($this->selectedInterests) < 8) {
                $this->selectedInterests[] = $interest;
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
                'interests' => json_encode($this->selectedInterests),
            ]);
            
            // Redirect to next step (photos)
            return redirect()->route('onboard.photos')
                ->with('success', 'Interests saved! Now add your photos.');
                
        } catch (\Exception $e) {
            Log::error('Interests update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your interests. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function getCanContinueProperty()
    {
        return count($this->selectedInterests) >= 1 && count($this->selectedInterests) <= 8;
    }

    public function render()
    {
        return view('livewire.profile.interests')
            ->layout('components.layouts.onboarding', [
                'title' => 'Your Interests - YorYor',
                'currentStep' => 5,
                'totalSteps' => 8
            ]);
    }
}
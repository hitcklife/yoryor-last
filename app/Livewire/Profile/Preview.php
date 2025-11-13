<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Preview extends Component
{
    public $user;
    public $profile;
    public $photos = [];
    public $isLoading = false;
    public $errorMessage = '';
    public $country = null;
    public $physicalProfile = null;
    public $careerProfile = null;
    public $culturalProfile = null;
    public $hasPrivatePhotos = false;

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $this->user = Auth::user();
        $this->profile = $this->user->profile;
        
        // Load country information
        if ($this->profile && $this->profile->country_id) {
            $this->country = Country::find($this->profile->country_id);
        }
        
        // Load additional profile information
        $this->physicalProfile = $this->user->physicalProfile ?? null;
        $this->careerProfile = $this->user->careerProfile ?? null;
        $this->culturalProfile = $this->user->culturalProfile ?? null;
        
        // Load photos
        $userPhotos = $this->user->photos()->orderBy('order')->get();
        $this->hasPrivatePhotos = $userPhotos->where('is_private', true)->count() > 0;
        
        $this->photos = $userPhotos->map(function ($photo) {
            return [
                'url' => $photo->original_url,
                'is_profile' => $photo->is_profile_photo,
                'is_private' => $photo->is_private
            ];
        })->toArray();
    }

    public function continueToDetails()
    {
        try {
            $user = Auth::user();
            
            // Mark profile as completed
            $user->profile->update([
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
            Log::error('Profile completion failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred. Please try again.';
        }
    }

    public function render()
    {
        return view('livewire.profile.preview')
            ->layout('components.layouts.onboarding', [
                'title' => 'Review Your Profile - YorYor',
                'currentStep' => 8,
                'totalSteps' => 8
            ]);
    }
}
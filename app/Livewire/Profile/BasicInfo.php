<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class BasicInfo extends Component
{
    public $gender = null;
    public $firstName = '';
    public $lastName = '';
    public $dateOfBirth = '';
    public $isLoading = false;
    public $errorMessage = '';

    protected $rules = [
        'gender' => 'required|in:male,female',
        'firstName' => 'required|string|min:2|max:50',
        'lastName' => 'required|string|min:2|max:50',
        'dateOfBirth' => 'required|date|before:18 years ago',
    ];

    protected $messages = [
        'gender.required' => 'Please select your gender.',
        'firstName.required' => 'First name is required.',
        'firstName.min' => 'First name must be at least 2 characters.',
        'lastName.required' => 'Last name is required.',
        'lastName.min' => 'Last name must be at least 2 characters.',
        'dateOfBirth.required' => 'Date of birth is required.',
        'dateOfBirth.before' => 'You must be at least 18 years old.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // If registration is already completed, redirect to dashboard
        if ($user->registration_completed && $user->profile?->first_name) {
            return redirect()->route('dashboard');
        }

        // Pre-populate with existing data if available
        if ($user->profile) {
            $this->firstName = $user->profile->first_name ?? '';
            $this->lastName = $user->profile->last_name ?? '';
            $this->dateOfBirth = $user->profile->date_of_birth ?? '';
            $this->gender = $user->profile->gender ?? '';
        }
    }

    public function submit()
    {
        $this->validate();
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            DB::beginTransaction();
            
            $user = Auth::user();
            
            // Calculate age from date of birth
            $age = null;
            if ($this->dateOfBirth) {
                $birthDate = new \DateTime($this->dateOfBirth);
                $today = new \DateTime('today');
                $age = $birthDate->diff($today)->y;
            }

            // Update or create profile - DON'T mark registration as completed yet
            if ($user->profile) {
                $user->profile->update([
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'date_of_birth' => $this->dateOfBirth,
                    'gender' => $this->gender,  // Gender goes in PROFILE, not user
                    'age' => $age,
                ]);
            } else {
                $user->profile()->create([
                    'user_id' => $user->id,
                    'first_name' => $this->firstName,
                    'last_name' => $this->lastName,
                    'date_of_birth' => $this->dateOfBirth,
                    'gender' => $this->gender,  // Gender goes in PROFILE, not user
                    'age' => $age,
                ]);
            }
            
            DB::commit();
            
            // Redirect to next step (contact info)
            return redirect()->route('onboard.contact-info')
                ->with('success', 'Basic information saved! Now add your contact details.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Profile basic info update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->errorMessage = 'Error: ' . $e->getMessage();
        } finally {
            $this->isLoading = false;
        }
    }

    public function updatedGender()
    {
        $this->dispatch('canContinueUpdated');
    }

    public function updatedFirstName()
    {
        $this->dispatch('canContinueUpdated');
    }

    public function updatedLastName()
    {
        $this->dispatch('canContinueUpdated');
    }

    public function updatedDateOfBirth()
    {
        $this->dispatch('canContinueUpdated');
    }

    public function getCanContinueProperty()
    {
        return !empty($this->gender) && 
               !empty(trim($this->firstName)) && 
               !empty(trim($this->lastName)) && 
               !empty($this->dateOfBirth);
    }

    public function render()
    {
        return view('livewire.profile.basic-info')
            ->layout('components.layouts.onboarding', [
                'title' => 'Basic Information - YorYor',
                'currentStep' => 1,
                'totalSteps' => 8
            ]);
    }
}
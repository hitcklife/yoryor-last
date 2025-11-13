<?php

namespace App\Livewire\Profile;

use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Location extends Component
{
    public $country = '';
    public $countryId = null;
    public $state = '';
    public $city = '';
    public $isLoading = false;
    public $errorMessage = '';
    public $detectedLocation = [];
    public $allCountries = [];

    protected $rules = [
        'countryId' => 'required|exists:countries,id',
        'state' => 'required|string|max:100',
        'city' => 'required|string|max:100',
    ];

    protected $messages = [
        'countryId.required' => 'Please select your country.',
        'countryId.exists' => 'Please select a valid country.',
        'state.required' => 'Please enter your state or region.',
        'city.required' => 'Please enter your city.',
    ];

    public function mount()
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('start');
        }

        $user = Auth::user();

        // Load all countries from database
        $this->allCountries = Country::orderBy('name')->get();

        // First priority: Try to detect location from IP for better accuracy
        $this->detectLocationFromIP();

        // Second priority: Pre-populate with existing data if available
        if ($user->profile) {
            // Only override IP detection if user has saved location data
            if ($user->profile->country_id && $user->profile->state && $user->profile->city) {
                $this->countryId = $user->profile->country_id;
                $country = Country::find($this->countryId);
                if ($country) {
                    $this->country = $country->name;
                }
                $this->state = $user->profile->state;
                $this->city = $user->profile->city;
            }
            
            // Fill in missing fields from profile if not detected
            if (empty($this->state) && $user->profile->state) {
                $this->state = $user->profile->state;
            }
            if (empty($this->city) && $user->profile->city) {
                $this->city = $user->profile->city;
            }
        }

        // Third priority: Use country from registration if still no country selected
        if (empty($this->countryId) && $user->country_id) {
            $this->countryId = $user->country_id;
            $country = Country::find($this->countryId);
            if ($country) {
                $this->country = $country->name;
            }
        }
    }

    public function detectLocationFromIP()
    {
        try {
            $response = Http::timeout(5)->get('https://ipapi.co/json/');
            
            if ($response->successful()) {
                $data = $response->json();
                $this->detectedLocation = $data;
                
                // Try to match country by code first (more accurate)
                if (!empty($data['country_code'])) {
                    $country = Country::where('code', $data['country_code'])->first();
                    
                    if ($country) {
                        $this->countryId = $country->id;
                        $this->country = $country->name;
                    } elseif (!empty($data['country_name'])) {
                        // Fallback to matching by name
                        $country = Country::where('name', 'LIKE', '%' . $data['country_name'] . '%')->first();
                        
                        if ($country) {
                            $this->countryId = $country->id;
                            $this->country = $country->name;
                        } else {
                            $this->country = $data['country_name'];
                        }
                    }
                }
                
                if (!empty($data['region'])) {
                    $this->state = $data['region'];
                }
                
                if (!empty($data['city'])) {
                    $this->city = $data['city'];
                }
                
                Log::info('Location detected from IP', [
                    'country' => $this->country,
                    'state' => $this->state,
                    'city' => $this->city,
                    'ip' => $data['ip'] ?? 'unknown'
                ]);
            }
        } catch (\Exception $e) {
            Log::info('Location detection failed', ['error' => $e->getMessage()]);
        }
    }

    public function updatedCountryId($value)
    {
        if ($value) {
            $country = Country::find($value);
            if ($country) {
                $this->country = $country->name;
                // Reset state when country changes
                $this->state = '';
                $this->city = '';
            }
        }
    }

    public function getStatesProperty()
    {
        // Return states based on selected country
        if ($this->country === 'United States') {
            return [
                'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California',
                'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia',
                'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
                'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland',
                'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri',
                'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
                'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
                'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina',
                'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont',
                'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming'
            ];
        } elseif ($this->country === 'Canada') {
            return [
                'Alberta', 'British Columbia', 'Manitoba', 'New Brunswick',
                'Newfoundland and Labrador', 'Northwest Territories', 'Nova Scotia',
                'Nunavut', 'Ontario', 'Prince Edward Island', 'Quebec', 'Saskatchewan', 'Yukon'
            ];
        } elseif ($this->country === 'United Kingdom') {
            return [
                'England', 'Scotland', 'Wales', 'Northern Ireland'
            ];
        } elseif ($this->country === 'Australia') {
            return [
                'New South Wales', 'Victoria', 'Queensland', 'Western Australia',
                'South Australia', 'Tasmania', 'Australian Capital Territory', 'Northern Territory'
            ];
        }
        // For other countries, return empty array so text input is shown
        return [];
    }

    public function submit()
    {
        $this->validate();
        
        $this->isLoading = true;
        $this->errorMessage = '';
        
        try {
            $user = Auth::user();
            
            // Update profile - only update fields that exist in the database
            $user->profile->update([
                'country_id' => $this->countryId,
                'state' => $this->state,
                'city' => $this->city,
            ]);

            // Also update user's country_id if not set
            if (!$user->country_id) {
                $user->update(['country_id' => $this->countryId]);
            }

            // Redirect to preview page
            return redirect()->route('onboard.preview')
                ->with('success', 'Location saved! Review your profile before completing.');
                
        } catch (\Exception $e) {
            Log::error('Location update failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            $this->errorMessage = 'An error occurred while saving your location. Please try again.';
        } finally {
            $this->isLoading = false;
        }
    }

    public function getCanContinueProperty()
    {
        return !empty($this->countryId) && !empty($this->state) && !empty($this->city);
    }

    public function render()
    {
        return view('livewire.profile.location')
            ->layout('components.layouts.onboarding', [
                'title' => 'Your Location - YorYor',
                'currentStep' => 7,
                'totalSteps' => 8
            ]);
    }
}
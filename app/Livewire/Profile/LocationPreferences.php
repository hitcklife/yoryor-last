<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserLocationPreference;

class LocationPreferences extends Component
{
    public $immigration_status;
    public $years_in_current_country;
    public $plans_to_return_uzbekistan;
    public $uzbekistan_visit_frequency;
    public $willing_to_relocate;
    public $relocation_countries = [];
    public $preferred_locations = [];
    public $live_with_family = false;
    public $future_location_plans;

    public function mount()
    {
        $user = Auth::user();
        $locationPreference = $user->locationPreference;

        if ($locationPreference) {
            $this->immigration_status = $locationPreference->immigration_status;
            $this->years_in_current_country = $locationPreference->years_in_current_country;
            $this->plans_to_return_uzbekistan = $locationPreference->plans_to_return_uzbekistan;
            $this->uzbekistan_visit_frequency = $locationPreference->uzbekistan_visit_frequency;
            $this->willing_to_relocate = $locationPreference->willing_to_relocate;
            $this->relocation_countries = $locationPreference->relocation_countries ?: [];
            $this->preferred_locations = $locationPreference->preferred_locations ?: [];
            $this->live_with_family = (bool) $locationPreference->live_with_family;
            $this->future_location_plans = $locationPreference->future_location_plans;
        }
    }

    public function toggleRelocationCountry($country)
    {
        if (in_array($country, $this->relocation_countries)) {
            $this->relocation_countries = array_values(array_filter($this->relocation_countries, function($c) use ($country) {
                return $c !== $country;
            }));
        } else {
            $this->relocation_countries[] = $country;
        }
    }

    public function togglePreferredLocation($location)
    {
        if (in_array($location, $this->preferred_locations)) {
            $this->preferred_locations = array_values(array_filter($this->preferred_locations, function($l) use ($location) {
                return $l !== $location;
            }));
        } else {
            $this->preferred_locations[] = $location;
        }
    }

    public function save()
    {
        $user = Auth::user();
        
        UserLocationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'immigration_status' => $this->immigration_status,
                'years_in_current_country' => $this->years_in_current_country,
                'plans_to_return_uzbekistan' => $this->plans_to_return_uzbekistan,
                'uzbekistan_visit_frequency' => $this->uzbekistan_visit_frequency,
                'willing_to_relocate' => $this->willing_to_relocate,
                'relocation_countries' => $this->relocation_countries,
                'preferred_locations' => $this->preferred_locations,
                'live_with_family' => $this->live_with_family,
                'future_location_plans' => $this->future_location_plans,
            ]
        );

        session()->flash('message', 'Location preferences saved successfully!');
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.profile.location-preferences')
            ->layout('components.layouts.user', ['title' => 'Location']);
    }
}

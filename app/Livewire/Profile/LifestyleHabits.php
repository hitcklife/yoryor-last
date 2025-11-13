<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPhysicalProfile;

class LifestyleHabits extends Component
{
    public $height;
    public $weight;
    public $smoking_habit;
    public $drinking_habit;
    public $exercise_frequency;
    public $diet_preference;
    public $pet_preference;
    public $hobbies = [];
    public $sleep_schedule;

    public function mount()
    {
        $user = Auth::user();
        $physicalProfile = $user->physicalProfile;

        if ($physicalProfile) {
            $this->height = $physicalProfile->height;
            $this->weight = $physicalProfile->weight;
            $this->smoking_habit = $physicalProfile->smoking_habit;
            $this->drinking_habit = $physicalProfile->drinking_habit;
            $this->exercise_frequency = $physicalProfile->exercise_frequency;
            $this->diet_preference = $physicalProfile->diet_preference;
            $this->pet_preference = $physicalProfile->pet_preference;
            $this->hobbies = $this->parseJsonField($physicalProfile->hobbies);
            $this->sleep_schedule = $physicalProfile->sleep_schedule;
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

    public function toggleHobby($hobby)
    {
        if (in_array($hobby, $this->hobbies)) {
            $this->hobbies = array_values(array_filter($this->hobbies, function($h) use ($hobby) {
                return $h !== $hobby;
            }));
        } else {
            $this->hobbies[] = $hobby;
        }
    }

    public function save()
    {
        $user = Auth::user();
        
        UserPhysicalProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'height' => $this->height,
                'weight' => $this->weight,
                'smoking_habit' => $this->smoking_habit,
                'drinking_habit' => $this->drinking_habit,
                'exercise_frequency' => $this->exercise_frequency,
                'diet_preference' => $this->diet_preference,
                'pet_preference' => $this->pet_preference,
                'hobbies' => json_encode($this->hobbies),
                'sleep_schedule' => $this->sleep_schedule,
            ]
        );

        session()->flash('message', 'Lifestyle preferences saved successfully!');
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.profile.lifestyle-habits')
            ->layout('components.layouts.user', ['title' => 'Lifestyle']);
    }
}

<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class EnhanceProfile extends Component
{
    public $completionData = [];
    public $overallCompletion = 0;

    public function mount()
    {
        $this->calculateCompletion();
    }

    public function calculateCompletion()
    {
        $user = Auth::user();
        $profile = $user->profile;
        $culturalProfile = $user->culturalProfile;
        $familyPreference = $user->familyPreference;
        $careerProfile = $user->careerProfile;
        $physicalProfile = $user->physicalProfile;
        $locationPreference = $user->locationPreference;

        // Cultural Background Section (20%)
        $culturalFields = [
            'ethnicity' => $culturalProfile?->ethnicity ?? null,
            'religion' => $culturalProfile?->religion ?? null,
            'native_languages' => $culturalProfile?->native_languages ?? null,
            'lifestyle_type' => $culturalProfile?->lifestyle_type ?? null,
            'uzbek_region' => $culturalProfile?->uzbek_region ?? null,
        ];
        $culturalCompleted = count(array_filter($culturalFields));
        $this->completionData['cultural'] = [
            'completed' => $culturalCompleted,
            'total' => count($culturalFields),
            'percentage' => ($culturalCompleted / count($culturalFields)) * 100,
            'title' => 'Cultural Background',
            'description' => 'Language, ethnicity, values',
            'icon' => '🏛️',
            'color' => 'purple'
        ];

        // Family & Marriage Section (20%) 
        $familyFields = [
            'marriage_intention' => $familyPreference?->marriage_intention ?? null,
            'children_preference' => $familyPreference?->children_preference ?? null,
            'family_involvement' => $familyPreference?->family_involvement ?? null,
            'marriage_timeline' => $familyPreference?->marriage_timeline ?? null,
            'family_importance' => $familyPreference?->family_importance ?? null,
        ];
        $familyCompleted = count(array_filter($familyFields));
        $this->completionData['family'] = [
            'completed' => $familyCompleted,
            'total' => count($familyFields),
            'percentage' => ($familyCompleted / count($familyFields)) * 100,
            'title' => 'Family & Marriage',
            'description' => 'Goals, children, values',
            'icon' => '💑',
            'color' => 'pink'
        ];

        // Career & Education Section (20%)
        $careerFields = [
            'education_level' => $careerProfile?->education_level ?? null,
            'field_of_study' => $careerProfile?->field_of_study ?? null,
            'work_status' => $careerProfile?->work_status ?? null,
            'occupation' => $careerProfile?->occupation ?? null,
            'career_goals' => $careerProfile?->career_goals ?? null,
        ];
        $careerCompleted = count(array_filter($careerFields));
        $this->completionData['career'] = [
            'completed' => $careerCompleted,
            'total' => count($careerFields),
            'percentage' => ($careerCompleted / count($careerFields)) * 100,
            'title' => 'Career & Education',
            'description' => 'Work, goals, achievements',
            'icon' => '🎓',
            'color' => 'blue'
        ];

        // Lifestyle Section (20%)
        $lifestyleFields = [
            'smoking_habit' => $physicalProfile?->smoking_habit ?? null,
            'drinking_habit' => $physicalProfile?->drinking_habit ?? null,
            'exercise_frequency' => $physicalProfile?->exercise_frequency ?? null,
            'diet_preference' => $physicalProfile?->diet_preference ?? null,
            'pet_preference' => $physicalProfile?->pet_preference ?? null,
        ];
        $lifestyleCompleted = count(array_filter($lifestyleFields));
        $this->completionData['lifestyle'] = [
            'completed' => $lifestyleCompleted,
            'total' => count($lifestyleFields),
            'percentage' => ($lifestyleCompleted / count($lifestyleFields)) * 100,
            'title' => 'Lifestyle',
            'description' => 'Habits, health, interests',
            'icon' => '🌟',
            'color' => 'green'
        ];

        // Location Section (20%)
        $locationFields = [
            'immigration_status' => $locationPreference?->immigration_status ?? null,
            'willing_to_relocate' => $locationPreference?->willing_to_relocate ?? null,
            'plans_to_return_uzbekistan' => $locationPreference?->plans_to_return_uzbekistan ?? null,
            'uzbekistan_visit_frequency' => $locationPreference?->uzbekistan_visit_frequency ?? null,
            'future_location_plans' => $locationPreference?->future_location_plans ?? null,
        ];
        $locationCompleted = count(array_filter($locationFields));
        $this->completionData['location'] = [
            'completed' => $locationCompleted,
            'total' => count($locationFields),
            'percentage' => ($locationCompleted / count($locationFields)) * 100,
            'title' => 'Location',
            'description' => 'Current, future plans',
            'icon' => '🗺️',
            'color' => 'indigo'
        ];

        // Calculate overall completion
        $totalCompleted = array_sum(array_column($this->completionData, 'completed'));
        $totalFields = array_sum(array_column($this->completionData, 'total'));
        $this->overallCompletion = $totalFields > 0 ? round(($totalCompleted / $totalFields) * 100) : 0;
    }

    public function render()
    {
        return view('livewire.profile.enhance-profile')
            ->layout('components.layouts.user', ['title' => 'Enhance Profile']);
    }
}

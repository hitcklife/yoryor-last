<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\UserPhoto;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ComprehensiveProfile extends Component
{
    public $user;
    public $profileData = [];
    public $photos = [];
    public $profileCompletion = 0;
    
    public function mount()
    {
        $this->user = Auth::user();
        $this->loadComprehensiveProfile();
        $this->calculateProfileCompletion();
    }
    
    public function loadComprehensiveProfile()
    {
        // Load user with all related data
        $this->user = $this->user->load([
            'profile',
            'culturalProfile',
            'physicalProfile', 
            'careerProfile',
            'familyPreference',
            'preference',
            'photos' => function($query) {
                $query->approved()->public()->ordered();
            }
        ]);
        
        // Build comprehensive profile data
        $this->buildProfileData();
        $this->loadPhotos();
    }
    
    public function buildProfileData()
    {
        $profile = $this->user->profile;
        $cultural = $this->user->culturalProfile;
        $physical = $this->user->physicalProfile;
        $career = $this->user->careerProfile;
        $family = $this->user->familyPreference;
        $preferences = $this->user->preference;
        
        $this->profileData = [
            // Basic Information
            'basic' => [
                'title' => 'Basic Information',
                'icon' => 'user',
                'data' => [
                    'Name' => $profile ? trim($profile->first_name . ' ' . $profile->last_name) : null,
                    'Age' => $profile?->age ? $profile->age . ' years old' : null,
                    'Gender' => $profile?->gender ? ucfirst($profile->gender) : null,
                    'Location' => $this->formatLocation($profile),
                    'Bio' => $profile?->bio,
                    'Looking for' => $profile?->looking_for_relationship ? ucfirst($profile->looking_for_relationship) : null,
                ]
            ],
            
            // Cultural & Religious
            'cultural' => [
                'title' => 'Cultural & Religious',
                'icon' => 'globe',
                'data' => [
                    'Religion' => $cultural?->religion ? ucfirst($cultural->religion) : null,
                    'Religiousness' => $cultural?->religiousness_level ? ucfirst($cultural->religiousness_level) : null,
                    'Ethnicity' => $cultural?->ethnicity ? ucfirst($cultural->ethnicity) : null,
                    'Languages' => $this->formatLanguages($cultural),
                    'Uzbek Region' => $cultural?->uzbek_region ? ucfirst($cultural->uzbek_region) : null,
                    'Lifestyle' => $cultural?->lifestyle_type ? ucfirst($cultural->lifestyle_type) : null,
                    'Halal Lifestyle' => $cultural?->halal_lifestyle ? 'Yes' : ($cultural?->halal_lifestyle === false ? 'No' : null),
                    'Observes Ramadan' => $cultural?->observes_ramadan ? 'Yes' : ($cultural?->observes_ramadan === false ? 'No' : null),
                    'Mosque Attendance' => $cultural?->mosque_attendance ? ucfirst($cultural->mosque_attendance) : null,
                ]
            ],
            
            // Physical & Lifestyle
            'physical' => [
                'title' => 'Physical & Lifestyle',
                'icon' => 'heart',
                'data' => [
                    'Height' => $physical?->height ? $physical->height . ' cm' : null,
                    'Weight' => $physical?->weight ? $physical->weight . ' kg' : null,
                    'Smoking' => $physical?->smoking_habit ? ucfirst($physical->smoking_habit) : null,
                    'Drinking' => $physical?->drinking_habit ? ucfirst($physical->drinking_habit) : null,
                    'Exercise' => $physical?->exercise_frequency ? ucfirst($physical->exercise_frequency) : null,
                    'Diet' => $physical?->diet_preference ? ucfirst($physical->diet_preference) : null,
                    'Pets' => $physical?->pet_preference ? ucfirst($physical->pet_preference) : null,
                    'Hobbies' => $this->formatHobbies($physical),
                    'Sleep Schedule' => $physical?->sleep_schedule ? ucfirst($physical->sleep_schedule) : null,
                ]
            ],
            
            // Career & Education
            'career' => [
                'title' => 'Career & Education',
                'icon' => 'briefcase',
                'data' => [
                    'Education' => $career?->education_level ? ucfirst($career->education_level) : null,
                    'Field of Study' => $career?->field_of_study ? ucfirst($career->field_of_study) : null,
                    'Work Status' => $career?->work_status ? ucfirst($career->work_status) : null,
                    'Occupation' => $career?->occupation ? ucfirst($career->occupation) : null,
                    'Employer' => $career?->employer ? ucfirst($career->employer) : null,
                    'Income Range' => $career?->income_range ? ucfirst($career->income_range) : null,
                    'Career Goals' => $this->formatCareerGoals($career),
                    'Owns Property' => $career?->owns_property ? 'Yes' : ($career?->owns_property === false ? 'No' : null),
                ]
            ],
            
            // Family & Relationships
            'family' => [
                'title' => 'Family & Relationships',
                'icon' => 'users',
                'data' => [
                    'Marriage Intention' => $family?->marriage_intention ? ucfirst($family->marriage_intention) : null,
                    'Children Preference' => $family?->children_preference ? ucfirst($family->children_preference) : null,
                    'Current Children' => $family?->current_children ? $family->current_children . ' children' : null,
                    'Family Values' => $this->formatFamilyValues($family),
                    'Living Situation' => $family?->living_situation ? ucfirst($family->living_situation) : null,
                    'Family Involvement' => $family?->family_involvement ? ucfirst($family->family_involvement) : null,
                    'Marriage Timeline' => $family?->marriage_timeline ? ucfirst($family->marriage_timeline) : null,
                    'Family Approval Important' => $family?->family_approval_important ? 'Yes' : ($family?->family_approval_important === false ? 'No' : null),
                    'Previous Marriages' => $family?->previous_marriages ? $family->previous_marriages . ' previous marriages' : null,
                ]
            ],
            
            // Preferences & Deal Breakers
            'preferences' => [
                'title' => 'Preferences & Deal Breakers',
                'icon' => 'star',
                'data' => [
                    'Search Radius' => $preferences?->search_radius ? $preferences->search_radius . ' km' : null,
                    'Age Range' => $this->formatAgeRange($preferences),
                    'Preferred Genders' => $this->formatPreferredGenders($preferences),
                    'Languages Spoken' => $this->formatLanguagesSpoken($preferences),
                    'Deal Breakers' => $this->formatDealBreakers($preferences),
                    'Must Haves' => $this->formatMustHaves($preferences),
                    'Global Mode' => $preferences?->show_me_globally ? 'Yes' : 'No',
                ]
            ]
        ];
    }
    
    public function loadPhotos()
    {
        $this->photos = $this->user->photos->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->medium_url ?: $photo->original_url,
                'thumbnail' => $photo->thumbnail_url,
                'is_profile' => $photo->is_profile_photo,
                'order' => $photo->order,
                'verified' => $photo->is_verified,
            ];
        })->toArray();
    }
    
    public function calculateProfileCompletion()
    {
        $totalFields = 0;
        $completedFields = 0;
        
        foreach ($this->profileData as $section) {
            foreach ($section['data'] as $field => $value) {
                $totalFields++;
                if (!empty($value)) {
                    $completedFields++;
                }
            }
        }
        
        // Add photos to completion calculation
        $totalFields += 6; // Up to 6 photos
        $completedFields += min(count($this->photos), 6);
        
        $this->profileCompletion = $totalFields > 0 ? round(($completedFields / $totalFields) * 100) : 0;
    }
    
    // Helper methods for formatting data
    private function formatLocation($profile)
    {
        if (!$profile) return null;
        
        $parts = array_filter([
            $profile->city,
            $profile->state,
            $profile->province,
            $profile->country?->name ?? $profile->country_code
        ]);
        
        return !empty($parts) ? implode(', ', $parts) : null;
    }
    
    private function formatLanguages($cultural)
    {
        if (!$cultural) return null;
        
        $languages = [];
        if ($cultural->native_languages) {
            $languages = array_merge($languages, $cultural->native_languages);
        }
        if ($cultural->spoken_languages) {
            $languages = array_merge($languages, $cultural->spoken_languages);
        }
        
        return !empty($languages) ? implode(', ', array_unique($languages)) : null;
    }
    
    private function formatHobbies($physical)
    {
        return $physical?->hobbies ? implode(', ', $physical->hobbies) : null;
    }
    
    private function formatCareerGoals($career)
    {
        return $career?->career_goals ? implode(', ', $career->career_goals) : null;
    }
    
    private function formatFamilyValues($family)
    {
        return $family?->family_values ? implode(', ', $family->family_values) : null;
    }
    
    private function formatAgeRange($preferences)
    {
        if (!$preferences || (!$preferences->min_age && !$preferences->max_age)) return null;
        
        $min = $preferences->min_age ?: '18';
        $max = $preferences->max_age ?: '99';
        
        return $min . ' - ' . $max . ' years';
    }
    
    private function formatPreferredGenders($preferences)
    {
        return $preferences?->preferred_genders ? implode(', ', $preferences->preferred_genders) : null;
    }
    
    private function formatLanguagesSpoken($preferences)
    {
        return $preferences?->languages_spoken ? implode(', ', $preferences->languages_spoken) : null;
    }
    
    private function formatDealBreakers($preferences)
    {
        return $preferences?->deal_breakers ? implode(', ', $preferences->deal_breakers) : null;
    }
    
    private function formatMustHaves($preferences)
    {
        return $preferences?->must_haves ? implode(', ', $preferences->must_haves) : null;
    }
    
    public function render()
    {
        return view('livewire.dashboard.comprehensive-profile');
    }
}

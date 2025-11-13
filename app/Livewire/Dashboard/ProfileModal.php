<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\MatchModel;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProfileModal extends Component
{
    public $showProfile = false;
    public $profile = null;
    public $currentPhotoIndex = 0;

    protected $listeners = [
        'show-profile-view' => 'showProfileView',
        'hide-profile-view' => 'hideProfileView'
    ];

    public function showProfileView($profileId)
    {
        $this->loadProfile($profileId);
        $this->showProfile = true;
    }

    public function hideProfileView()
    {
        $this->showProfile = false;
        $this->profile = null;
        $this->currentPhotoIndex = 0;
    }

    private function loadProfile($profileId)
    {
        $user = User::with([
            'profile:user_id,first_name,last_name,age,bio,occupation,city,state,country_code,gender,date_of_birth,interests,looking_for_relationship,profile_views,status',
            'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url',
            'photos:id,user_id,original_url,thumbnail_url,medium_url,order',
            'culturalProfile:user_id,native_languages,spoken_languages,preferred_communication_language,religion,religiousness_level,ethnicity,uzbek_region,lifestyle_type,gender_role_views,traditional_clothing_comfort,uzbek_cuisine_knowledge,cultural_events_participation,halal_lifestyle',
            'careerProfile:user_id,education_level,university_name,income_range,owns_property,financial_goals',
            'physicalProfile:user_id,height,weight,fitness_level,smoking_status,drinking_status,smoking_habit,drinking_habit,exercise_frequency,diet_preference,pet_preference,hobbies,sleep_schedule,diet',
            'familyPreference:user_id,marriage_intention,children_preference,current_children,family_values,living_situation,family_involvement,marriage_timeline,family_importance,family_approval_important,previous_marriages,homemaker_preference,number_of_children_wanted,living_with_family',
            'locationPreference:user_id,immigration_status,years_in_current_country,plans_to_return_uzbekistan,uzbekistan_visit_frequency,willing_to_relocate,relocation_countries',
            'preference:user_id,search_radius,preferred_genders,hobbies_interests,min_age,max_age,languages_spoken,deal_breakers,must_haves,show_me_globally'
        ])->find($profileId);

        if (!$user) return;

        // Use pre-computed age from database
        $age = $user->profile?->age;

        // Get all photos
        $photos = collect();
        if ($user->profilePhoto) {
            $photos->push([
                'id' => $user->profilePhoto->id,
                'url' => $user->profilePhoto->original_url,
                'thumbnail' => $user->profilePhoto->thumbnail_url
            ]);
        }
        $user->photos->each(function($photo) use ($photos) {
            $photos->push([
                'id' => $photo->id,
                'url' => $photo->original_url,
                'thumbnail' => $photo->thumbnail_url
            ]);
        });

        $this->profile = [
            // Basic User Information
            'id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone,
            'name' => $user->profile?->first_name ?? 'User',
            'full_name' => trim(($user->profile?->first_name ?? '') . ' ' . ($user->profile?->last_name ?? '')),
            'first_name' => $user->profile?->first_name,
            'last_name' => $user->profile?->last_name,
            'age' => $age,
            'gender' => $user->profile?->gender,
            'date_of_birth' => $user->profile?->date_of_birth,
            'bio' => $user->profile?->bio,
            'occupation' => $user->profile?->occupation,
            'photos' => $photos->toArray(),
            
            // Account Status
            'registration_completed' => $user->registration_completed,
            'is_active' => !$user->disabled_at,
            'is_private' => $user->is_private,
            'is_online' => $user->last_active_at && $user->last_active_at->gt(now()->subMinutes(5)),
            'last_active_at' => $user->last_active_at,
            'last_login_at' => $user->last_login_at ?? null,
            'email_verified' => $user->email_verified_at !== null,
            'phone_verified' => $user->phone_verified_at !== null,
            'verified' => $user->email_verified_at !== null,
            
            // Location Information
            'location' => $user->profile?->city ?? 'Unknown',
            'city' => $user->profile?->city,
            'state' => $user->profile?->state,
            'country_code' => $user->profile?->country_code,
            'profile_views' => $user->profile?->profile_views ?? 0,
            'status' => $user->profile?->status,
            
            // Profile Interests & Looking For
            'interests' => $user->profile?->interests ? (is_array($user->profile->interests) ? $user->profile->interests : json_decode($user->profile->interests, true)) : [],
            'looking_for' => $user->profile?->looking_for_relationship,
            
            // Cultural Information
            'religion' => $user->culturalProfile?->religion,
            'religiousness' => $user->culturalProfile?->religiousness_level,
            'ethnicity' => $user->culturalProfile?->ethnicity,
            'uzbek_region' => $user->culturalProfile?->uzbek_region,
            'lifestyle_type' => $user->culturalProfile?->lifestyle_type,
            'gender_role_views' => $user->culturalProfile?->gender_role_views,
            'traditional_clothing_comfort' => $user->culturalProfile?->traditional_clothing_comfort,
            'uzbek_cuisine_knowledge' => $user->culturalProfile?->uzbek_cuisine_knowledge,
            'cultural_events_participation' => $user->culturalProfile?->cultural_events_participation,
            'halal_lifestyle' => $user->culturalProfile?->halal_lifestyle,
            'native_languages' => $user->culturalProfile?->native_languages ?? [],
            'languages' => $user->culturalProfile?->spoken_languages ?? [],
            'preferred_communication_language' => $user->culturalProfile?->preferred_communication_language,
            
            // Career Information
            'education' => $user->careerProfile?->education_level,
            'university_name' => $user->careerProfile?->university_name,
            'income_range' => $user->careerProfile?->income_range,
            'owns_property' => $user->careerProfile?->owns_property,
            'financial_goals' => $user->careerProfile?->financial_goals,
            
            // Physical Information
            'height' => $user->physicalProfile?->height,
            'weight' => $user->physicalProfile?->weight,
            'fitness_level' => $user->physicalProfile?->fitness_level,
            'smoking_status' => $user->physicalProfile?->smoking_status,
            'drinking_status' => $user->physicalProfile?->drinking_status,
            'smoking_habit' => $user->physicalProfile?->smoking_habit,
            'drinking_habit' => $user->physicalProfile?->drinking_habit,
            'exercise_frequency' => $user->physicalProfile?->exercise_frequency,
            'diet_preference' => $user->physicalProfile?->diet_preference,
            'diet' => $user->physicalProfile?->diet,
            'pet_preference' => $user->physicalProfile?->pet_preference,
            'hobbies' => $user->physicalProfile?->hobbies ?? [],
            'sleep_schedule' => $user->physicalProfile?->sleep_schedule,
            
            // Family Information
            'marriage_intention' => $user->familyPreference?->marriage_intention,
            'children_preference' => $user->familyPreference?->children_preference,
            'current_children' => $user->familyPreference?->current_children,
            'family_values' => $user->familyPreference?->family_values,
            'living_situation' => $user->familyPreference?->living_situation,
            'family_involvement' => $user->familyPreference?->family_involvement,
            'family_importance' => $user->familyPreference?->family_importance,
            'family_approval_important' => $user->familyPreference?->family_approval_important,
            'marriage_timeline' => $user->familyPreference?->marriage_timeline,
            'previous_marriages' => $user->familyPreference?->previous_marriages,
            'homemaker_preference' => $user->familyPreference?->homemaker_preference,
            'number_of_children_wanted' => $user->familyPreference?->number_of_children_wanted,
            'living_with_family' => $user->familyPreference?->living_with_family,
            
            // Location Preferences
            'immigration_status' => $user->locationPreference?->immigration_status,
            'years_in_current_country' => $user->locationPreference?->years_in_current_country,
            'plans_to_return_uzbekistan' => $user->locationPreference?->plans_to_return_uzbekistan,
            'uzbekistan_visit_frequency' => $user->locationPreference?->uzbekistan_visit_frequency,
            'willing_to_relocate' => $user->locationPreference?->willing_to_relocate,
            'relocation_countries' => $user->locationPreference?->relocation_countries ?? [],
            
            // Dating Preferences
            'search_radius' => $user->preference?->search_radius,
            'preferred_genders' => $user->preference?->preferred_genders ?? [],
            'hobbies_interests' => $user->preference?->hobbies_interests ?? [],
            'min_age' => $user->preference?->min_age,
            'max_age' => $user->preference?->max_age,
            'languages_spoken' => $user->preference?->languages_spoken ?? [],
            'deal_breakers' => $user->preference?->deal_breakers ?? [],
            'must_haves' => $user->preference?->must_haves ?? [],
            'show_me_globally' => $user->preference?->show_me_globally,
        ];
    }

    public function nextPhoto()
    {
        if ($this->profile && count($this->profile['photos']) > 1) {
            $this->currentPhotoIndex = ($this->currentPhotoIndex + 1) % count($this->profile['photos']);
        }
    }

    public function previousPhoto()
    {
        if ($this->profile && count($this->profile['photos']) > 1) {
            $this->currentPhotoIndex = $this->currentPhotoIndex > 0 
                ? $this->currentPhotoIndex - 1 
                : count($this->profile['photos']) - 1;
        }
    }

    public function likeProfile()
    {
        if (!$this->profile) return;

        $currentUserId = Auth::id();
        $profileId = $this->profile['id'];
        
        // Create like record
        Like::create([
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId
        ]);
        
        // Check for mutual like (match)
        $mutualLike = Like::where('user_id', $profileId)
                         ->where('liked_user_id', $currentUserId)
                         ->exists();
        
        if ($mutualLike) {
            // Create match records
            MatchModel::create([
                'user_id' => $currentUserId,
                'matched_user_id' => $profileId,
                'created_at' => now()
            ]);
            
            MatchModel::create([
                'user_id' => $profileId,
                'matched_user_id' => $currentUserId,
                'created_at' => now()
            ]);
            
            // Emit match event
            $this->dispatch('match-found', profileId: $profileId);
        }
        
        $this->dispatch('profileActioned', profileId: $profileId);
        $this->hideProfileView();
        
        // Log the action
        Log::info('Profile liked from modal', [
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_match' => $mutualLike
        ]);
    }

    public function passProfile()
    {
        if (!$this->profile) return;

        $currentUserId = Auth::id();
        $profileId = $this->profile['id'];
        
        // Create dislike record
        Dislike::create([
            'user_id' => $currentUserId,
            'disliked_user_id' => $profileId
        ]);
        
        $this->dispatch('profileActioned', profileId: $profileId);
        $this->hideProfileView();
    }

    public function superLikeProfile()
    {
        if (!$this->profile) return;

        $currentUserId = Auth::id();
        $profileId = $this->profile['id'];
        
        // Create super like record
        Like::create([
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_super_like' => true
        ]);
        
        $this->dispatch('profileActioned', profileId: $profileId);
        $this->dispatch('super-like-sent', profileId: $profileId);
        $this->hideProfile();
    }

    public function render()
    {
        return view('livewire.dashboard.profile-modal');
    }
}
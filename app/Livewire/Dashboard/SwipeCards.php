<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\MatchModel;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SwipeCards extends Component
{
    public $potentialMatches = [];
    public $currentCardIndex = 0;
    public $currentUser = null;
    public $loading = true;
    public $noMoreProfiles = false;
    public $matchFound = false;
    public $matchedUser = null;
    
    public function mount()
    {
        $this->loadPotentialMatches();
        $this->setCurrentUser();
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
    
    public function loadPotentialMatches()
    {
        $user = Auth::user();
        $this->loading = true;
        
        // Get users not already liked, disliked, or matched
        $excludedUserIds = collect()
            ->merge(Like::where('user_id', $user->id)->pluck('liked_user_id'))
            ->merge(Dislike::where('user_id', $user->id)->pluck('disliked_user_id'))
            ->merge(MatchModel::where('user_id', $user->id)->pluck('matched_user_id'))
            ->merge(MatchModel::where('matched_user_id', $user->id)->pluck('user_id'))
            ->unique()
            ->values()
            ->toArray();
        
        $this->potentialMatches = User::with([
            'profile:user_id,first_name,last_name,date_of_birth,bio,occupation,city,country_code',
            'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url',
            'photos:id,user_id,original_url,thumbnail_url,medium_url,order',
            'culturalProfile:user_id,ethnicity,religion,religiousness_level,spoken_languages,traditional_clothing_comfort,halal_lifestyle,quran_reading,mosque_attendance',
            'familyPreference:user_id,marriage_timeline,children_preference,current_children,family_involvement,homemaker_preference,family_importance',
            'careerProfile:user_id,education_level,field_of_study,work_status,job_title,career_goals,income_range',
            'physicalProfile:user_id,height,weight,exercise_frequency,smoking_status,drinking_status',
            'locationPreference:user_id,willing_to_relocate,relocation_countries,plans_to_return_uzbekistan'
        ])
        ->where('id', '!=', $user->id)
        ->where('registration_completed', true)
        ->whereNull('disabled_at')
        ->whereNotIn('id', $excludedUserIds)
        ->whereHas('profile')
        ->whereHas('photos')
        ->inRandomOrder()
        ->take(10)
        ->get()
        ->map(function($potentialMatch) use ($user) {
            // Calculate age
            $age = $potentialMatch->profile?->date_of_birth 
                ? \Carbon\Carbon::parse($potentialMatch->profile->date_of_birth)->age 
                : null;
                
            // Calculate distance (mock for now)
            $distance = rand(1, 25);
            
            // Calculate profile completion
            $profileCompletion = 20; // Base profile
            $profileCompletion += $potentialMatch->culturalProfile ? 20 : 0;
            $profileCompletion += $potentialMatch->familyPreference ? 20 : 0;
            $profileCompletion += $potentialMatch->careerProfile ? 20 : 0;
            $profileCompletion += $potentialMatch->physicalProfile ? 20 : 0;
            
            return [
                'id' => $potentialMatch->id,
                'name' => $potentialMatch->profile?->first_name ?? $potentialMatch->name,
                'age' => $age,
                'bio' => $potentialMatch->profile?->bio,
                'occupation' => $potentialMatch->profile?->occupation,
                'location' => $potentialMatch->profile?->city ?? 'Unknown',
                'distance' => $distance,
                'profile_completion' => $profileCompletion,
                'photos' => $potentialMatch->photos->sortBy('order')->map(function($photo) {
                    return [
                        'id' => $photo->id,
                        'url' => $photo->medium_url,
                        'thumbnail' => $photo->thumbnail_url
                    ];
                })->values()->toArray(),
                'profile_photo' => $potentialMatch->profilePhoto ? [
                    'url' => $potentialMatch->profilePhoto->medium_url,
                    'thumbnail' => $potentialMatch->profilePhoto->thumbnail_url
                ] : null,
                'is_online' => $potentialMatch->last_active_at && $potentialMatch->last_active_at->gt(now()->subMinutes(5)),
                
                // Enhanced Profile Data
                'cultural' => $potentialMatch->culturalProfile ? [
                    'ethnicity' => $potentialMatch->culturalProfile->ethnicity,
                    'religion' => $potentialMatch->culturalProfile->religion,
                    'religious_practice' => $potentialMatch->culturalProfile->religiousness_level,
                    'languages' => $this->parseJsonField($potentialMatch->culturalProfile->spoken_languages),
                    'dietary_preferences' => $potentialMatch->culturalProfile->halal_lifestyle ? 'Halal' : null,
                ] : null,
                
                'family' => $potentialMatch->familyPreference ? [
                    'marriage_timeline' => $potentialMatch->familyPreference->marriage_timeline,
                    'children_preference' => $potentialMatch->familyPreference->children_preference,
                    'current_children' => $potentialMatch->familyPreference->current_children,
                    'family_importance' => $potentialMatch->familyPreference->family_importance,
                ] : null,
                
                'career' => $potentialMatch->careerProfile ? [
                    'education' => $potentialMatch->careerProfile->education_level,
                    'field' => $potentialMatch->careerProfile->field_of_study,
                    'work_status' => $potentialMatch->careerProfile->work_status,
                    'job_title' => $potentialMatch->careerProfile->job_title,
                    'goals' => $this->parseJsonField($potentialMatch->careerProfile->career_goals),
                ] : null,
                
                'lifestyle' => $potentialMatch->physicalProfile ? [
                    'height' => $potentialMatch->physicalProfile->height,
                    'weight' => $potentialMatch->physicalProfile->weight,
                    'exercise' => $potentialMatch->physicalProfile->exercise_frequency,
                    'smoking' => $potentialMatch->physicalProfile->smoking_status,
                    'drinking' => $potentialMatch->physicalProfile->drinking_status,
                ] : null,
                
                'location_preferences' => $potentialMatch->locationPreference ? [
                    'willing_to_relocate' => $potentialMatch->locationPreference->willing_to_relocate,
                    'relocation_countries' => $this->parseJsonField($potentialMatch->locationPreference->relocation_countries),
                ] : null
            ];
        })
        ->toArray();
        
        $this->loading = false;
        $this->noMoreProfiles = empty($this->potentialMatches);
    }
    
    public function setCurrentUser()
    {
        if (isset($this->potentialMatches[$this->currentCardIndex])) {
            $this->currentUser = $this->potentialMatches[$this->currentCardIndex];
        } else {
            $this->currentUser = null;
        }
    }
    
    public function likeUser()
    {
        if (!$this->currentUser) return;
        
        $currentUserId = Auth::id();
        $likedUserId = $this->currentUser['id'];
        
        // Create like record
        Like::create([
            'user_id' => $currentUserId,
            'liked_user_id' => $likedUserId
        ]);
        
        // Check if it's a mutual like (match)
        $mutualLike = Like::where('user_id', $likedUserId)
                         ->where('liked_user_id', $currentUserId)
                         ->exists();
        
        if ($mutualLike) {
            // Create match
            MatchModel::create([
                'user_id' => $currentUserId,
                'matched_user_id' => $likedUserId,
                'created_at' => now()
            ]);
            
            MatchModel::create([
                'user_id' => $likedUserId,
                'matched_user_id' => $currentUserId,
                'created_at' => now()
            ]);
            
            // Show match popup
            $this->matchFound = true;
            $this->matchedUser = $this->currentUser;
            
            Log::info('New match created', [
                'user_id' => $currentUserId,
                'matched_user_id' => $likedUserId
            ]);
        }
        
        $this->nextCard();
    }
    
    public function passUser()
    {
        if (!$this->currentUser) return;
        
        // Create dislike record
        Dislike::create([
            'user_id' => Auth::id(),
            'disliked_user_id' => $this->currentUser['id']
        ]);
        
        $this->nextCard();
    }
    
    public function superLikeUser()
    {
        // For now, treat super like same as regular like but with special flag
        if (!$this->currentUser) return;
        
        Like::create([
            'user_id' => Auth::id(),
            'liked_user_id' => $this->currentUser['id'],
            'is_super_like' => true
        ]);
        
        $this->nextCard();
    }
    
    public function nextCard()
    {
        $this->currentCardIndex++;
        
        if ($this->currentCardIndex >= count($this->potentialMatches)) {
            // Load more profiles
            $this->loadPotentialMatches();
            $this->currentCardIndex = 0;
        }
        
        $this->setCurrentUser();
    }
    
    public function closeMatchModal()
    {
        $this->matchFound = false;
        $this->matchedUser = null;
    }
    
    public function sendMessage()
    {
        if ($this->matchedUser) {
            return redirect()->route('messages', ['user' => $this->matchedUser['id']]);
        }
    }
    
    public function rewind()
    {
        // Implement rewind functionality (premium feature)
        if ($this->currentCardIndex > 0) {
            $this->currentCardIndex--;
            $this->setCurrentUser();
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.enhanced-swipe-cards');
    }
}

<?php

namespace App\Livewire\Pages;

use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\MatchModel;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiscoverPage extends Component
{
    public $currentUser = null;
    public $loading = false;
    public $hasMore = true;
    public $page = 1;
    
    // Filters
    public $ageMin = 18;
    public $ageMax = 50;
    public $maxDistance = 50;
    public $religion = '';
    public $education = '';
    public $profession = '';
    public $marriageIntention = '';
    public $childrenPreference = '';
    public $smokingHabit = '';
    public $drinkingHabit = '';
    public $showOnlineOnly = false;
    public $showVerifiedOnly = false;
    
    // Enhanced filters
    public $ethnicity = '';
    public $lifestyleType = '';
    public $workStatus = '';
    public $incomeRange = '';
    public $heightMin = '';
    public $heightMax = '';
    public $exerciseFrequency = '';
    public $dietPreference = '';
    public $marriageTimeline = '';
    public $familyImportance = '';
    public $religiousnessLevel = '';
    public $spokenLanguages = [];
    public $interests = [];
    public $sortBy = 'compatibility'; // compatibility, distance, activity, age
    public $filterPreset = '';
    
    protected $listeners = [
        'profileActioned' => 'loadNextProfile',
        'updateFilters' => 'applyFilters',
        'applyFilterPreset' => 'applyFilterPreset'
    ];
    
    public function mount()
    {
        $this->loadNextProfile();
    }
    
    public function loadNextProfile()
    {
        $this->loading = true;
        
        $user = Auth::user();
        
        if (!$user) {
            $this->currentUser = null;
            $this->loading = false;
            return;
        }
        
        // Get excluded user IDs (already liked, disliked, or matched)
        $excludedUserIds = collect()
            ->merge(Like::where('user_id', $user->id)->pluck('liked_user_id'))
            ->merge(Dislike::where('user_id', $user->id)->pluck('disliked_user_id'))
            ->merge(MatchModel::where('user_id', $user->id)->pluck('matched_user_id'))
            ->merge(MatchModel::where('matched_user_id', $user->id)->pluck('user_id'))
            ->push($user->id) // Exclude current user
            ->unique()
            ->values()
            ->toArray();
        
        // Build query with comprehensive filters
        $query = User::with([
            'profile:user_id,first_name,last_name,age,bio,occupation,city,country_code,looking_for_relationship',
            'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url',
            'photos:id,user_id,original_url,thumbnail_url,medium_url,order',
            'culturalProfile:user_id,ethnicity,religion,religiousness_level,spoken_languages,lifestyle_type',
            'careerProfile:user_id,education_level,field_of_study,work_status,job_title,income_range',
            'physicalProfile:user_id,height,weight,smoking_habit,drinking_habit,exercise_frequency,diet_preference',
            'familyPreference:user_id,marriage_intention,children_preference,marriage_timeline,family_importance'
        ])
        ->whereNotIn('id', $excludedUserIds)
        ->where('registration_completed', true)
        ->whereNull('disabled_at')
        ->whereHas('profile')
        ->whereHas('photos');
        
        // Apply age filter
        if ($this->ageMin || $this->ageMax) {
            $query->whereHas('profile', function($q) {
                if ($this->ageMin) {
                    $q->where('age', '>=', $this->ageMin);
                }
                if ($this->ageMax) {
                    $q->where('age', '<=', $this->ageMax);
                }
            });
        }
        
        // Apply profession filter
        if ($this->profession) {
            $query->whereHas('profile', function($q) {
                $q->where('occupation', 'like', '%' . $this->profession . '%');
            });
        }
        
        // Apply religion filter
        if ($this->religion) {
            $query->whereHas('culturalProfile', function($q) {
                $q->where('religion', $this->religion);
            });
        }
        
        // Apply education filter
        if ($this->education) {
            $query->whereHas('careerProfile', function($q) {
                $q->where('education_level', $this->education);
            });
        }
        
        // Apply marriage intention filter
        if ($this->marriageIntention) {
            $query->whereHas('familyPreference', function($q) {
                $q->where('marriage_intention', $this->marriageIntention);
            });
        }
        
        // Apply children preference filter
        if ($this->childrenPreference) {
            $query->whereHas('familyPreference', function($q) {
                $q->where('children_preference', $this->childrenPreference);
            });
        }
        
        // Apply smoking habit filter
        if ($this->smokingHabit) {
            $query->whereHas('physicalProfile', function($q) {
                $q->where('smoking_habit', $this->smokingHabit);
            });
        }
        
        // Apply drinking habit filter
        if ($this->drinkingHabit) {
            $query->whereHas('physicalProfile', function($q) {
                $q->where('drinking_habit', $this->drinkingHabit);
            });
        }
        
        // Apply online only filter
        if ($this->showOnlineOnly) {
            $query->where('last_active_at', '>=', now()->subMinutes(30));
        }
        
        // Apply verified only filter
        if ($this->showVerifiedOnly) {
            $query->whereNotNull('email_verified_at');
        }
        
        // Apply ethnicity filter
        if ($this->ethnicity) {
            $query->whereHas('culturalProfile', function($q) {
                $q->where('ethnicity', $this->ethnicity);
            });
        }
        
        // Apply lifestyle type filter
        if ($this->lifestyleType) {
            $query->whereHas('culturalProfile', function($q) {
                $q->where('lifestyle_type', $this->lifestyleType);
            });
        }
        
        // Apply work status filter
        if ($this->workStatus) {
            $query->whereHas('careerProfile', function($q) {
                $q->where('work_status', $this->workStatus);
            });
        }
        
        // Apply income range filter
        if ($this->incomeRange) {
            $query->whereHas('careerProfile', function($q) {
                $q->where('income_range', $this->incomeRange);
            });
        }
        
        // Apply height filter
        if ($this->heightMin || $this->heightMax) {
            $query->whereHas('physicalProfile', function($q) {
                if ($this->heightMin) {
                    $q->where('height', '>=', $this->heightMin);
                }
                if ($this->heightMax) {
                    $q->where('height', '<=', $this->heightMax);
                }
            });
        }
        
        // Apply exercise frequency filter
        if ($this->exerciseFrequency) {
            $query->whereHas('physicalProfile', function($q) {
                $q->where('exercise_frequency', $this->exerciseFrequency);
            });
        }
        
        // Apply diet preference filter
        if ($this->dietPreference) {
            $query->whereHas('physicalProfile', function($q) {
                $q->where('diet_preference', $this->dietPreference);
            });
        }
        
        // Apply marriage timeline filter
        if ($this->marriageTimeline) {
            $query->whereHas('familyPreference', function($q) {
                $q->where('marriage_timeline', $this->marriageTimeline);
            });
        }
        
        // Apply family importance filter
        if ($this->familyImportance) {
            $query->whereHas('familyPreference', function($q) {
                $q->where('family_importance', $this->familyImportance);
            });
        }
        
        // Apply religiousness level filter
        if ($this->religiousnessLevel) {
            $query->whereHas('culturalProfile', function($q) {
                $q->where('religiousness_level', $this->religiousnessLevel);
            });
        }
        
        // Apply sorting
        switch ($this->sortBy) {
            case 'distance':
                // For now, we'll use a mock distance calculation
                $query->orderBy('created_at', 'desc');
                break;
            case 'activity':
                $query->orderBy('last_active_at', 'desc');
                break;
            case 'age':
                $query->orderBy('created_at', 'asc'); // Assuming newer users are younger
                break;
            case 'compatibility':
            default:
                $query->orderBy('last_active_at', 'desc')
                      ->orderBy('created_at', 'desc');
                break;
        }
        
        $user = $query->first();
        
        if ($user) {
            $this->currentUser = $this->formatProfileData($user);
        } else {
            $this->currentUser = null;
            $this->hasMore = false;
        }
        
        $this->loading = false;
    }
    
    public function applyFilters()
    {
        $this->page = 1;
        $this->loadNextProfile();
    }
    
    public function resetFilters()
    {
        $this->ageMin = 18;
        $this->ageMax = 50;
        $this->maxDistance = 50;
        $this->religion = '';
        $this->education = '';
        $this->profession = '';
        $this->marriageIntention = '';
        $this->childrenPreference = '';
        $this->smokingHabit = '';
        $this->drinkingHabit = '';
        $this->showOnlineOnly = false;
        $this->showVerifiedOnly = false;
        $this->ethnicity = '';
        $this->lifestyleType = '';
        $this->workStatus = '';
        $this->incomeRange = '';
        $this->heightMin = '';
        $this->heightMax = '';
        $this->exerciseFrequency = '';
        $this->dietPreference = '';
        $this->marriageTimeline = '';
        $this->familyImportance = '';
        $this->religiousnessLevel = '';
        $this->spokenLanguages = [];
        $this->interests = [];
        $this->sortBy = 'compatibility';
        $this->filterPreset = '';
        $this->applyFilters();
    }
    
    public function applyFilterPreset($preset)
    {
        $this->resetFilters();
        
        switch ($preset) {
            case 'nearby':
                $this->maxDistance = 25;
                $this->showOnlineOnly = true;
                $this->sortBy = 'distance';
                break;
            case 'highly_compatible':
                $this->showVerifiedOnly = true;
                $this->sortBy = 'compatibility';
                break;
            case 'recently_active':
                $this->showOnlineOnly = true;
                $this->sortBy = 'activity';
                break;
            case 'marriage_ready':
                $this->marriageIntention = 'seeking_marriage';
                $this->marriageTimeline = 'within_1_year';
                $this->familyImportance = 'very_important';
                break;
            case 'professionals':
                $this->education = 'bachelor';
                $this->workStatus = 'employed';
                $this->incomeRange = 'above_average';
                break;
            case 'health_conscious':
                $this->smokingHabit = 'never';
                $this->drinkingHabit = 'socially';
                $this->exerciseFrequency = 'regularly';
                $this->dietPreference = 'healthy';
                break;
        }
        
        $this->filterPreset = $preset;
        $this->applyFilters();
    }
    
    private function formatProfileData($profile)
    {
        // Use pre-computed age from database
        $age = $profile->profile?->age;
            
        // Calculate distance (mock for now)
        $distance = rand(1, 50);
        
        // Get primary photo
        $primaryPhoto = $profile->profilePhoto?->medium_url ?? 
                       ($profile->photos->first()?->medium_url ?? null);
        
        // Get additional photos
        $additionalPhotos = $profile->photos->take(5)->map(function($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->medium_url,
                'thumbnail' => $photo->thumbnail_url
            ];
        })->toArray();
        
        // Calculate compatibility score (mock)
        $compatibilityScore = rand(65, 95);
        
        return [
            'id' => $profile->id,
            'name' => $profile->profile?->first_name ?? 'User',
            'full_name' => trim(($profile->profile?->first_name ?? '') . ' ' . ($profile->profile?->last_name ?? '')),
            'age' => $age,
            'distance' => $distance,
            'location' => $profile->profile?->city ?? 'Unknown',
            'bio' => $profile->profile?->bio,
            'occupation' => $profile->profile?->occupation,
            'primary_photo' => $primaryPhoto,
            'photos' => $additionalPhotos,
            'compatibility_score' => $compatibilityScore,
            'is_online' => $profile->last_active_at && $profile->last_active_at->gt(now()->subMinutes(5)),
            'verified' => $profile->email_verified_at !== null,
            
            // Profile completion indicators
            'has_bio' => !empty($profile->profile?->bio),
            'has_occupation' => !empty($profile->profile?->occupation),
            'photos_count' => $profile->photos->count(),
            
            // Cultural/Religious info
            'religion' => $profile->culturalProfile?->religion,
            'religiousness_level' => $profile->culturalProfile?->religiousness_level,
            'ethnicity' => $profile->culturalProfile?->ethnicity,
            'lifestyle_type' => $profile->culturalProfile?->lifestyle_type,
            'spoken_languages' => $profile->culturalProfile?->spoken_languages,
            
            // Career info
            'education' => $profile->careerProfile?->education_level,
            'education_field' => $profile->careerProfile?->field_of_study,
            'work_status' => $profile->careerProfile?->work_status,
            'job_title' => $profile->careerProfile?->job_title,
            'income_range' => $profile->careerProfile?->income_range,
            
            // Physical info
            'height' => $profile->physicalProfile?->height,
            'weight' => $profile->physicalProfile?->weight,
            'smoking_habit' => $profile->physicalProfile?->smoking_habit,
            'drinking_habit' => $profile->physicalProfile?->drinking_habit,
            'exercise_frequency' => $profile->physicalProfile?->exercise_frequency,
            'diet_preference' => $profile->physicalProfile?->diet_preference,
            
            // Family info
            'marriage_intention' => $profile->familyPreference?->marriage_intention,
            'children_preference' => $profile->familyPreference?->children_preference,
            'marriage_timeline' => $profile->familyPreference?->marriage_timeline,
            'family_importance' => $profile->familyPreference?->family_importance,
            
            // Basic info
            'gender' => $profile->profile?->gender,
            'looking_for' => $profile->profile?->looking_for_relationship,
        ];
    }
    
    public function likeProfile()
    {
        if (!$this->currentUser) return;
        
        $currentUserId = Auth::id();
        $profileId = $this->currentUser['id'];
        
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
        
        $this->loadNextProfile();
        
        // Log the action
        Log::info('Profile liked', [
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_match' => $mutualLike
        ]);
    }
    
    public function passProfile()
    {
        if (!$this->currentUser) return;
        
        $currentUserId = Auth::id();
        $profileId = $this->currentUser['id'];
        
        // Create dislike record
        Dislike::create([
            'user_id' => $currentUserId,
            'disliked_user_id' => $profileId
        ]);
        
        $this->loadNextProfile();
    }
    
    public function superLikeProfile()
    {
        if (!$this->currentUser) return;
        
        $currentUserId = Auth::id();
        $profileId = $this->currentUser['id'];
        
        // Create super like record
        Like::create([
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_super_like' => true
        ]);
        
        $this->loadNextProfile();
        
        // Emit super like event
        $this->dispatch('super-like-sent', profileId: $profileId);
    }
    
    public function render()
    {
        return view('livewire.pages.discover-page')
            ->layout('layouts.app', ['title' => 'Discover']);
    }
}

<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\MatchModel;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DiscoveryGrid extends Component
{
    public $profiles = [];
    public $loading = false;
    public $hasMore = true;
    public $page = 1;
    public $selectedProfileId = null;
    
    // Filters
    public $ageMin = 18;
    public $ageMax = 50;
    public $maxDistance = 50;
    public $education = '';
    public $profession = '';
    public $religion = '';
    
    protected $listeners = [
        'loadMore' => 'loadMoreProfiles',
        'profileActioned' => 'removeProfile'
    ];
    
    public function mount()
    {
        $this->loadProfiles();
    }
    
    public function loadProfiles($append = false)
    {
        $this->loading = true;
        
        $user = Auth::user();
        
        if (!$user) {
            $this->profiles = collect();
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
        
        // Build query with filters - Load comprehensive profile data like UserProfilePage
        $query = User::with([
            'profile.country',
            'photos' => function($query) {
                $query->where('is_private', false)
                      ->where(function($q) {
                          $q->where('status', 'approved')
                            ->orWhere('status', 'pending');
                      })
                      ->orderBy('order')
                      ->select('id', 'user_id', 'original_url', 'thumbnail_url', 'medium_url', 'is_profile_photo', 'order', 'status', 'uploaded_at');
            },
            'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo,order,status,uploaded_at',
            'culturalProfile',
            'physicalProfile', 
            'careerProfile',
            'familyPreference',
            'locationPreference',
            'preference',
            'userSetting',
            'prayerTimes',
            'verifiedBadges',
            'emergencyContacts'
        ])
        ->whereNotIn('id', $excludedUserIds)
        ->where('registration_completed', true)
        ->whereNull('disabled_at')
        ->whereHas('profile')
        ->whereHas('photos');
        
        // Apply age filter using pre-computed age column
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
        
        $newProfiles = $query
            ->orderBy('last_active_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(20) // Load 20 profiles at a time
            ->skip(($this->page - 1) * 20)
            ->get()
            ->map(function($profile) {
                return $this->formatProfileData($profile);
            })
            ->toArray();
        
        if ($append) {
            $this->profiles = array_merge($this->profiles, $newProfiles);
        } else {
            $this->profiles = $newProfiles;
        }
        
        $this->hasMore = count($newProfiles) === 20;
        $this->loading = false;
    }
    
    public function loadMoreProfiles()
    {
        if ($this->hasMore && !$this->loading) {
            $this->page++;
            $this->loadProfiles(true);
        }
    }
    
    public function applyFilters()
    {
        $this->page = 1;
        $this->loadProfiles(false);
    }
    
    public function resetFilters()
    {
        $this->ageMin = 18;
        $this->ageMax = 50;
        $this->maxDistance = 50;
        $this->education = '';
        $this->profession = '';
        $this->religion = '';
        $this->applyFilters();
    }
    
    private function formatProfileData($profile)
    {
        $currentUser = Auth::user();
        
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
                'thumbnail' => $photo->thumbnail_url,
                'original_url' => $photo->original_url
            ];
        })->toArray();
        
        // Calculate compatibility score (mock)
        $compatibilityScore = rand(65, 95);
        
        // Check privacy status
        $isPrivate = $profile->is_private ?? false;
        $canViewPrivateContent = $currentUser->canViewPrivateContent($profile);
        $showPrivacyRestrictions = $isPrivate && !$canViewPrivateContent;
        
        return [
            'id' => $profile->id,
            'name' => $profile->profile?->first_name ?? 'User',
            'age' => $age,
            'distance' => $distance,
            'location' => $profile->profile?->city ?? 'Unknown',
            'bio' => $showPrivacyRestrictions ? null : $profile->profile?->bio,
            'occupation' => $showPrivacyRestrictions ? null : $profile->profile?->occupation,
            'primary_photo' => $primaryPhoto,
            'photos' => $additionalPhotos,
            'compatibility_score' => $compatibilityScore,
            'is_online' => $profile->last_active_at && $profile->last_active_at->gt(now()->subMinutes(5)),
            'verified' => $profile->email_verified_at !== null,
            
            // Privacy information
            'is_private' => $isPrivate,
            'can_view_private_content' => $canViewPrivateContent,
            'show_privacy_restrictions' => $showPrivacyRestrictions,
            
            // Profile completion indicators
            'has_bio' => !empty($profile->profile?->bio),
            'has_occupation' => !empty($profile->profile?->occupation),
            'photos_count' => $profile->photos->count(),
            
            // Cultural/Religious info (hidden if private)
            'religion' => $showPrivacyRestrictions ? null : $profile->culturalProfile?->religion,
            'education' => $showPrivacyRestrictions ? null : $profile->careerProfile?->education_level,
            'religiousness' => $showPrivacyRestrictions ? null : $profile->culturalProfile?->religiousness_level,
            'ethnicity' => $showPrivacyRestrictions ? null : $profile->culturalProfile?->ethnicity,
            'languages' => $showPrivacyRestrictions ? null : ($profile->culturalProfile?->spoken_languages ? (is_array($profile->culturalProfile->spoken_languages) ? $profile->culturalProfile->spoken_languages : json_decode($profile->culturalProfile->spoken_languages, true)) : null),
            
            // Physical info (hidden if private)
            'height' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->height,
            'weight' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->weight,
            'fitness_level' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->fitness_level,
            'smoking_habit' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->smoking_habit,
            'drinking_habit' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->drinking_habit,
            'diet_preference' => $showPrivacyRestrictions ? null : $profile->physicalProfile?->diet_preference,
            
            // Family/Marriage info (hidden if private)
            'marriage_intention' => $showPrivacyRestrictions ? null : $profile->familyPreference?->marriage_intention,
            'children_preference' => $showPrivacyRestrictions ? null : $profile->familyPreference?->children_preference,
            'family_importance' => $showPrivacyRestrictions ? null : $profile->familyPreference?->family_importance,
            
            // Career info (hidden if private)
            'work_status' => $showPrivacyRestrictions ? null : $profile->careerProfile?->work_status,
            'job_title' => $showPrivacyRestrictions ? null : $profile->careerProfile?->job_title,
            'field_of_study' => $showPrivacyRestrictions ? null : $profile->careerProfile?->field_of_study,
            
            // Basic info (always shown)
            'gender' => $profile->profile?->gender,
            'looking_for' => $profile->profile?->looking_for_relationship,
            
            // Additional profile information
            'interests' => $showPrivacyRestrictions ? null : ($profile->profile?->interests ? (is_array($profile->profile->interests) ? $profile->profile->interests : explode(',', $profile->profile->interests)) : null),
            'country' => $profile->profile?->country?->name,
            'state' => $profile->profile?->state,
            'province' => $profile->profile?->province,
            
            // Verification badges
            'verified_badges' => $showPrivacyRestrictions ? null : $profile->verifiedBadges?->pluck('type')->toArray(),
        ];
    }
    
    public function likeProfile($profileId)
    {
        $currentUserId = Auth::id();
        
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
        
        $this->removeProfile($profileId);
        
        // Log the action
        Log::info('Profile liked', [
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_match' => $mutualLike
        ]);
    }
    
    public function passProfile($profileId)
    {
        $currentUserId = Auth::id();
        
        // Create dislike record
        Dislike::create([
            'user_id' => $currentUserId,
            'disliked_user_id' => $profileId
        ]);
        
        $this->removeProfile($profileId);
    }
    
    public function superLikeProfile($profileId)
    {
        $currentUserId = Auth::id();
        
        // Create super like record
        Like::create([
            'user_id' => $currentUserId,
            'liked_user_id' => $profileId,
            'is_super_like' => true
        ]);
        
        $this->removeProfile($profileId);
        
        // Emit super like event
        $this->dispatch('super-like-sent', profileId: $profileId);
    }
    
    public function viewProfile($profileId)
    {
        $this->selectedProfileId = $profileId;
        $this->dispatch('show-profile-modal', profileId: $profileId);
    }
    
    private function removeProfile($profileId)
    {
        $this->profiles = array_filter($this->profiles, function($profile) use ($profileId) {
            return $profile['id'] !== $profileId;
        });
        
        // Re-index array
        $this->profiles = array_values($this->profiles);
        
        // Load more if running low
        if (count($this->profiles) < 10 && $this->hasMore) {
            $this->loadMoreProfiles();
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.discovery-grid', [
            'profiles' => $this->profiles,
            'hasMore' => $this->hasMore,
            'loading' => $this->loading
        ]);
    }
}
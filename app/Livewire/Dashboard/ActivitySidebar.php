<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Models\Like;
use App\Models\UserActivity;
use App\Models\Profile;
use App\Models\UserPhoto;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActivitySidebar extends Component
{
    public $whoViewedYou = [];
    public $mutualLikes = [];
    public $recentActivity = [];
    public $profileViews = 0;
    public $totalLikes = 0;
    public $profileCompletion = 0;
    
    public function mount()
    {
        $this->loadActivityData();
        $this->calculateStats();
    }
    
    public function loadActivityData()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->whoViewedYou = [];
            $this->mutualLikes = [];
            $this->recentActivity = [];
            return;
        }
        
        // Who viewed you (last 5 viewers) - using UserActivity model
        try {
            $this->whoViewedYou = UserActivity::with([
                'user:id',
                'user.profile:user_id,first_name,last_name',
                'user.profilePhoto:id,user_id,thumbnail_url'
            ])
            ->where('activity_type', 'profile_view')
            ->whereJsonContains('metadata->target_user_id', $user->id)
            ->where('user_id', '!=', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($activity) {
                return [
                    'id' => $activity->user_id,
                    'name' => $activity->user?->profile?->first_name ?? 'Unknown',
                    'avatar' => $activity->user?->profilePhoto?->thumbnail_url,
                    'viewed_at' => $activity->created_at->diffForHumans()
                ];
            })
            ->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load who viewed you data: ' . $e->getMessage());
            $this->whoViewedYou = [];
        }
        
        // Mutual likes (people who liked you back)
        try {
            $this->mutualLikes = Like::with([
                'likedUser:id',
                'likedUser.profile:user_id,first_name,last_name',
                'likedUser.profilePhoto:id,user_id,thumbnail_url'
            ])
            ->where('user_id', $user->id)
            ->whereHas('likedUser.likes', function($query) use ($user) {
                $query->where('liked_user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function($like) {
                return [
                    'id' => $like->liked_user_id,
                    'name' => $like->likedUser?->profile?->first_name ?? 'Unknown',
                    'avatar' => $like->likedUser?->profilePhoto?->thumbnail_url,
                    'is_super_like' => $like->is_super_like ?? false,
                    'liked_at' => $like->created_at->diffForHumans()
                ];
            })
            ->toArray();
        } catch (\Exception $e) {
            \Log::warning('Failed to load mutual likes data: ' . $e->getMessage());
            $this->mutualLikes = [];
        }
        
        // Recent activity mix
        $this->recentActivity = $this->buildRecentActivity($user);
    }
    
    public function buildRecentActivity($user)
    {
        $activities = [];
        
        // Recent likes received
        $recentLikes = Like::with([
            'user:id', 
            'user.profile:user_id,first_name',
            'user.profilePhoto:id,user_id,thumbnail_url'
        ])
            ->where('liked_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();
        
        foreach($recentLikes as $like) {
            $activities[] = [
                'type' => 'like_received',
                'user_name' => $like->user?->profile?->first_name ?? 'Someone',
                'user_avatar' => $like->user?->profilePhoto?->thumbnail_url,
                'text' => $like->is_super_like ? 'super liked you' : 'liked you',
                'time' => $like->created_at->diffForHumans(),
                'icon' => $like->is_super_like ? 'star' : 'heart'
            ];
        }
        
        // Recent profile views - using UserActivity
        $recentViews = UserActivity::with([
            'user:id', 
            'user.profile:user_id,first_name',
            'user.profilePhoto:id,user_id,thumbnail_url'
        ])
            ->where('activity_type', 'profile_view')
            ->whereJsonContains('metadata->target_user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();
        
        foreach($recentViews as $activity) {
            $activities[] = [
                'type' => 'profile_view',
                'user_name' => $activity->user?->profile?->first_name ?? 'Someone',
                'user_avatar' => $activity->user?->profilePhoto?->thumbnail_url,
                'text' => 'viewed your profile',
                'time' => $activity->created_at->diffForHumans(),
                'icon' => 'eye'
            ];
        }
        
        // Sort by time and take top 5
        return collect($activities)
            ->sortByDesc('time')
            ->take(5)
            ->values()
            ->toArray();
    }
    
    public function calculateStats()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->profileViews = 0;
            $this->totalLikes = 0;
            $this->profileCompletion = 0;
            return;
        }
        
        try {
            // Profile views count - get from UserActivity table or profiles table
            $this->profileViews = UserActivity::where('activity_type', 'profile_view')
                ->whereJsonContains('metadata->target_user_id', $user->id)
                ->count();
        } catch (\Exception $e) {
            \Log::warning('Failed to load profile views count: ' . $e->getMessage());
            $this->profileViews = 0;
        }

        try {
            // Total likes received
            $this->totalLikes = Like::where('liked_user_id', $user->id)->count();
        } catch (\Exception $e) {
            \Log::warning('Failed to load total likes count: ' . $e->getMessage());
            $this->totalLikes = 0;
        }

        try {
            // Profile completion percentage
            $this->profileCompletion = $this->calculateProfileCompletion($user);
        } catch (\Exception $e) {
            \Log::warning('Failed to calculate profile completion: ' . $e->getMessage());
            $this->profileCompletion = 0;
        }
    }
    
    public function calculateProfileCompletion($user)
    {
        $fields = [
            'profile' => $user->profile !== null,
            'photos' => $user->photos->count() > 0,
            'bio' => !empty($user->profile?->bio),
            'occupation' => !empty($user->profile?->occupation),
            'cultural_profile' => $user->culturalProfile !== null,
            'physical_profile' => $user->physicalProfile !== null,
            'career_profile' => $user->careerProfile !== null,
            'family_preference' => $user->familyPreference !== null,
            'location' => $user->locationPreference !== null,
            'interests' => !empty($user->profile?->interests),
        ];
        
        $completed = array_sum($fields);
        $total = count($fields);
        
        return round(($completed / $total) * 100);
    }
    
    public function viewProfile($userId)
    {
        return redirect()->route('user.profile.show', $userId);
    }
    
    public function render()
    {
        return view('livewire.dashboard.activity-sidebar');
    }
}

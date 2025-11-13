<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Like;
use App\Models\MatchModel;
use App\Models\Message;
use App\Models\UserActivity;
use Carbon\Carbon;

class MyProfilePage extends Component
{
    public $user;
    public $profileStats = [];
    public $profileCompletion = 0;
    public $completionSections = [];
    public $recentActivity = [];
    public $profileViews = [];
    public $likeStats = [];
    public $messageStats = [];

    public function mount()
    {
        $this->user = Auth::user()->load([
            'profile',
            'profilePhoto',
            'photos',
            'culturalProfile',
            'familyPreference',
            'careerProfile',
            'physicalProfile',
            'locationPreference'
        ]);
        
        $this->calculateProfileCompletion();
        $this->loadProfileStats();
        $this->loadRecentActivity();
        $this->loadProfileViews();
        $this->loadLikeStats();
        $this->loadMessageStats();
    }

    public function calculateProfileCompletion()
    {
        $sections = [
            'basic_info' => [
                'name' => 'Basic Information',
                'completed' => $this->user->profile && 
                              $this->user->profile->first_name && 
                              $this->user->profile->bio && 
                              $this->user->photos->count() >= 2,
                'weight' => 25,
                'items' => [
                    'Profile photos (2+)' => $this->user->photos->count() >= 2,
                    'Name & bio' => $this->user->profile?->first_name && $this->user->profile?->bio,
                    'Basic details' => $this->user->profile?->date_of_birth && $this->user->profile?->city,
                ]
            ],
            'cultural_background' => [
                'name' => 'Cultural Background',
                'completed' => $this->user->culturalProfile && 
                              $this->user->culturalProfile->religion &&
                              $this->user->culturalProfile->spoken_languages,
                'weight' => 20,
                'items' => [
                    'Religion & practice level' => $this->user->culturalProfile?->religion,
                    'Languages spoken' => $this->user->culturalProfile?->spoken_languages,
                    'Traditional values' => $this->user->culturalProfile?->lifestyle_type,
                ]
            ],
            'family_marriage' => [
                'name' => 'Family & Marriage',
                'completed' => $this->user->familyPreference && 
                              $this->user->familyPreference->marriage_timeline &&
                              $this->user->familyPreference->children_preference,
                'weight' => 20,
                'items' => [
                    'Marriage intentions' => $this->user->familyPreference?->marriage_timeline,
                    'Children preference' => $this->user->familyPreference?->children_preference,
                    'Family importance' => $this->user->familyPreference?->family_importance,
                ]
            ],
            'career_education' => [
                'name' => 'Career & Education',
                'completed' => $this->user->careerProfile && 
                              $this->user->careerProfile->education_level &&
                              $this->user->careerProfile->work_status,
                'weight' => 20,
                'items' => [
                    'Education level' => $this->user->careerProfile?->education_level,
                    'Work status' => $this->user->careerProfile?->work_status,
                    'Career goals' => $this->user->careerProfile?->career_goals,
                ]
            ],
            'lifestyle' => [
                'name' => 'Lifestyle',
                'completed' => $this->user->physicalProfile && 
                              $this->user->physicalProfile->exercise_frequency &&
                              $this->user->physicalProfile->smoking_status &&
                              $this->user->physicalProfile->drinking_status,
                'weight' => 15,
                'items' => [
                    'Exercise habits' => $this->user->physicalProfile?->exercise_frequency,
                    'Smoking & drinking' => $this->user->physicalProfile?->smoking_status && $this->user->physicalProfile?->drinking_status,
                    'Physical details' => $this->user->physicalProfile?->height && $this->user->physicalProfile?->weight,
                ]
            ]
        ];

        $this->completionSections = $sections;
        
        $totalWeight = 0;
        $completedWeight = 0;
        
        foreach ($sections as $section) {
            $totalWeight += $section['weight'];
            if ($section['completed']) {
                $completedWeight += $section['weight'];
            }
        }
        
        $this->profileCompletion = $totalWeight > 0 ? round(($completedWeight / $totalWeight) * 100) : 0;
    }

    public function loadProfileStats()
    {
        $userId = $this->user->id;
        $thirtyDaysAgo = Carbon::now()->subDays(30);

        // Profile views (from user_activity or profiles table)
        $profileViews = $this->user->profile?->profile_views ?? 0;
        
        // Recent profile views (last 30 days)
        $recentViews = UserActivity::where('activity_type', 'profile_view')
                                  ->whereJsonContains('metadata->target_user_id', $userId)
                                  ->where('created_at', '>=', $thirtyDaysAgo)
                                  ->count();

        // Likes received
        $likesReceived = Like::where('liked_user_id', $userId)->count();
        $recentLikes = Like::where('liked_user_id', $userId)
                          ->where('created_at', '>=', $thirtyDaysAgo)
                          ->count();

        // Matches
        $totalMatches = MatchModel::where('user_id', $userId)->count();
        $recentMatches = MatchModel::where('user_id', $userId)
                                  ->where('created_at', '>=', $thirtyDaysAgo)
                                  ->count();

        // Messages sent/received
        $messagesSent = Message::where('sender_id', $userId)->count();
        $messagesReceived = Message::whereHas('chat.users', function($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where('sender_id', '!=', $userId)->count();
        
        // Response rate calculation
        $conversationsStarted = Message::whereHas('chat.users', function($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where('sender_id', '!=', $userId)
          ->distinct('sender_id')
          ->count('sender_id');
        
        $conversationsReplied = Message::where('sender_id', $userId)
                                      ->distinct('chat_id')
                                      ->count('chat_id');

        $responseRate = $conversationsStarted > 0 ? round(($conversationsReplied / $conversationsStarted) * 100) : 0;

        // Average response time (mock for now)
        $avgResponseTime = '2h 15m';

        $this->profileStats = [
            'profile_views' => [
                'total' => $profileViews,
                'recent' => $recentViews,
                'trend' => $recentViews > 0 ? 'up' : 'stable'
            ],
            'likes_received' => [
                'total' => $likesReceived,
                'recent' => $recentLikes,
                'trend' => $recentLikes > 0 ? 'up' : 'stable'
            ],
            'matches' => [
                'total' => $totalMatches,
                'recent' => $recentMatches,
                'trend' => $recentMatches > 0 ? 'up' : 'stable'
            ],
            'messages' => [
                'sent' => $messagesSent,
                'received' => $messagesReceived,
                'total' => $messagesSent + $messagesReceived
            ],
            'response_rate' => $responseRate,
            'avg_response_time' => $avgResponseTime
        ];
    }

    public function loadRecentActivity()
    {
        $userId = $this->user->id;
        
        // Get recent activities (likes received, matches, profile views)
        $recentLikes = Like::with(['user:id', 'user.profile:user_id,first_name', 'user.profilePhoto:id,user_id,thumbnail_url'])
                          ->where('liked_user_id', $userId)
                          ->where('created_at', '>=', Carbon::now()->subDays(7))
                          ->orderBy('created_at', 'desc')
                          ->take(5)
                          ->get()
                          ->map(function($like) {
                              return [
                                  'type' => 'like',
                                  'user_name' => $like->user?->profile?->first_name ?? 'Someone',
                                  'user_avatar' => $like->user?->profilePhoto?->thumbnail_url,
                                  'text' => 'liked your profile',
                                  'time' => $like->created_at->diffForHumans(),
                                  'icon' => 'heart'
                              ];
                          });

        $recentMatches = MatchModel::with(['matchedUser:id', 'matchedUser.profile:user_id,first_name', 'matchedUser.profilePhoto:id,user_id,thumbnail_url'])
                                  ->where('user_id', $userId)
                                  ->where('created_at', '>=', Carbon::now()->subDays(7))
                                  ->orderBy('created_at', 'desc')
                                  ->take(3)
                                  ->get()
                                  ->map(function($match) {
                                      return [
                                          'type' => 'match',
                                          'user_name' => $match->matchedUser?->profile?->first_name ?? 'Someone',
                                          'user_avatar' => $match->matchedUser?->profilePhoto?->thumbnail_url,
                                          'text' => 'matched with you',
                                          'time' => $match->created_at->diffForHumans(),
                                          'icon' => 'star'
                                      ];
                                  });

        // Combine and sort activities
        $this->recentActivity = $recentLikes->merge($recentMatches)
                                           ->sortByDesc('time')
                                           ->take(8)
                                           ->values()
                                           ->toArray();
    }

    public function loadProfileViews()
    {
        $userId = $this->user->id;
        
        // Get recent profile views
        $this->profileViews = UserActivity::with([
            'user:id',
            'user.profile:user_id,first_name,last_name',
            'user.profilePhoto:id,user_id,thumbnail_url'
        ])
        ->where('activity_type', 'profile_view')
        ->whereJsonContains('metadata->target_user_id', $userId)
        ->where('user_id', '!=', $userId)
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get()
        ->map(function($activity) {
            return [
                'id' => $activity->user_id,
                'name' => $activity->user?->profile?->first_name ?? $activity->user?->name ?? 'Anonymous',
                'avatar' => $activity->user?->profilePhoto?->thumbnail_url,
                'viewed_at' => $activity->created_at->diffForHumans(),
                'is_recent' => $activity->created_at->gt(Carbon::now()->subHours(24))
            ];
        })
        ->toArray();
    }

    public function loadLikeStats()
    {
        $userId = $this->user->id;
        
        // Likes given vs received
        $likesGiven = Like::where('user_id', $userId)->count();
        $likesReceived = Like::where('liked_user_id', $userId)->count();
        
        // Match rate (likes that became matches)
        $mutualLikes = Like::where('user_id', $userId)
                          ->whereExists(function($query) use ($userId) {
                              $query->select('id')
                                    ->from('likes as l2')
                                    ->whereRaw('l2.user_id = likes.liked_user_id')
                                    ->whereRaw('l2.liked_user_id = ?', [$userId]);
                          })
                          ->count();
        
        $matchRate = $likesGiven > 0 ? round(($mutualLikes / $likesGiven) * 100) : 0;
        
        $this->likeStats = [
            'given' => $likesGiven,
            'received' => $likesReceived,
            'mutual' => $mutualLikes,
            'match_rate' => $matchRate
        ];
    }

    public function loadMessageStats()
    {
        $userId = $this->user->id;
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        // Recent message activity
        $recentMessagesSent = Message::where('sender_id', $userId)
                                    ->where('created_at', '>=', $sevenDaysAgo)
                                    ->count();
        
        $recentMessagesReceived = Message::whereHas('chat.users', function($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where('sender_id', '!=', $userId)
                                        ->where('created_at', '>=', $sevenDaysAgo)
                                        ->count();
        
        // Active conversations (conversations with messages in last 30 days)
        $activeConversations = Message::whereHas('chat.users', function($query) use ($userId) {
            $query->where('users.id', $userId);
        })->where(function($query) use ($userId) {
            $query->where('sender_id', $userId)->orWhere('sender_id', '!=', $userId);
        })
                                     ->where('created_at', '>=', Carbon::now()->subDays(30))
                                     ->select('sender_id', 'chat_id')
                                     ->get()
                                     ->map(function($message) use ($userId) {
                                         return $message->chat_id;
                                     })
                                     ->unique()
                                     ->count();
        
        $this->messageStats = [
            'recent_sent' => $recentMessagesSent,
            'recent_received' => $recentMessagesReceived,
            'active_conversations' => $activeConversations
        ];
    }

    public function boostProfile()
    {
        // Implementation for profile boost feature
        session()->flash('message', 'Profile boost activated! (Feature coming soon)');
    }

    public function viewAsOthers()
    {
        return redirect()->route('user.profile.preview');
    }

    public function editProfile()
    {
        return redirect()->route('profile.enhance');
    }

    public function render()
    {
        return view('livewire.pages.my-profile-page')->layout('layouts.app');
    }
}

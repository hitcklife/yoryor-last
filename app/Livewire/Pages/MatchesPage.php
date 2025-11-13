<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\MatchModel;
use App\Models\Like;
use App\Models\Message;
use App\Models\Chat;
use Carbon\Carbon;

class MatchesPage extends Component
{
    public $activeTab = 'liked_you';
    public $matches = [];
    public $newMatches = [];
    public $messagesWithMatches = [];
    public $superLikeMatches = [];
    public $searchTerm = '';
    public $filterDistance = 'all';
    public $filterAge = 'all';

    protected $listeners = ['refreshMatches' => 'loadMatches'];

    public function mount()
    {
        $this->loadMatches();
    }

    public function loadMatches()
    {
        $user = Auth::user();
        
        // Get all matches with user details
        $allMatches = MatchModel::with([
            'matchedUser:id,last_active_at',
            'matchedUser.profile:user_id,first_name,last_name,date_of_birth,bio,city,country_code',
            'matchedUser.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url',
            'matchedUser.culturalProfile:user_id,religion,religiousness_level',
            'matchedUser.familyPreference:user_id,marriage_timeline,children_preference'
        ])
        ->where('user_id', $user->id)
        ->whereHas('matchedUser') // Ensure matched user exists
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function($match) use ($user) {
            $matchedUser = $match->matchedUser;
            
            // Calculate age
            $age = $matchedUser->profile?->date_of_birth 
                ? Carbon::parse($matchedUser->profile->date_of_birth)->age 
                : null;
                
            // Get last message - find chat between users
            $chat = \App\Models\Chat::whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })->whereHas('users', function($query) use ($matchedUser) {
                $query->where('users.id', $matchedUser->id);
            })->first();
            
            $lastMessage = $chat ? Message::where('chat_id', $chat->id)->latest()->first() : null;
            
            // Check if it was a super like (not implemented in current database schema)
            $isSuperLike = false;
            
            // Count unread messages
            $unreadCount = $chat ? Message::where('chat_id', $chat->id)
                                ->where('sender_id', $matchedUser->id)
                                ->whereDoesntHave('messageReads', function($query) use ($user) {
                                    $query->where('user_id', $user->id);
                                })
                                ->count() : 0;
            
            return [
                'id' => $matchedUser->id,
                'match_id' => $match->id,
                'name' => $matchedUser->profile?->first_name ?? 'Unknown',
                'full_name' => trim(($matchedUser->profile?->first_name ?? '') . ' ' . ($matchedUser->profile?->last_name ?? '')),
                'age' => $age,
                'bio' => $matchedUser->profile?->bio,
                'location' => $matchedUser->profile?->city ?? 'Unknown',
                'avatar' => $matchedUser->profilePhoto?->thumbnail_url,
                'is_online' => $matchedUser->last_active_at && $matchedUser->last_active_at->gt(now()->subMinutes(5)),
                'last_active' => $matchedUser->last_active_at?->diffForHumans(),
                'matched_at' => $match->created_at->diffForHumans(),
                'is_new' => $match->created_at->gt(now()->subDays(3)),
                'is_super_like' => $isSuperLike,
                'religion' => $matchedUser->culturalProfile?->religion,
                'marriage_timeline' => $matchedUser->familyPreference?->marriage_timeline,
                'has_messages' => $lastMessage !== null,
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->message_content,
                    'sent_at' => $lastMessage->created_at->diffForHumans(),
                    'is_from_me' => $lastMessage->sender_id === $user->id
                ] : null,
                'unread_count' => $unreadCount,
                'distance' => rand(1, 25) // Mock distance for now
            ];
        })
        ->toArray();

        // Categorize matches
        $this->matches = $allMatches;
        $this->newMatches = array_filter($allMatches, fn($match) => $match['is_new']);
        $this->messagesWithMatches = array_filter($allMatches, fn($match) => $match['has_messages']);
        $this->superLikeMatches = array_filter($allMatches, fn($match) => $match['is_super_like']);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function getFilteredMatches()
    {
        $matches = match($this->activeTab) {
            'liked_you' => $this->newMatches,
            'you_liked' => $this->messagesWithMatches,
            'mutual' => $this->matches,
            'views' => $this->superLikeMatches,
            default => $this->newMatches
        };

        // Apply search filter
        if (!empty($this->searchTerm)) {
            $matches = array_filter($matches, function($match) {
                return stripos($match['name'], $this->searchTerm) !== false ||
                       stripos($match['full_name'], $this->searchTerm) !== false ||
                       stripos($match['location'], $this->searchTerm) !== false;
            });
        }

        // Apply distance filter
        if ($this->filterDistance !== 'all') {
            $maxDistance = (int)$this->filterDistance;
            $matches = array_filter($matches, fn($match) => $match['distance'] <= $maxDistance);
        }

        // Apply age filter
        if ($this->filterAge !== 'all') {
            [$minAge, $maxAge] = explode('-', $this->filterAge);
            $matches = array_filter($matches, function($match) use ($minAge, $maxAge) {
                return $match['age'] && $match['age'] >= $minAge && $match['age'] <= $maxAge;
            });
        }

        return $matches;
    }

    public function startChat($matchId)
    {
        return redirect()->route('messages.chat', ['user' => $matchId]);
    }

    public function viewProfile($userId)
    {
        return redirect()->route('user.profile.view', ['user' => $userId]);
    }

    public function unmatchUser($matchId)
    {
        $user = Auth::user();
        
        // Delete both match records
        MatchModel::where('user_id', $user->id)->where('matched_user_id', $matchId)->delete();
        MatchModel::where('user_id', $matchId)->where('matched_user_id', $user->id)->delete();
        
        // Reload matches
        $this->loadMatches();
        
        session()->flash('message', 'User unmatched successfully.');
    }

    public function starMatch($matchId)
    {
        // Implementation for starring/favoriting matches
        // This would require adding a favorites table or field
        session()->flash('message', 'Match starred! (Feature coming soon)');
    }

    public function render()
    {
        return view('livewire.pages.matches-page', [
            'filteredMatches' => $this->getFilteredMatches()
        ])->layout('layouts.app');
    }
}

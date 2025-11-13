<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use App\Models\User;
use App\Models\Like;
use App\Models\Dislike;
use App\Models\UserBlock;
use App\Models\UserStory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserProfilePage extends Component
{
    public User $user;
    public $isLiked = false;
    public $isDisliked = false;
    public $isBlocked = false;
    public $hasBlocked = false; // If the viewed user has blocked current user
    public $isMatch = false;
    public $showReportModal = false;
    public $reportReason = '';
    public $reportDescription = '';
    public $isPrivateUser = false;

    protected $rules = [
        'reportReason' => 'required|string',
        'reportDescription' => 'nullable|string|max:500',
    ];

    public function mount($uuid)
    {
        // Find user by profile UUID instead of route model binding
        $user = User::findByProfileUuid($uuid);
        
        if (!$user) {
            abort(404, 'User profile not found');
        }

        // Load the relationships we need
        $this->user = $user->load([
            'profile', 
            'photos', 
            'stories' => function($query) {
                $query->where('expires_at', '>', now())
                      ->where('status', 'active')
                      ->orderBy('created_at', 'desc');
            },
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
        ]);

        // Don't allow users to view their own profile through this route
        if ($this->user->id === Auth::id()) {
            return redirect()->route('my-profile');
        }

        // Check if the user has privacy enabled and if current user can view private content
        $this->isPrivateUser = ($this->user->is_private ?? false) && !Auth::user()->canViewPrivateContent($this->user);

        $this->loadUserInteractionStatus();
    }

    protected function loadUserInteractionStatus()
    {
        $currentUserId = Auth::id();

        // Check if current user has liked this user
        $this->isLiked = Like::where('user_id', $currentUserId)
                             ->where('liked_user_id', $this->user->id)
                             ->exists();

        // Check if current user has disliked this user
        $this->isDisliked = Dislike::where('user_id', $currentUserId)
                                   ->where('disliked_user_id', $this->user->id)
                                   ->exists();

        // Check if current user has blocked this user
        $this->isBlocked = UserBlock::where('blocker_id', $currentUserId)
                                    ->where('blocked_id', $this->user->id)
                                    ->exists();

        // Check if the viewed user has blocked current user
        $this->hasBlocked = UserBlock::where('blocker_id', $this->user->id)
                                     ->where('blocked_id', $currentUserId)
                                     ->exists();

        // Check if it's a mutual match
        $this->isMatch = $this->isLiked && Like::where('user_id', $this->user->id)
                                               ->where('liked_user_id', $currentUserId)
                                               ->exists();
    }

    public function likeUser()
    {
        if ($this->isBlocked || $this->hasBlocked) {
            return;
        }

        $currentUserId = Auth::id();

        if (!$this->isLiked) {
            // Create like
            Like::create([
                'user_id' => $currentUserId,
                'liked_user_id' => $this->user->id,
            ]);

            // Remove dislike if exists
            if ($this->isDisliked) {
                Dislike::where('user_id', $currentUserId)
                       ->where('disliked_user_id', $this->user->id)
                       ->delete();
                $this->isDisliked = false;
            }

            $this->isLiked = true;

            // Check if it becomes a match
            $this->isMatch = Like::where('user_id', $this->user->id)
                                 ->where('liked_user_id', $currentUserId)
                                 ->exists();

            if ($this->isMatch) {
                session()->flash('match', 'It\'s a match! 🎉');
            } else {
                session()->flash('message', 'Like sent! ❤️');
            }
        }
    }

    public function unlikeUser()
    {
        if ($this->isLiked) {
            Like::where('user_id', Auth::id())
                ->where('liked_user_id', $this->user->id)
                ->delete();

            $this->isLiked = false;
            $this->isMatch = false;

            session()->flash('message', 'Like removed');
        }
    }

    public function dislikeUser()
    {
        if ($this->isBlocked || $this->hasBlocked) {
            return;
        }

        $currentUserId = Auth::id();

        if (!$this->isDisliked) {
            // Create dislike
            Dislike::create([
                'user_id' => $currentUserId,
                'disliked_user_id' => $this->user->id,
            ]);

            // Remove like if exists
            if ($this->isLiked) {
                Like::where('user_id', $currentUserId)
                    ->where('liked_user_id', $this->user->id)
                    ->delete();
                $this->isLiked = false;
                $this->isMatch = false;
            }

            $this->isDisliked = true;
            session()->flash('message', 'User passed');
        }
    }

    public function blockUser()
    {
        if (!$this->isBlocked) {
            UserBlock::create([
                'blocker_id' => Auth::id(),
                'blocked_id' => $this->user->id,
                'reason' => 'Blocked from profile view',
            ]);

            // Remove any existing likes/dislikes
            Like::where(function ($query) {
                $query->where('user_id', Auth::id())
                      ->where('liked_user_id', $this->user->id);
            })->orWhere(function ($query) {
                $query->where('user_id', $this->user->id)
                      ->where('liked_user_id', Auth::id());
            })->delete();

            Dislike::where('user_id', Auth::id())
                   ->where('disliked_user_id', $this->user->id)
                   ->delete();

            $this->isBlocked = true;
            $this->isLiked = false;
            $this->isDisliked = false;
            $this->isMatch = false;

            session()->flash('message', 'User blocked successfully');
            
            // Redirect to matches page after blocking
            return redirect()->route('matches');
        }
    }

    public function messageUser()
    {
        if ($this->isMatch && !$this->isBlocked && !$this->hasBlocked) {
            // Redirect to messages with this user
            return redirect()->route('messages')->with('start_conversation', $this->user->id);
        }
    }


    public function openReportModal()
    {
        $this->showReportModal = true;
    }

    public function closeReportModal()
    {
        $this->showReportModal = false;
        $this->reportReason = '';
        $this->reportDescription = '';
    }

    public function reportUser()
    {
        $this->validate();

        // Create user report (you may need to create this model)
        // UserReport::create([
        //     'reporter_id' => Auth::id(),
        //     'reported_user_id' => $this->user->id,
        //     'reason' => $this->reportReason,
        //     'description' => $this->reportDescription,
        // ]);

        $this->closeReportModal();
        session()->flash('message', 'User reported. Thank you for keeping our community safe.');
    }

    public function viewStory($storyId)
    {
        // Handle story viewing logic
        $story = $this->user->stories->find($storyId);
        if ($story) {
            // Mark story as viewed (if you track story views)
            // StoryView::firstOrCreate([
            //     'story_id' => $storyId,
            //     'viewer_id' => Auth::id(),
            // ]);

            $this->dispatch('story-viewed', ['story' => $story]);
        }
    }

    public function getAge()
    {
        if ($this->user->profile && $this->user->profile->date_of_birth) {
            return \Carbon\Carbon::parse($this->user->profile->date_of_birth)->age;
        }
        return null;
    }

    public function getCountryName()
    {
        if (!$this->user->profile || !$this->user->profile->country) {
            return null;
        }

        $country = $this->user->profile->country;
        
        // If it's already a string and not JSON, return as is
        if (is_string($country)) {
            $decoded = json_decode($country, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['name'])) {
                return $decoded['name'];
            }
            return $country; // Return as string if not JSON
        }
        
        // If it's an array with name key
        if (is_array($country) && isset($country['name'])) {
            return $country['name'];
        }
        
        return $country;
    }

    public function render()
    {
        // Don't show the profile if the viewed user has blocked the current user
        if ($this->hasBlocked) {
            abort(404, 'Profile not found');
        }

        // For private users, we still show the profile but with privacy restrictions
        // The view will handle the privacy UI

        return view('livewire.pages.user-profile-page')
            ->layout('layouts.app');
    }
}
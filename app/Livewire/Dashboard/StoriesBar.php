<?php

namespace App\Livewire\Dashboard;

use App\Models\UserStory;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class StoriesBar extends Component
{
    public $userHasStory = false;
    public $activeStories = [];
    
    public function mount()
    {
        $this->loadStories();
    }
    
    public function loadStories()
    {
        $user = Auth::user();
        
        if (!$user) {
            $this->userHasStory = false;
            $this->stories = collect();
            return;
        }
        
        // Check if current user has an active story
        $this->userHasStory = UserStory::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->exists();
        
        // Demo stories for development
        $this->activeStories = collect([
            [
                'id' => 1,
                'user_id' => 2,
                'user_name' => 'Sarah',
                'user_avatar' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                'media_url' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                'thumbnail_url' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                'type' => 'image',
                'created_at' => now(),
                'is_viewed' => false,
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'user_name' => 'Emma',
                'user_avatar' => '/assets/images/pexels-asadphoto-169196.jpg',
                'media_url' => '/assets/images/pexels-asadphoto-169196.jpg',
                'thumbnail_url' => '/assets/images/pexels-asadphoto-169196.jpg',
                'type' => 'image',
                'created_at' => now()->subHours(2),
                'is_viewed' => false,
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'user_name' => 'Lisa',
                'user_avatar' => '/assets/images/538664-married-couple.jpg',
                'media_url' => '/assets/images/538664-married-couple.jpg',
                'thumbnail_url' => '/assets/images/538664-married-couple.jpg',
                'type' => 'image',
                'created_at' => now()->subHours(1),
                'is_viewed' => true,
            ],
        ]);
    }
    
    public function viewStory($userId)
    {
        // Emit event to open story viewer
        \Log::info("Dispatching open-story-viewer event for userId: {$userId}");
        $this->dispatch('open-story-viewer', userId: $userId);
    }
    
    public function createStory()
    {
        // Emit event to open story creation modal
        $this->dispatch('open-story-creator');
    }
    
    public function render()
    {
        return view('livewire.dashboard.stories-bar');
    }
}

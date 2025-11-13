<?php

namespace App\Livewire\Dashboard;

use App\Models\UserStory;
use Livewire\Component;
use Livewire\Attributes\On;

class StoryViewer extends Component
{
    public $isOpen = false;
    public $stories = [];
    public $currentStoryIndex = 0;
    public $currentStory = null;
    public $userId;
    public $progress = 0;
    public $isPaused = false;
    
    #[On('open-story-viewer')]
    public function openStoryViewer($userId)
    {
        \Log::info("Received open-story-viewer event for userId: {$userId}");
        $this->userId = $userId;
        $this->loadUserStories();
        $this->isOpen = true;
        $this->currentStoryIndex = 0;
        $this->setCurrentStory();
        $this->startProgress();
        \Log::info("Story viewer opened, isOpen: {$this->isOpen}, stories count: " . count($this->stories));

        // Force component refresh
        $this->dispatch('$refresh');
    }
    
    public function loadUserStories()
    {
        // Demo stories for development
        $demoStories = [
            [
                'id' => 1,
                'user_id' => 2,
                'media_url' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                'thumbnail_url' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg',
                'type' => 'image',
                'caption' => 'Beautiful sunset from my balcony! 🌅',
                'created_at' => now()->toISOString(),
                'user' => [
                    'id' => 2,
                    'name' => 'Sarah',
                    'profile_photo' => [
                        'thumbnail_url' => '/assets/images/premium_photo-1674235766088-80d8410f9523.jpeg'
                    ],
                    'profile' => [
                        'first_name' => 'Sarah'
                    ]
                ]
            ],
            [
                'id' => 2,
                'user_id' => 3,
                'media_url' => '/assets/images/pexels-asadphoto-169196.jpg',
                'thumbnail_url' => '/assets/images/pexels-asadphoto-169196.jpg',
                'type' => 'image',
                'caption' => 'Coffee and good vibes ☕️',
                'created_at' => now()->subMinutes(30)->toISOString(),
                'user' => [
                    'id' => 3,
                    'name' => 'Emma',
                    'profile_photo' => [
                        'thumbnail_url' => '/assets/images/pexels-asadphoto-169196.jpg'
                    ],
                    'profile' => [
                        'first_name' => 'Emma'
                    ]
                ]
            ],
            [
                'id' => 3,
                'user_id' => 4,
                'media_url' => '/assets/images/538664-married-couple.jpg',
                'thumbnail_url' => '/assets/images/538664-married-couple.jpg',
                'type' => 'image',
                'caption' => 'Weekend getaway! 🏔️',
                'created_at' => now()->subHours(1)->toISOString(),
                'user' => [
                    'id' => 4,
                    'name' => 'Lisa',
                    'profile_photo' => [
                        'thumbnail_url' => '/assets/images/538664-married-couple.jpg'
                    ],
                    'profile' => [
                        'first_name' => 'Lisa'
                    ]
                ]
            ]
        ];

        // Find stories for the specific user
        $this->stories = array_values(array_filter($demoStories, function($story) {
            return $story['user_id'] == $this->userId;
        }));

        // If no specific user stories found, show the first user's stories as fallback
        if (empty($this->stories)) {
            $this->stories = array_filter($demoStories, function($story) {
                return $story['user_id'] == 2; // Default to Sarah's stories
            });
            $this->stories = array_values($this->stories);
        }
    }
    
    public function setCurrentStory()
    {
        if (isset($this->stories[$this->currentStoryIndex])) {
            $this->currentStory = $this->stories[$this->currentStoryIndex];
            $this->progress = 0;
        }
    }
    
    public function nextStory()
    {
        if ($this->currentStoryIndex < count($this->stories) - 1) {
            $this->currentStoryIndex++;
            $this->setCurrentStory();
            $this->startProgress();
        } else {
            $this->closeViewer();
        }
    }
    
    public function previousStory()
    {
        if ($this->currentStoryIndex > 0) {
            $this->currentStoryIndex--;
            $this->setCurrentStory();
            $this->startProgress();
        }
    }
    
    public function startProgress()
    {
        $this->progress = 0;
        $this->isPaused = false;
        // Progress will be handled by JavaScript
        $this->dispatch('start-story-progress');
    }
    
    public function pauseStory()
    {
        $this->isPaused = true;
        $this->dispatch('pause-story-progress');
    }
    
    public function resumeStory()
    {
        $this->isPaused = false;
        $this->dispatch('resume-story-progress');
    }
    
    public function closeViewer()
    {
        $this->isOpen = false;
        $this->currentStory = null;
        $this->currentStoryIndex = 0;
        $this->progress = 0;
        $this->dispatch('close-story-viewer');
    }
    
    public function updateProgress($progress)
    {
        $this->progress = $progress;
        if ($progress >= 100) {
            $this->nextStory();
        }
    }
    
    public function render()
    {
        return view('livewire.dashboard.story-viewer');
    }
}

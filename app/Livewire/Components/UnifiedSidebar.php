<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UnifiedSidebar extends Component
{
    public $hideText = false;
    public $unreadMessages = 0;
    public $totalLikes = 0;

    public function mount()
    {
        // Hide text labels on messages route
        $this->hideText = request()->routeIs('messages*');
        
        // Get counts for notifications
        $this->unreadMessages = 0; // TODO: Implement actual unread message count
        $this->totalLikes = 0; // TODO: Implement actual likes count
    }

    public function render()
    {
        return view('livewire.components.unified-sidebar');
    }
}
<?php

namespace App\Livewire\Pages;

use App\Models\Notification;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NotificationsPage extends Component
{
    use WithPagination;

    public $selectedCategory = 'all';
    public $showUnreadOnly = false;
    public $notifications = [];
    public $unreadCount = 0;

    protected $queryString = [
        'selectedCategory' => ['except' => 'all'],
        'showUnreadOnly' => ['except' => false],
    ];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $query = Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->with(['notifiable.profile', 'notifiable.photos']);

        // Filter by category
        if ($this->selectedCategory !== 'all') {
            $query->where('type', $this->selectedCategory);
        }

        // Filter by read status
        if ($this->showUnreadOnly) {
            $query->where('read_at', null);
        }

        $this->notifications = $query->orderBy('created_at', 'desc')->get();
        $this->unreadCount = Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->where('read_at', null)->count();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
        $this->loadNotifications();
    }

    public function updatedShowUnreadOnly()
    {
        $this->resetPage();
        $this->loadNotifications();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->first();

        if ($notification && !$notification->read_at) {
            $notification->update(['read_at' => now()]);
            $this->loadNotifications();
            
            $this->dispatch('notification-marked-read', notificationId: $notificationId);
        }
    }

    public function markAllAsRead()
    {
        Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->where('read_at', null)
            ->update(['read_at' => now()]);

        $this->loadNotifications();
        
        $this->dispatch('all-notifications-marked-read');
        session()->flash('success', 'All notifications marked as read');
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->first();

        if ($notification) {
            $notification->delete();
            $this->loadNotifications();
            
            $this->dispatch('notification-deleted', notificationId: $notificationId);
        }
    }

    public function clearAllRead()
    {
        $deletedCount = Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', Auth::id())
            ->whereNotNull('read_at')
            ->delete();

        $this->loadNotifications();
        
        session()->flash('success', "Cleared {$deletedCount} read notifications");
    }

    public function getCategories()
    {
        return [
            'all' => 'All Notifications',
            'match' => 'New Matches',
            'message' => 'Messages',
            'like' => 'Likes & Super Likes',
            'view' => 'Profile Views',
            'system' => 'System Notifications',
        ];
    }

    public function getNotificationIcon($type)
    {
        return match($type) {
            'match' => 'heart',
            'message' => 'message-circle',
            'like' => 'thumbs-up',
            'view' => 'eye',
            'system' => 'bell',
            default => 'bell'
        };
    }

    public function getNotificationColor($type)
    {
        return match($type) {
            'match' => 'text-pink-500',
            'message' => 'text-blue-500',
            'like' => 'text-purple-500',
            'view' => 'text-green-500',
            'system' => 'text-gray-500',
            default => 'text-gray-500'
        };
    }

    public function render()
    {
        return view('livewire.pages.notifications-page')
            ->layout('layouts.app', ['title' => 'Notifications']);
    }
}

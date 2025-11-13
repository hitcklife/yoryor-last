<?php

namespace App\Livewire\Admin;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Chats extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $sortBy = 'last_activity_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'sortBy' => ['except' => 'last_activity_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function viewChatMessages($chatId)
    {
        return redirect()->route('admin.chat.details', $chatId);
    }

    public function deleteChat($chatId)
    {
        $chat = Chat::findOrFail($chatId);
        $chat->delete();
        
        $this->dispatch('chat-deleted', [
            'message' => 'Chat deleted successfully'
        ]);
    }

    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->delete();
        
        // Refresh messages
        if ($this->selectedChat) {
            $this->messages = $this->selectedChat->messages()
                                   ->with('sender.profile')
                                   ->orderBy('sent_at', 'desc')
                                   ->take(50)
                                   ->get()
                                   ->reverse();
        }
        
        $this->dispatch('message-deleted', [
            'message' => 'Message deleted successfully'
        ]);
    }

    public function render()
    {
        $chatsQuery = Chat::with(['users.profile', 'lastMessage.sender.profile'])
            ->when($this->search, function($query) {
                $query->whereHas('users.profile', function($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->orWhereHas('users', function($q) {
                    $q->where('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                })
                ->orWhere('name', 'like', '%' . $this->search . '%');
            })
            ->when($this->typeFilter, function($query) {
                $query->where('type', $this->typeFilter);
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        $chats = $chatsQuery->paginate($this->perPage);

        // Statistics
        $totalChats = Chat::count();
        $activeChats = Chat::where('is_active', true)->count();
        $privateChats = Chat::where('type', 'private')->count();
        $groupChats = Chat::where('type', 'group')->count();
        $totalMessages = Message::count();
        $messagesToday = Message::whereDate('sent_at', today())->count();

        return view('livewire.admin.chats', [
            'chats' => $chats,
            'totalChats' => $totalChats,
            'activeChats' => $activeChats,
            'privateChats' => $privateChats,
            'groupChats' => $groupChats,
            'totalMessages' => $totalMessages,
            'messagesToday' => $messagesToday,
        ])->layout('components.layouts.admin', ['title' => 'Chats Management']);
    }
}
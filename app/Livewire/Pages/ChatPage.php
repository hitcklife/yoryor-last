<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;

class ChatPage extends Component
{
    public $conversationId;
    public $messages = [];
    public $newMessage = '';
    public $otherUser;
    public $chat;

    public function mount($conversationId)
    {
        $this->conversationId = $conversationId;
        $this->loadChat();
        $this->loadMessages();
    }

    public function loadChat()
    {
        $user = Auth::user();
        $this->otherUser = User::with(['profile', 'profilePhoto'])->findOrFail($this->conversationId);
        
        // Find or create chat between users
        $this->chat = Chat::whereHas('users', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->whereHas('users', function($query) {
            $query->where('user_id', $this->otherUser->id);
        })->where('type', 'private')->first();
        
        if (!$this->chat) {
            // Create new chat
            $this->chat = Chat::create([
                'type' => 'private',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Attach users to chat
            $this->chat->users()->attach([
                $user->id => ['joined_at' => now()],
                $this->otherUser->id => ['joined_at' => now()]
            ]);
        }
    }

    public function loadMessages()
    {
        if ($this->chat) {
            $this->messages = Message::where('chat_id', $this->chat->id)
                ->with('sender:id,email', 'sender.profile:user_id,first_name,last_name')
                ->orderBy('sent_at', 'asc')
                ->get()
                ->map(function($message) {
                    return [
                        'id' => $message->id,
                        'content' => $message->content,
                        'sender_id' => $message->sender_id,
                        'sender_name' => $message->sender->profile?->first_name ?? 'User',
                        'is_mine' => $message->sender_id === Auth::id(),
                        'sent_at' => $message->sent_at ?? $message->created_at,
                        'time' => ($message->sent_at ?? $message->created_at)->format('H:i')
                    ];
                })->toArray();
        }
    }

    public function sendMessage()
    {
        $this->validate([
            'newMessage' => 'required|string|max:1000'
        ]);

        if ($this->chat) {
            Message::create([
                'chat_id' => $this->chat->id,
                'sender_id' => Auth::id(),
                'content' => $this->newMessage,
                'message_type' => 'text',
                'sent_at' => now()
            ]);

            $this->newMessage = '';
            $this->loadMessages();
            
            // Dispatch browser event to scroll to bottom
            $this->dispatch('messageSent');
        }
    }

    public function render()
    {
        return view('livewire.pages.chat-page')->layout('layouts.app-sidebar');
    }
}
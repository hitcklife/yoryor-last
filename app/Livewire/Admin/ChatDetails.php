<?php

namespace App\Livewire\Admin;

use App\Models\Chat;
use App\Models\Message;
use Livewire\Component;
use Livewire\WithPagination;

class ChatDetails extends Component
{
    use WithPagination;

    public $chatId;
    public $chat;
    public $perPage = 50;
    public $messageFilter = '';

    public function mount($chatId)
    {
        $this->chatId = $chatId;
        $this->chat = Chat::with(['users.profile'])->findOrFail($chatId);
    }

    public function deleteMessage($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->delete();
        
        $this->dispatch('message-deleted', [
            'message' => 'Message deleted successfully'
        ]);
    }

    public function deleteChat()
    {
        $this->chat->delete();
        
        return redirect()->route('admin.chats')->with('success', 'Chat deleted successfully');
    }

    public function render()
    {
        $messagesQuery = Message::where('chat_id', $this->chatId)
            ->with(['sender.profile'])
            ->when($this->messageFilter, function($query) {
                $query->where(function($q) {
                    $q->where('content', 'like', '%' . $this->messageFilter . '%')
                      ->orWhere('message_type', 'like', '%' . $this->messageFilter . '%')
                      ->orWhereHas('sender.profile', function($sq) {
                          $sq->where('first_name', 'like', '%' . $this->messageFilter . '%')
                            ->orWhere('last_name', 'like', '%' . $this->messageFilter . '%');
                      });
                });
            })
            ->orderBy('sent_at', 'desc');

        $messages = $messagesQuery->paginate($this->perPage);

        // Chat statistics
        $totalMessages = Message::where('chat_id', $this->chatId)->count();
        $messagesToday = Message::where('chat_id', $this->chatId)
                               ->whereDate('sent_at', today())->count();
        $messagesThisWeek = Message::where('chat_id', $this->chatId)
                                  ->whereBetween('sent_at', [now()->startOfWeek(), now()->endOfWeek()])
                                  ->count();
        
        // Message types breakdown
        $messageTypes = Message::where('chat_id', $this->chatId)
                              ->selectRaw('message_type, COUNT(*) as count')
                              ->groupBy('message_type')
                              ->get();

        return view('livewire.admin.chat-details', [
            'messages' => $messages,
            'totalMessages' => $totalMessages,
            'messagesToday' => $messagesToday,
            'messagesThisWeek' => $messagesThisWeek,
            'messageTypes' => $messageTypes,
        ])->layout('components.layouts.admin', ['title' => 'Chat Details - ' . ($this->chat->name ?: 'Private Chat')]);
    }
}
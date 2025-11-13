<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;
use App\Models\User;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $sender;
    public $chatId;

    public function __construct(Message $message, User $sender, $chatId)
    {
        $this->message = $message;
        $this->sender = $sender;
        $this->chatId = $chatId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->sender->profile?->first_name ?? 'User',
            'is_mine' => false, // Will be determined on frontend
            'sent_at' => $this->message->sent_at ?? $this->message->created_at,
            'time' => ($this->message->sent_at ?? $this->message->created_at)->format('H:i')
        ];
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }
}
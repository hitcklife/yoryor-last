<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReadEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The chat instance.
     *
     * @var \App\Models\Chat
     */
    public $chat;

    /**
     * The user who read the messages.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The number of messages marked as read.
     *
     * @var int
     */
    public $count;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Chat  $chat
     * @param  \App\Models\User  $user
     * @param  int  $count
     * @return void
     */
    public function __construct(Chat $chat, User $user, int $count)
    {
        $this->chat = $chat;
        $this->user = $user;
        $this->count = $count;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chat->id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'messages.read';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'chat_id' => $this->chat->id,
            'user_id' => $this->user->id,
            'count' => $this->count,
            'timestamp' => now()->toIso8601String()
        ];
    }
}

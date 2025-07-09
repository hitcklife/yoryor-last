<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTypingStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $chatId;
    public $isTyping;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, int $chatId, bool $isTyping)
    {
        $this->user = $user;
        $this->chatId = $chatId;
        $this->isTyping = $isTyping;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Only broadcast to the presence channel since it provides more information
        // and all active users in the chat should be subscribed to it
        return [
            new PresenceChannel('presence-chat.' . $this->chatId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->full_name,
            'user_avatar' => $this->user->profile_photo_path,
            'chat_id' => $this->chatId,
            'is_typing' => $this->isTyping,
            'timestamp' => $this->timestamp->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing.status.changed';
    }
}

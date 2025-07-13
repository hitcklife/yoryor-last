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

class UserOnlineStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $isOnline;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, bool $isOnline)
    {
        $this->user = $user;
        $this->isOnline = $isOnline;
        $this->timestamp = now();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Broadcast to the user's matches/friends about their status change
        $channels[] = new PrivateChannel('presence-user-matches.' . $this->user->id);

        // Broadcast to general presence channel
        $channels[] = new PresenceChannel('presence-online-users');

        // Only broadcast to active chats to reduce overhead
        // Get only the most recent/active chats (limit to 5 most recent)
        $recentChatIds = $this->user->chats()
            ->orderBy('chat_user.updated_at', 'desc')
            ->limit(5)
            ->pluck('chats.id');

        foreach ($recentChatIds as $chatId) {
            $channels[] = new PrivateChannel('presence-chat.' . $chatId);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->full_name,
            'user_avatar' => $this->user->getProfilePhotoUrl(),
            'is_online' => $this->isOnline,
            'last_active_at' => $this->user->last_active_at?->toISOString(),
            'status_changed_at' => $this->timestamp->toISOString(),
            'status' => $this->isOnline ? 'online' : 'offline'
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.online.status.changed';
    }
}

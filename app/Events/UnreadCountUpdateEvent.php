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

class UnreadCountUpdateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user whose unread count was updated.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The total unread messages count.
     *
     * @var int
     */
    public $totalUnreadCount;

    /**
     * The chat ID where the count changed (optional).
     *
     * @var int|null
     */
    public $chatId;

    /**
     * The unread count for the specific chat (optional).
     *
     * @var int|null
     */
    public $chatUnreadCount;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user
     * @param int $totalUnreadCount
     * @param int|null $chatId
     * @param int|null $chatUnreadCount
     * @return void
     */
    public function __construct(User $user, int $totalUnreadCount, ?int $chatId = null, ?int $chatUnreadCount = null)
    {
        $this->user = $user;
        $this->totalUnreadCount = $totalUnreadCount;
        $this->chatId = $chatId;
        $this->chatUnreadCount = $chatUnreadCount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->user->id)
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'UnreadCountUpdate';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $data = [
            'total_unread_count' => $this->totalUnreadCount,
            'timestamp' => now()->toIso8601String()
        ];

        if ($this->chatId) {
            $data['chat_id'] = $this->chatId;
            $data['chat_unread_count'] = $this->chatUnreadCount;
        }

        return $data;
    }
}

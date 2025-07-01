<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeletedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message ID that was deleted.
     *
     * @var int
     */
    public $messageId;

    /**
     * The chat ID where the message was deleted.
     *
     * @var int
     */
    public $chatId;

    /**
     * The user who deleted the message.
     *
     * @var int
     */
    public $deletedByUserId;

    /**
     * Create a new event instance.
     *
     * @param  int  $messageId
     * @param  int  $chatId
     * @param  int  $deletedByUserId
     * @return void
     */
    public function __construct(int $messageId, int $chatId, int $deletedByUserId)
    {
        $this->messageId = $messageId;
        $this->chatId = $chatId;
        $this->deletedByUserId = $deletedByUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chatId);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'MessageDeleted';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message_id' => $this->messageId,
            'chat_id' => $this->chatId,
            'deleted_by_user_id' => $this->deletedByUserId,
            'deleted_at' => now()->toIso8601String()
        ];
    }
} 
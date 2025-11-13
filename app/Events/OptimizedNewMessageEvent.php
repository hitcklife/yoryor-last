<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OptimizedNewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $message;
    public $chatId;
    public $recipientId;

    /**
     * Create a new event instance.
     */
    public function __construct($message, $chatId, $recipientId)
    {
        $this->message = $message;
        $this->chatId = $chatId;
        $this->recipientId = $recipientId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Optimized to broadcast only to necessary channels
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatId),
            new PrivateChannel('user.' . $this->recipientId)
        ];
    }

    /**
     * Get the data to broadcast.
     * Optimized payload - only essential data
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->chatId,
                'sender_id' => $this->message->sender_id,
                'content' => $this->truncateContent($this->message->content),
                'message_type' => $this->message->message_type,
                'sent_at' => $this->message->sent_at->toISOString(),
                'sender_name' => $this->getSenderName()
            ],
            'chat_id' => $this->chatId,
            'type' => 'new_message'
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.new';
    }

    /**
     * Determine if this event should broadcast.
     */
    public function broadcastWhen(): bool
    {
        // Don't broadcast if message is from a blocked user
        return !\App\Models\UserBlock::where('user_id', $this->recipientId)
            ->where('blocked_user_id', $this->message->sender_id)
            ->exists();
    }

    /**
     * Get sender name efficiently
     */
    private function getSenderName(): string
    {
        // Use cached sender name if available
        $cacheKey = "user_name:{$this->message->sender_id}";
        return cache()->remember($cacheKey, 3600, function () {
            $profile = \App\Models\Profile::where('user_id', $this->message->sender_id)
                ->select('first_name', 'last_name')
                ->first();
            
            return $profile ? trim("{$profile->first_name} {$profile->last_name}") : 'User';
        });
    }

    /**
     * Truncate content for notification
     */
    private function truncateContent($content): string
    {
        switch ($this->message->message_type) {
            case 'image':
                return '📷 Photo';
            case 'video':
                return '📹 Video';
            case 'voice':
                return '🎤 Voice message';
            case 'call':
                return '📞 Call';
            default:
                return mb_strlen($content) > 100 ? mb_substr($content, 0, 100) . '...' : $content;
        }
    }
}
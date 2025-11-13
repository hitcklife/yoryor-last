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

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance.
     *
     * @var \App\Models\Message
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        // Load the sender relationship for the response
        $this->message->load([
            'sender:id,email,phone,google_id,facebook_id,email_verified_at,phone_verified_at,disabled_at,registration_completed,is_admin,is_private,last_active_at,deleted_at,created_at,updated_at,two_factor_enabled,last_login_at',
            'sender.profile:id,user_id,first_name,last_name',
            'sender.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo'
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Get all users in the chat to broadcast to their private channels
        $chat = $this->message->chat;
        $userIds = $chat->users()->pluck('user_id')->toArray();

        $channels = [
            new PrivateChannel('chat.' . $this->message->chat_id)
        ];

        // Add a channel for each user in the chat
        foreach ($userIds as $userId) {
            // Skip the sender to avoid duplicate notifications
            if ($userId != $this->message->sender_id) {
                $channels[] = new PrivateChannel('user.' . $userId);
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        // Use 'NewMessage' for user channels to match NewLike pattern
        return 'NewMessage';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Load the chat relationship for the response
        $this->message->load('chat:id,name,created_at,updated_at,last_activity_at');

        // Get sender profile photo URL
        $senderPhotoUrl = null;
        if ($this->message->sender && $this->message->sender->profilePhoto) {
            $senderPhotoUrl = $this->message->sender->profilePhoto->thumbnail_url;
        }

        // Get sender's full name
        $senderName = null;
        if ($this->message->sender && $this->message->sender->profile) {
            $senderName = $this->message->sender->profile->first_name . ' ' . $this->message->sender->profile->last_name;
        }

        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'type' => $this->message->type,
                'metadata' => $this->message->metadata,
                'is_read' => $this->message->is_read,
                'created_at' => $this->message->created_at,
                'updated_at' => $this->message->updated_at,
            ],
            'sender' => [
                'id' => $this->message->sender_id,
                'name' => $senderName,
                'profile_photo' => $senderPhotoUrl,
            ],
            'chat' => [
                'id' => $this->message->chat_id,
                'name' => $this->message->chat->name ?? null,
                'last_activity_at' => $this->message->chat->last_activity_at ?? null,
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}

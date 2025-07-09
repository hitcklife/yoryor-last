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

class GeneralNotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The user who should receive the notification.
     *
     * @var \App\Models\User
     */
    public $user;

    /**
     * The notification type.
     *
     * @var string
     */
    public $type;

    /**
     * The notification title.
     *
     * @var string
     */
    public $title;

    /**
     * The notification message.
     *
     * @var string
     */
    public $message;

    /**
     * Additional data for the notification.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array $data
     * @return void
     */
    public function __construct(User $user, string $type, string $title, string $message, array $data = [])
    {
        $this->user = $user;
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
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
        return 'GeneralNotification';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'notification' => [
                'id' => uniqid(),
                'type' => $this->type,
                'title' => $this->title,
                'message' => $this->message,
                'data' => $this->data,
                'created_at' => now()->toIso8601String()
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}

<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiatedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The call instance.
     *
     * @var \App\Models\Call
     */
    public $call;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Call  $call
     * @return void
     */
    public function __construct(Call $call)
    {
        $this->call = $call;

        // Load the relationships for the response if not already loaded
        if (!$this->call->relationLoaded('caller') || !$this->call->relationLoaded('receiver')) {
            $this->call->load(['caller:id,email,profile_photo_path', 'receiver:id,email,profile_photo_path']);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('private-user.' . $this->call->receiver_id);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'CallInitiated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'call' => [
                'id' => $this->call->id,
                'channel_name' => $this->call->channel_name,
                'type' => $this->call->type,
                'status' => $this->call->status,
                'caller' => [
                    'id' => $this->call->caller->id,
                    'email' => $this->call->caller->email,
                    'profile_photo_path' => $this->call->caller->profile_photo_path,
                ],
                'created_at' => $this->call->created_at,
            ]
        ];
    }
}

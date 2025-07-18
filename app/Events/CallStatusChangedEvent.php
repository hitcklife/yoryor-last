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

class CallStatusChangedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The call instance.
     *
     * @var \App\Models\Call
     */
    public $call;

    /**
     * The user who changed the call status.
     *
     * @var int
     */
    public $changedBy;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Call  $call
     * @param  int  $changedBy  User ID who changed the status
     * @return void
     */
    public function __construct(Call $call, int $changedBy)
    {
        $this->call = $call;
        $this->changedBy = $changedBy;

        // Load the relationships for the response if not already loaded
        if (!$this->call->relationLoaded('caller') || !$this->call->relationLoaded('receiver')) {
            $this->call->load(['caller:id,name', 'receiver:id,name', 'caller.profilePhoto', 'receiver.profilePhoto']);
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Broadcast to both caller and receiver, excluding the user who changed the status
        $channels = [];

        if ($this->changedBy !== $this->call->caller_id) {
            $channels[] = new PrivateChannel('user.' . $this->call->caller_id);
        }

        if ($this->changedBy !== $this->call->receiver_id) {
            $channels[] = new PrivateChannel('user.' . $this->call->receiver_id);
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
        return 'CallStatusChanged';
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
                'started_at' => $this->call->started_at,
                'ended_at' => $this->call->ended_at,
                'caller' => [
                    'id' => $this->call->caller->id,
                    'name' => $this->call->caller->full_name,
                    'profile_photo_url' => $this->call->caller->getProfilePhotoUrl(),
                ],
                'receiver' => [
                    'id' => $this->call->receiver->id,
                    'name' => $this->call->receiver->full_name,
                    'profile_photo_url' => $this->call->receiver->getProfilePhotoUrl(),
                ],
                'changed_by' => $this->changedBy,
                'updated_at' => $this->call->updated_at,
            ]
        ];
    }
}

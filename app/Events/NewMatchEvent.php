<?php

namespace App\Events;

use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMatchEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The match instance.
     *
     * @var \App\Models\MatchModel
     */
    public $match;

    /**
     * The user who initiated the match (who liked last).
     *
     * @var \App\Models\User
     */
    public $initiator;

    /**
     * The user who received the match.
     *
     * @var \App\Models\User
     */
    public $receiver;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\MatchModel $match
     * @param \App\Models\User $initiator
     * @param \App\Models\User $receiver
     * @return void
     */
    public function __construct(MatchModel $match, User $initiator, User $receiver)
    {
        $this->match = $match;
        $this->initiator = $initiator;
        $this->receiver = $receiver;

        // Load necessary relationships
        $this->match->load(['user.profile', 'matchedUser.profile']);
        $this->initiator->load(['profile', 'profilePhoto']);
        $this->receiver->load(['profile', 'profilePhoto']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('user.' . $this->initiator->id),
            new PrivateChannel('user.' . $this->receiver->id)
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'NewMatch';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'match' => [
                'id' => $this->match->id,
                'matched_at' => $this->match->matched_at,
                'initiator' => [
                    'id' => $this->initiator->id,
                    'name' => $this->initiator->full_name,
                    'profile_photo' => $this->initiator->profilePhoto?->thumbnail_url ?? $this->initiator->profile_photo_path,
                    'age' => $this->initiator->age,
                    'city' => $this->initiator->profile?->city,
                ],
                'receiver' => [
                    'id' => $this->receiver->id,
                    'name' => $this->receiver->full_name,
                    'profile_photo' => $this->receiver->profilePhoto?->thumbnail_url ?? $this->receiver->profile_photo_path,
                    'age' => $this->receiver->age,
                    'city' => $this->receiver->profile?->city,
                ]
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}

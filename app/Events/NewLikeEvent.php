<?php

namespace App\Events;

use App\Models\Like;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewLikeEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The like instance.
     *
     * @var \App\Models\Like
     */
    public $like;

    /**
     * The user who gave the like.
     *
     * @var \App\Models\User
     */
    public $liker;

    /**
     * The user who received the like.
     *
     * @var \App\Models\User
     */
    public $likedUser;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Like $like
     * @param \App\Models\User $liker
     * @param \App\Models\User $likedUser
     * @return void
     */
    public function __construct(Like $like, User $liker, User $likedUser)
    {
        $this->like = $like;
        $this->liker = $liker;
        $this->likedUser = $likedUser;

        // Load necessary relationships
        $this->liker->load(['profile', 'profilePhoto']);
        $this->likedUser->load(['profile', 'profilePhoto']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Only broadcast to the user who received the like
        return [
            new PrivateChannel('user.' . $this->likedUser->id)
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'NewLike';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'like' => [
                'id' => $this->like->id,
                'created_at' => $this->like->created_at,
                'liker' => [
                    'id' => $this->liker->id,
                    'name' => $this->liker->full_name,
                    'profile_photo' => $this->liker->getProfilePhotoUrl('thumbnail'),
                    'age' => $this->liker->age,
                    'city' => $this->liker->profile?->city,
                ],
                'is_mutual' => $this->likedUser->hasLiked($this->liker),
            ],
            'timestamp' => now()->toIso8601String()
        ];
    }
}

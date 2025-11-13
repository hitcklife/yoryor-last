<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->when($this->type !== 'private', $this->name),
            'description' => $this->when($this->type !== 'private', $this->description),
            'is_active' => $this->is_active,
            'last_activity_at' => $this->last_activity_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Other user for private chats
            'other_user' => $this->when(
                $this->type === 'private' && $this->relationLoaded('users'),
                function () use ($user) {
                    $otherUser = $this->users->where('id', '!=', $user->id)->first();
                    return $otherUser ? new UserResource($otherUser) : null;
                }
            ),

            // Last message with read status
            'last_message' => $this->when(
                $this->relationLoaded('lastMessage') && $this->lastMessage,
                function () use ($user) {
                    return new MessageResource($this->lastMessage);
                }
            ),

            // Unread count for current user
            'unread_count' => $this->when(
                isset($this->unread_count),
                $this->unread_count ?? 0
            ),

            // All participants for group chats
            'participants' => $this->when(
                $this->type !== 'private' && $this->relationLoaded('users'),
                function () {
                    return UserResource::collection($this->users);
                }
            ),

            // User-specific pivot data (muted status, last read time, etc.)
            'user_settings' => $this->when(
                $user && $this->relationLoaded('users'),
                function () use ($user) {
                    $pivotUser = $this->users->where('id', $user->id)->first();
                    if (!$pivotUser) {
                        return null;
                    }

                    return [
                        'is_muted' => $pivotUser->pivot->is_muted ?? false,
                        'last_read_at' => $pivotUser->pivot->last_read_at,
                        'joined_at' => $pivotUser->pivot->joined_at,
                        'left_at' => $pivotUser->pivot->left_at,
                        'role' => $pivotUser->pivot->role ?? 'member',
                    ];
                }
            ),
        ];
    }
}

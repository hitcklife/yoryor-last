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
        $currentUser = auth()->user();

        return [
            'id' => $this->id,
            'type' => $this->type,

            // Chat Details
            'name' => $this->name,
            'description' => $this->description,

            // Status
            'is_active' => (bool) $this->is_active,
            'last_activity_at' => $this->last_activity_at?->toIso8601String(),

            // For Private Chats - Get the other user
            'other_user' => $this->when(
                $this->type === 'private' && $this->relationLoaded('users'),
                fn() => new UserResource($this->getOtherUser($currentUser))
            ),

            // For Group Chats - Get all participants
            'participants' => $this->when(
                $this->relationLoaded('users'),
                fn() => UserResource::collection($this->users)
            ),

            // Active participants (not left the chat)
            'active_participants' => $this->when(
                $this->relationLoaded('activeUsers'),
                fn() => UserResource::collection($this->activeUsers)
            ),

            // Current user's chat settings
            'is_muted' => $this->when(
                $this->relationLoaded('users') && $currentUser,
                fn() => $this->users->firstWhere('id', $currentUser->id)?->pivot?->is_muted ?? false
            ),
            'last_read_at' => $this->when(
                $this->relationLoaded('users') && $currentUser,
                fn() => $this->users->firstWhere('id', $currentUser->id)?->pivot?->last_read_at?->toIso8601String()
            ),
            'joined_at' => $this->when(
                $this->relationLoaded('users') && $currentUser,
                fn() => $this->users->firstWhere('id', $currentUser->id)?->pivot?->joined_at?->toIso8601String()
            ),
            'role' => $this->when(
                $this->relationLoaded('users') && $currentUser,
                fn() => $this->users->firstWhere('id', $currentUser->id)?->pivot?->role
            ),

            // Last Message
            'last_message' => $this->when(
                $this->relationLoaded('lastMessage') && $this->lastMessage,
                fn() => new MessageResource($this->lastMessage)
            ),

            // Messages (when loaded)
            'messages' => $this->when(
                $this->relationLoaded('messages'),
                fn() => MessageResource::collection($this->messages)
            ),

            // Unread count for current user
            'unread_count' => $this->when(
                $currentUser,
                fn() => (int) $this->getUnreadCountForUser($currentUser)
            ),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}

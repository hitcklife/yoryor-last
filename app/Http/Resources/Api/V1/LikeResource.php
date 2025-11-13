<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LikeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'liked_user_id' => $this->liked_user_id,
            'liked_at' => $this->when(isset($this->liked_at), $this->liked_at),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // User who initiated the like
            'user' => $this->when(
                $this->relationLoaded('user'),
                function () {
                    return new UserResource($this->user);
                }
            ),

            // User who was liked
            'liked_user' => $this->when(
                $this->relationLoaded('likedUser'),
                function () {
                    return new UserResource($this->likedUser);
                }
            ),
        ];
    }
}

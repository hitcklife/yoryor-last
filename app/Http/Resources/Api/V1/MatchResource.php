<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchResource extends JsonResource
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
            'matched_user_id' => $this->matched_user_id,
            'matched_at' => $this->matched_at?->toIso8601String(),

            // User Relationships (when loaded)
            'user' => new UserResource($this->whenLoaded('user')),
            'matched_user' => new UserResource($this->whenLoaded('matchedUser')),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

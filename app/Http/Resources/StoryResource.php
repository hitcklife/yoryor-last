<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoryResource extends JsonResource
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
            'media_url' => $this->media_url,
            'thumbnail_url' => $this->thumbnail_url,
            'type' => $this->type,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'status' => $this->status,
            'is_expired' => $this->isExpired(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}

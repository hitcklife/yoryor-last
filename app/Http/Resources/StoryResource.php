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
            'type' => 'stories',
            'id' => (string) $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'media_url' => $this->media_url,
                'thumbnail_url' => $this->thumbnail_url,
                'type' => $this->type,
                'caption' => $this->caption,
                'created_at' => $this->created_at,
                'expires_at' => $this->expires_at,
                'status' => $this->status,
                'is_expired' => $this->isExpired(),
            ],
            'relationships' => [
                'user' => $this->when($this->relationLoaded('user'), function () {
                    return [
                        'data' => [
                            'type' => 'users',
                            'id' => (string) $this->user_id,
                        ],
                    ];
                }),
            ],
            'included' => $this->when($this->relationLoaded('user'), function () {
                $included = [];

                if ($this->user) {
                    // Only include non-sensitive user information
                    $included[] = [
                        'type' => 'users',
                        'id' => (string) $this->user->id,
                        'attributes' => [
                            'profile_photo_url' => $this->user->getProfilePhotoUrl(),
                            'is_online' => $this->user->last_active_at && $this->user->last_active_at->greaterThan(now()->subMinutes(5)),
                            'full_name' => $this->user->relationLoaded('profile') && $this->user->profile
                                ? trim($this->user->profile->first_name . ' ' . $this->user->profile->last_name) ?: null
                                : null,
                        ],
                    ];
                }

                return $included;
            }),
        ];
    }
}

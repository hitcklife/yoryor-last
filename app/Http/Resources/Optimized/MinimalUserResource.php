<?php

namespace App\Http\Resources\Optimized;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MinimalUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Returns only essential user data for listings
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name ?? trim("{$this->first_name} {$this->last_name}"),
            'avatar' => $this->when($this->profile_photo_thumbnail, $this->profile_photo_thumbnail),
            'is_online' => $this->isOnline(),
            $this->mergeWhen($request->route()->getName() === 'matches.potential', [
                'age' => $this->age,
                'bio' => $this->when($this->bio, fn() => mb_substr($this->bio, 0, 100)),
                'location' => $this->when($this->city, $this->city)
            ])
        ];
    }

    /**
     * Check if user is online
     */
    private function isOnline(): bool
    {
        return $this->last_active_at && 
               now()->diffInMinutes($this->last_active_at) < 5;
    }
}
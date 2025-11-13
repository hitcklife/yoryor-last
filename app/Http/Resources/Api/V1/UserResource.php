<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'uuid' => $this->profile_uuid,
            'name' => $this->name,
            'email' => $this->when($this->id === $request->user()?->id, $this->email),
            'phone' => $this->when($this->id === $request->user()?->id, $this->phone),
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'age' => $this->age,

            // Profile photo
            'profile_photo' => $this->getProfilePhotoUrl(),
            'profile_photo_url' => $this->getProfilePhotoUrl(),

            // Status flags
            'is_online' => $this->isOnline(),
            'is_active' => $this->is_active,
            'is_verified' => $this->email_verified_at !== null,
            'registration_completed' => $this->registration_completed,

            // Activity
            'last_active_at' => $this->last_active_at?->toIso8601String(),
            'last_login_at' => $this->when($this->id === $request->user()?->id, $this->last_login_at?->toIso8601String()),

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Conditionally include relationships (when eager loaded)
            'profile' => $this->when($this->relationLoaded('profile'), fn() => $this->profile),
            'photos' => $this->when($this->relationLoaded('photos'), fn() => $this->photos),
            'preferences' => $this->when($this->relationLoaded('preference'), fn() => $this->preference),

            // Online status details (when requested)
            'online_status' => $this->when(
                $request->input('include_online_status'),
                fn() => $this->getOnlineStatus()
            ),
        ];
    }
}

<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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

            // Basic Information
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'age' => $this->age,

            // Professional
            'occupation' => $this->occupation,
            'profession' => $this->profession,
            'status' => $this->status,

            // Personal
            'bio' => $this->bio,
            'interests' => $this->interests,
            'looking_for_relationship' => $this->looking_for_relationship,

            // Location
            'city' => $this->city,
            'state' => $this->state,
            'province' => $this->province,
            'country_id' => $this->country_id,
            'country_code' => $this->country_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            // Country relationship (when loaded)
            'country' => $this->when($this->relationLoaded('country'), function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
                    'code' => $this->country->code,
                    'flag' => $this->country->flag ?? null,
                ];
            }),

            // Metrics
            'profile_views' => $this->profile_views,
            'profile_completed_at' => $this->profile_completed_at?->toIso8601String(),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Extended Profiles (when loaded)
            'user' => new UserResource($this->whenLoaded('user')),
            'cultural_profile' => $this->whenLoaded('culturalProfile'),
            'career_profile' => $this->whenLoaded('careerProfile'),
            'physical_profile' => $this->whenLoaded('physicalProfile'),
            'family_preference' => $this->whenLoaded('familyPreference'),
            'location_preference' => $this->whenLoaded('locationPreference'),
        ];
    }
}

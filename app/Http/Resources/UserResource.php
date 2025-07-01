<?php

namespace App\Http\Resources;

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
            'type' => 'users',
            'id' => (string) $this->id,
            'attributes' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'profile_photo_path' => $this->profile_photo_path,
                'registration_completed' => $this->registration_completed,
                'is_private' => $this->is_private,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                // Add useful attributes that can be calculated from existing data
                'age' => $this->when($this->relationLoaded('profile'), function() {
                    return $this->profile->date_of_birth ? $this->profile->date_of_birth->age : null;
                }),
                'full_name' => $this->when($this->relationLoaded('profile'), function() {
                    return trim($this->profile->first_name . ' ' . $this->profile->last_name) ?: null;
                }),
                'is_online' => $this->last_active_at && $this->last_active_at->greaterThan(now()->subMinutes(5)),
                'last_active_at' => $this->last_active_at,
            ],
            'included' => $this->when($this->relationLoaded('profile') || $this->relationLoaded('photos') || $this->relationLoaded('profilePhoto'), function () {
                $included = [];
                $includedIds = []; // Track included IDs to avoid duplicates
                if ($this->relationLoaded('profile')) {
                    $included[] = [
                        'type' => 'profiles',
                        'id' => (string) $this->profile->id,
                        'attributes' => [
                            'first_name' => $this->profile->first_name,
                            'last_name' => $this->profile->last_name,
                            'gender' => $this->profile->gender,
                            'date_of_birth' => $this->profile->date_of_birth,
                            'city' => $this->profile->city,
                            'state' => $this->profile->state,
                            'province' => $this->profile->province,
                            'country_id' => $this->profile->country_id,
                            'latitude' => $this->profile->latitude,
                            'longitude' => $this->profile->longitude,
                            'marital_status' => $this->profile->status,
                            'looking_for' => $this->profile->looking_for,
                            'bio' => $this->profile->bio ?? null,
                            'profession' => $this->profile->profession ?? null,
                            'interests' => $this->profile->interests ?? null,
                        ],
                    ];

                    // Include country data if the country relationship is loaded
                    if ($this->profile->country) {
                        $countryId = (string) $this->profile->country->id;

                        // Skip if already included (avoid duplicates)
                        if (!in_array("countries:{$countryId}", $includedIds)) {
                            $includedIds[] = "countries:{$countryId}";

                            $included[] = [
                                'type' => 'countries',
                                'id' => $countryId,
                                'attributes' => [
                                    'name' => $this->profile->country->name,
                                    'code' => $this->profile->country->code,
                                    'flag' => $this->profile->country->flag,
                                    'phone_code' => $this->profile->country->phone_code,
                                    'phone_template' => $this->profile->country->phone_template,
                                ],
                            ];
                        }
                    }

                }

                // Process photos - only include non-private, approved photos
                if ($this->relationLoaded('photos')) {
//                    $filteredPhotos = $this->photos->filter(function ($photo) {
//                        return !$photo->is_private && $photo->status !== 'rejected';
//                    });

                    foreach ($this->photos as $photo) {
                        $photoId = (string) $photo->id;

                        // Skip if already included (avoid duplicates)
                        if (in_array("photos:{$photoId}", $includedIds)) {
                            continue;
                        }

                        $includedIds[] = "photos:{$photoId}";

                        $included[] = [
                            'type' => 'photos',
                            'id' => $photoId,
                            'attributes' => [
                                'user_id' => $photo->user_id,
                                'original_url' => $photo->original_url,
                                'thumbnail_url' => $photo->thumbnail_url,
                                'medium_url' => $photo->medium_url,
                                'is_profile_photo' => $photo->is_profile_photo,
                                'order' => $photo->order,
                                'is_private' => $photo->is_private,
                                'is_verified' => $photo->is_verified,
                                'status' => $photo->status,
                                // Only include essential fields, remove unnecessary ones
                                'uploaded_at' => $photo->uploaded_at,
                            ],
                        ];
                    }
                }

                // Don't separately include profilePhoto as it's already included in photos
                // This avoids duplication in the response

                return $included;
            }),
        ];
    }
}

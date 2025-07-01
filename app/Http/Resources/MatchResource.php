<?php

namespace App\Http\Resources;

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
            'type' => 'matches',
            'id' => (string) $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'matched_user_id' => $this->matched_user_id,
                'matched_at' => $this->matched_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'is_mutual' => $this->when(isset($this->is_mutual), $this->is_mutual),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->user_id,
                    ],
                ],
                'matched_user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->matched_user_id,
                    ],
                ],
                'chat' => $this->when(isset($this->chat), function () {
                    return [
                        'data' => [
                            'type' => 'chats',
                            'id' => (string) $this->chat->id,
                        ],
                    ];
                }),
            ],
            'included' => $this->when(
                $this->relationLoaded('user') || $this->relationLoaded('matchedUser'),
                function () {
                    $included = [];
                    $includedIds = []; // Track included IDs to avoid duplicates

                    // Include user data if loaded
                    if ($this->relationLoaded('user') && $this->user) {
                        $userId = (string) $this->user->id;
                        if (!in_array("users:{$userId}", $includedIds)) {
                            $includedIds[] = "users:{$userId}";
                            $included[] = $this->buildUserIncluded($this->user, $includedIds);
                        }
                    }

                    // Include matched user data if loaded
                    if ($this->relationLoaded('matchedUser') && $this->matchedUser) {
                        $matchedUserId = (string) $this->matchedUser->id;
                        if (!in_array("users:{$matchedUserId}", $includedIds)) {
                            $includedIds[] = "users:{$matchedUserId}";
                            $included[] = $this->buildUserIncluded($this->matchedUser, $includedIds);
                        }
                    }

                    // Include chat data if available
                    if (isset($this->chat)) {
                        $included[] = [
                            'type' => 'chats',
                            'id' => (string) $this->chat->id,
                            'attributes' => [
                                'type' => $this->chat->type ?? 'private',
                                'name' => $this->chat->name ?? null,
                                'created_at' => $this->chat->created_at,
                                'updated_at' => $this->chat->updated_at,
                                'is_active' => $this->chat->is_active ?? true,
                            ],
                        ];
                    }

                    return array_filter($included); // Remove any null entries
                }
            ),
        ];
    }

    /**
     * Build user included data following UserResource pattern
     */
    private function buildUserIncluded($user, &$includedIds): array
    {
        $userIncluded = [
            'type' => 'users',
            'id' => (string) $user->id,
            'attributes' => [
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_photo_path' => $user->profile_photo_path,
                'registration_completed' => $user->registration_completed,
                'is_private' => $user->is_private,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                // Add useful attributes that can be calculated from existing data
                'age' => $user->relationLoaded('profile') && $user->profile && $user->profile->date_of_birth
                    ? $user->profile->date_of_birth->age : null,
                'full_name' => $user->relationLoaded('profile') && $user->profile
                    ? trim($user->profile->first_name . ' ' . $user->profile->last_name) ?: null : null,
                'is_online' => $user->last_active_at && $user->last_active_at->greaterThan(now()->subMinutes(5)),
                'last_active_at' => $user->last_active_at,
            ],
        ];

        // Add profile data if loaded
        if ($user->relationLoaded('profile') && $user->profile) {
            $profileId = (string) $user->profile->id;
            if (!in_array("profiles:{$profileId}", $includedIds)) {
                $includedIds[] = "profiles:{$profileId}";

                $userIncluded['profile'] = [
                    'type' => 'profiles',
                    'id' => $profileId,
                    'attributes' => [
                        'first_name' => $user->profile->first_name,
                        'last_name' => $user->profile->last_name,
                        'gender' => $user->profile->gender,
                        'date_of_birth' => $user->profile->date_of_birth,
                        'city' => $user->profile->city,
                        'state' => $user->profile->state,
                        'province' => $user->profile->province,
                        'country_id' => $user->profile->country_id,
                        'latitude' => $user->profile->latitude,
                        'longitude' => $user->profile->longitude,
                        'marital_status' => $user->profile->status,
                        'looking_for' => $user->profile->looking_for,
                        'bio' => $user->profile->bio ?? null,
                        'profession' => $user->profile->profession ?? null,
                        'interests' => $user->profile->interests ?? null,
                    ],
                ];

                // Include country data if the country relationship is loaded
                if ($user->profile->relationLoaded('country') && $user->profile->country) {
                    $countryId = (string) $user->profile->country->id;
                    if (!in_array("countries:{$countryId}", $includedIds)) {
                        $includedIds[] = "countries:{$countryId}";

                        $userIncluded['country'] = [
                            'type' => 'countries',
                            'id' => $countryId,
                            'attributes' => [
                                'name' => $user->profile->country->name,
                                'code' => $user->profile->country->code,
                                'flag' => $user->profile->country->flag,
                                'phone_code' => $user->profile->country->phone_code,
                                'phone_template' => $user->profile->country->phone_template,
                            ],
                        ];
                    }
                }
            }
        }

        // Process photos - only include non-private, approved photos
        if ($user->relationLoaded('photos') && $user->photos) {
            $filteredPhotos = $user->photos->filter(function ($photo) {
                return !$photo->is_private && $photo->status !== 'rejected';
            });

            $userIncluded['photos'] = [];
            foreach ($filteredPhotos as $photo) {
                $photoId = (string) $photo->id;
                if (!in_array("photos:{$photoId}", $includedIds)) {
                    $includedIds[] = "photos:{$photoId}";

                    $userIncluded['photos'][] = [
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
                            'uploaded_at' => $photo->uploaded_at,
                        ],
                    ];
                }
            }
        }

        return $userIncluded;
    }
}

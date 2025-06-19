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
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'profile' => $this->when($this->relationLoaded('profile'), function () {
                    return [
                        'data' => [
                            'type' => 'profiles',
                            'id' => (string) $this->profile->id,
                        ],
                    ];
                }),
                'photos' => $this->when($this->relationLoaded('photos'), function () {
                    return [
                        'data' => $this->photos->map(function ($photo) {
                            return [
                                'type' => 'photos',
                                'id' => (string) $photo->id,
                            ];
                        }),
                    ];
                }),
                'profilePhoto' => $this->when($this->relationLoaded('profilePhoto'), function () {
                    return [
                        'data' => $this->profilePhoto ? [
                            'type' => 'photos',
                            'id' => (string) $this->profilePhoto->id,
                        ] : null,
                    ];
                }),
            ],
            'included' => $this->when($this->relationLoaded('profile') || $this->relationLoaded('photos') || $this->relationLoaded('profilePhoto'), function () {
                $included = [];

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
                        ],
                    ];
                }

                if ($this->relationLoaded('photos')) {
                    foreach ($this->photos as $photo) {
                        $included[] = [
                            'type' => 'photos',
                            'id' => (string) $photo->id,
                            'attributes' => [
                                'user_id' => $photo->user_id,
                                'path' => $photo->path,
                                'order' => $photo->order,
                                'created_at' => $photo->created_at,
                                'updated_at' => $photo->updated_at,
                            ],
                        ];
                    }
                }

                if ($this->relationLoaded('profilePhoto') && $this->profilePhoto) {
                    $included[] = [
                        'type' => 'photos',
                        'id' => (string) $this->profilePhoto->id,
                        'attributes' => [
                            'user_id' => $this->profilePhoto->user_id,
                            'path' => $this->profilePhoto->path,
                            'order' => $this->profilePhoto->order,
                            'created_at' => $this->profilePhoto->created_at,
                            'updated_at' => $this->profilePhoto->updated_at,
                        ],
                    ];
                }

                return $included;
            }),
        ];
    }
}

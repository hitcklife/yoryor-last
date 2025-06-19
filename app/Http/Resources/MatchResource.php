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
            'included' => $this->when($this->relationLoaded('matchedUser'), function () {
                return [
                    [
                        'type' => 'users',
                        'id' => (string) $this->matchedUser->id,
                        'attributes' => [
                            'email' => $this->matchedUser->email,
                            'profile_photo_path' => $this->matchedUser->profile_photo_path,
                            'created_at' => $this->matchedUser->created_at,
                            'updated_at' => $this->matchedUser->updated_at,
                        ],
                        'relationships' => $this->when($this->matchedUser->relationLoaded('profile'), function () {
                            return [
                                'profile' => [
                                    'data' => [
                                        'type' => 'profiles',
                                        'id' => (string) $this->matchedUser->profile->id,
                                    ],
                                ],
                            ];
                        }),
                    ],
                    $this->when($this->matchedUser->relationLoaded('profile'), function () {
                        return [
                            'type' => 'profiles',
                            'id' => (string) $this->matchedUser->profile->id,
                            'attributes' => [
                                'first_name' => $this->matchedUser->profile->first_name,
                                'last_name' => $this->matchedUser->profile->last_name,
                                'gender' => $this->matchedUser->profile->gender,
                                'date_of_birth' => $this->matchedUser->profile->date_of_birth,
                                'city' => $this->matchedUser->profile->city,
                                'state' => $this->matchedUser->profile->state,
                                'province' => $this->matchedUser->profile->province,
                            ],
                        ];
                    }),
                ];
            }),
        ];
    }
}

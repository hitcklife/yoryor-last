<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'registration_completed' => $this->registration_completed,
            'profile_photo_path' => $this->profile_photo_path,
            'profile' => new ProfileResource($this->whenLoaded('profile')),
            'preference' => new PreferenceResource($this->whenLoaded('preference')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

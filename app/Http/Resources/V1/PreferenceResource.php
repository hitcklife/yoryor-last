<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PreferenceResource extends JsonResource
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
            'user_id' => $this->user_id,
            'search_radius' => $this->search_radius,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'preferred_genders' => $this->preferred_genders,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

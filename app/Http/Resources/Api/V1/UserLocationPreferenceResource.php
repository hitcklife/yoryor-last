<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLocationPreferenceResource extends JsonResource
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
            'immigration_status' => $this->immigration_status,
            'years_in_current_country' => $this->years_in_current_country,
            'plans_to_return_uzbekistan' => $this->plans_to_return_uzbekistan,
            'uzbekistan_visit_frequency' => $this->uzbekistan_visit_frequency,
            'willing_to_relocate' => $this->willing_to_relocate,
            'relocation_countries' => $this->relocation_countries,
            'preferred_locations' => $this->preferred_locations,
            'live_with_family' => $this->live_with_family,
            'future_location_plans' => $this->future_location_plans,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

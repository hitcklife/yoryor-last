<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCulturalProfileResource extends JsonResource
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
            'native_languages' => $this->native_languages,
            'spoken_languages' => $this->spoken_languages,
            'preferred_communication_language' => $this->preferred_communication_language,
            'religion' => $this->religion,
            'religiousness_level' => $this->religiousness_level,
            'ethnicity' => $this->ethnicity,
            'uzbek_region' => $this->uzbek_region,
            'lifestyle_type' => $this->lifestyle_type,
            'gender_role_views' => $this->gender_role_views,
            'traditional_clothing_comfort' => $this->traditional_clothing_comfort,
            'uzbek_cuisine_knowledge' => $this->uzbek_cuisine_knowledge,
            'cultural_events_participation' => $this->cultural_events_participation,
            'halal_lifestyle' => $this->halal_lifestyle,

            // Prayer/religious practice fields
            'observes_ramadan' => $this->observes_ramadan,
            'prefers_halal_dates' => $this->prefers_halal_dates,
            'mosque_attendance' => $this->mosque_attendance,
            'quran_reading' => $this->quran_reading,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

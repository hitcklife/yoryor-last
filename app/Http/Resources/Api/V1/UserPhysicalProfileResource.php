<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPhysicalProfileResource extends JsonResource
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
            'height' => $this->height,
            'weight' => $this->weight,
            'smoking_habit' => $this->smoking_habit,
            'drinking_habit' => $this->drinking_habit,
            'exercise_frequency' => $this->exercise_frequency,
            'diet_preference' => $this->diet_preference,
            'pet_preference' => $this->pet_preference,
            'hobbies' => $this->hobbies,
            'sleep_schedule' => $this->sleep_schedule,

            // Legacy fields
            'fitness_level' => $this->when(isset($this->fitness_level), $this->fitness_level),
            'dietary_restrictions' => $this->when(isset($this->dietary_restrictions), $this->dietary_restrictions),
            'smoking_status' => $this->when(isset($this->smoking_status), $this->smoking_status),
            'drinking_status' => $this->when(isset($this->drinking_status), $this->drinking_status),
            'diet' => $this->when(isset($this->diet), $this->diet),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

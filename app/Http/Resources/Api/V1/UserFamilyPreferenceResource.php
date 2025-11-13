<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserFamilyPreferenceResource extends JsonResource
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
            'marriage_intention' => $this->marriage_intention,
            'children_preference' => $this->children_preference,
            'current_children' => $this->current_children,
            'family_values' => $this->family_values,
            'living_situation' => $this->living_situation,
            'family_involvement' => $this->family_involvement,
            'marriage_timeline' => $this->marriage_timeline,
            'family_importance' => $this->family_importance,
            'family_approval_important' => $this->family_approval_important,
            'previous_marriages' => $this->previous_marriages,
            'homemaker_preference' => $this->homemaker_preference,

            // Legacy fields
            'number_of_children_wanted' => $this->when(isset($this->number_of_children_wanted), $this->number_of_children_wanted),
            'living_with_family' => $this->when(isset($this->living_with_family), $this->living_with_family),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

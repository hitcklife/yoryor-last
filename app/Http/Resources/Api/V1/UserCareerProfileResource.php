<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCareerProfileResource extends JsonResource
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
            'education_level' => $this->education_level,
            'field_of_study' => $this->field_of_study,
            'work_status' => $this->work_status,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
            'career_goals' => $this->career_goals,
            'income_range' => $this->income_range,

            // Legacy fields
            'profession' => $this->when(isset($this->profession), $this->profession),
            'company' => $this->when(isset($this->company), $this->company),
            'job_title' => $this->when(isset($this->job_title), $this->job_title),
            'income' => $this->when(isset($this->income), $this->income),
            'university_name' => $this->when(isset($this->university_name), $this->university_name),
            'owns_property' => $this->when(isset($this->owns_property), $this->owns_property),
            'financial_goals' => $this->when(isset($this->financial_goals), $this->financial_goals),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

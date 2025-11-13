<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCareerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'education_level',
        'field_of_study',
        'work_status',
        'occupation',
        'employer',
        'career_goals',
        'income_range',
        
        // Legacy fields for backward compatibility
        'profession',
        'company',
        'job_title',
        'income',
        'university_name',
        'owns_property',
        'financial_goals',
    ];

    protected $casts = [
        'owns_property' => 'boolean',
        'career_goals' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

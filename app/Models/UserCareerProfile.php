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
        'university_name',
        'income_range',
        'owns_property',
        'financial_goals',
    ];

    protected $casts = [
        'owns_property' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

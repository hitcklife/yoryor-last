<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhysicalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'height',
        'weight',
        'smoking_habit',
        'drinking_habit',
        'exercise_frequency',
        'diet_preference',
        'pet_preference',
        'hobbies',
        'sleep_schedule',
        
        // Legacy fields for backward compatibility
        'fitness_level',
        'dietary_restrictions',
        'smoking_status',
        'drinking_status',
        'diet',
    ];

    protected $casts = [
        'height' => 'integer',
        'weight' => 'decimal:2',
        'dietary_restrictions' => 'array',
        'hobbies' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

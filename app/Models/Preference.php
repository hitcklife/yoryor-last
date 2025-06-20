<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preference extends Model
{
    use HasFactory;
    protected $table = 'user_preferences';
    protected $fillable = [
        'user_id',
        'search_radius',
        'country',
        'gender',
        'min_age',
        'max_age',
        'languages_spoken',
        'hobbies_interests'
    ];

    protected $casts = [
        'languages_spoken' => 'array',
        'hobbies_interests' => 'array',
        'preferred_genders' => 'array'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

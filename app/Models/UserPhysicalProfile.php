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
        'body_type',
        'hair_color',
        'eye_color',
        'fitness_level',
        'dietary_restrictions',
        'smoking_status',
        'drinking_status',
    ];

    protected $casts = [
        'height' => 'integer',
        'dietary_restrictions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCulturalProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'native_languages',
        'spoken_languages',
        'preferred_communication_language',
        'religion',
        'religiousness_level',
        'ethnicity',
        'uzbek_region',
        'lifestyle_type',
        'gender_role_views',
        'traditional_clothing_comfort',
        'uzbek_cuisine_knowledge',
        'cultural_events_participation',
        'halal_lifestyle',
    ];

    protected $casts = [
        'native_languages' => 'array',
        'spoken_languages' => 'array',
        'traditional_clothing_comfort' => 'boolean',
        'halal_lifestyle' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'search_radius',
        'country',
        'preferred_genders',
        'hobbies_interests',
        'min_age',
        'max_age',
        'languages_spoken',
        'deal_breakers',
        'must_haves',
        'distance_unit',
        'show_me_globally',
        'notification_preferences',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'preferred_genders' => 'array',
        'hobbies_interests' => 'array',
        'languages_spoken' => 'array',
        'deal_breakers' => 'array',
        'must_haves' => 'array',
        'notification_preferences' => 'array',
        'show_me_globally' => 'boolean',
    ];

    /**
     * Get the user that owns the preferences.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

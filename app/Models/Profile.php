<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'age',
        'status',
        'occupation',
        'city',
        'state',
        'province',
        'country_id',
        'country_code',
        'latitude',
        'longitude',
        'profession',
        'bio',
        'interests',
        'looking_for_relationship',
        'profile_views',
        'profile_completed_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'interests' => 'json',
        'date_of_birth' => 'date',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'age' => 'integer',
        'profile_views' => 'integer',
        'profile_completed_at' => 'datetime',
        'looking_for_relationship' => 'string',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function getCountryNameAttribute(): ?string
    {
        // Use the country relationship if country_id exists
        if ($this->country_id) {
            // Try to load the relationship if not already loaded
            if (! $this->relationLoaded('country')) {
                try {
                    $this->load('country');
                } catch (\Exception $e) {
                    // Fallback if relationship loading fails
                }
            }

            if ($this->relationLoaded('country') && $this->country) {
                return $this->country->name;
            }
        }

        return null;
    }
}

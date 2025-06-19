<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'city',
        'state',
        'province',
        'country_id',
        'country_code',
        'latitude',
        'longitude',
        'status',
        'occupation',
        'profession',
        'bio',
        'interests'
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
        'longitude' => 'decimal:7'

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

}

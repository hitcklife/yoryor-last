<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'immigration_status',
        'years_in_current_country',
        'plans_to_return_uzbekistan',
        'uzbekistan_visit_frequency',
        'willing_to_relocate',
        'relocation_countries',
    ];

    protected $casts = [
        'willing_to_relocate' => 'boolean',
        'years_in_current_country' => 'integer',
        'relocation_countries' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

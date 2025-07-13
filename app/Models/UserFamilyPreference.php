<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFamilyPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_importance',
        'wants_children',
        'number_of_children_wanted',
        'living_with_family',
        'family_approval_important',
        'marriage_timeline',
        'previous_marriages',
        'homemaker_preference',
    ];

    protected $casts = [
        'living_with_family' => 'boolean',
        'family_approval_important' => 'boolean',
        'number_of_children_wanted' => 'integer',
        'previous_marriages' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

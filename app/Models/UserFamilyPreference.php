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
        'marriage_intention',
        'children_preference',
        'current_children',
        'family_values',
        'living_situation',
        'family_involvement',
        'marriage_timeline',
        'family_importance',
        'family_approval_important',
        'previous_marriages',
        'homemaker_preference',
        'number_of_children_wanted', // Keep for backward compatibility
        'living_with_family', // Keep for backward compatibility
    ];

    protected $casts = [
        'living_with_family' => 'boolean',
        'family_approval_important' => 'boolean',
        'current_children' => 'integer',
        'number_of_children_wanted' => 'integer',
        'previous_marriages' => 'integer',
        'family_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

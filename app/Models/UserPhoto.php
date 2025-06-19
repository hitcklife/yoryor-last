<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPhoto extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'photo_url',
        'is_profile_photo',
        'order',
        'is_private'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_profile_photo' => 'boolean',
        'is_private' => 'boolean',
        'uploaded_at' => 'datetime'
    ];


    /**
     * The user who owns the photo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

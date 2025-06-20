<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class UserPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'original_url', 'thumbnail_url', 'medium_url',
        'is_profile_photo', 'order', 'is_private', 'is_verified',
        'status', 'rejection_reason', 'metadata', 'uploaded_at'
    ];

    protected $casts = [
        'is_profile_photo' => 'boolean',
        'is_private' => 'boolean',
        'is_verified' => 'boolean',
        'metadata' => 'json',
        'uploaded_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_private', false);
    }

    public function scopeProfilePhoto(Builder $query): Builder
    {
        return $query->where('is_profile_photo', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
}

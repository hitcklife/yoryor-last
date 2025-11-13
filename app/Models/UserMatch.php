<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserMatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matches';

    protected $fillable = [
        'user_id',
        'matched_user_id',
        'matched_at',
    ];

    protected $casts = [
        'matched_at' => 'datetime',
    ];

    /**
     * Get the user who initiated the match
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the matched user
     */
    public function matchedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'matched_user_id');
    }

    /**
     * Check if two users have a match
     */
    public static function exists(int $userId1, int $userId2): bool
    {
        return static::where(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId1)
                ->where('matched_user_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId2)
                ->where('matched_user_id', $userId1);
        })->exists();
    }

    /**
     * Get match between two users
     */
    public static function between(int $userId1, int $userId2): ?UserMatch
    {
        return static::where(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId1)
                ->where('matched_user_id', $userId2);
        })->orWhere(function ($query) use ($userId1, $userId2) {
            $query->where('user_id', $userId2)
                ->where('matched_user_id', $userId1);
        })->first();
    }
}

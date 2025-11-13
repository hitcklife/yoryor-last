<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'reason',
    ];

    /**
     * Get the user who is blocking
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    /**
     * Get the user being blocked
     */
    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }

    /**
     * Check if a user is blocked by another user
     */
    public static function isBlocked(int $blockerId, int $blockedId): bool
    {
        return static::where('blocker_id', $blockerId)
            ->where('blocked_id', $blockedId)
            ->exists();
    }

    /**
     * Check if two users have blocked each other
     */
    public static function isMutuallyBlocked(int $userId1, int $userId2): bool
    {
        return static::isBlocked($userId1, $userId2) || static::isBlocked($userId2, $userId1);
    }
}

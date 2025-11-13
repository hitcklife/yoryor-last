<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserUsageLimits extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'swipes_used',
        'likes_used',
        'video_calls_used',
        'voice_calls_used',
        'video_minutes_used',
        'voice_minutes_used',
    ];

    protected $casts = [
        'date' => 'date',
        'swipes_used' => 'integer',
        'likes_used' => 'integer',
        'video_calls_used' => 'integer',
        'voice_calls_used' => 'integer',
        'video_minutes_used' => 'integer',
        'voice_minutes_used' => 'integer',
    ];

    /**
     * Get the user for this usage record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total actions used
     */
    public function getTotalActionsAttribute(): int
    {
        return $this->swipes_used + $this->likes_used;
    }

    /**
     * Get total calls made
     */
    public function getTotalCallsAttribute(): int
    {
        return $this->video_calls_used + $this->voice_calls_used;
    }

    /**
     * Get total minutes used
     */
    public function getTotalMinutesAttribute(): int
    {
        return $this->video_minutes_used + $this->voice_minutes_used;
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for current day
     */
    public function scopeToday($query)
    {
        return $query->where('date', now()->toDateString());
    }
}
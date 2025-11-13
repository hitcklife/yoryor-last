<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMonthlyUsage extends Model
{
    use HasFactory;

    protected $table = 'user_monthly_usage';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'video_calls_count',
        'voice_calls_count',
        'video_minutes_total',
        'voice_minutes_total',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'video_calls_count' => 'integer',
        'voice_calls_count' => 'integer',
        'video_minutes_total' => 'integer',
        'voice_minutes_total' => 'integer',
    ];

    /**
     * Get the user for this usage record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total calls for the month
     */
    public function getTotalCallsAttribute(): int
    {
        return $this->video_calls_count + $this->voice_calls_count;
    }

    /**
     * Get total minutes for the month
     */
    public function getTotalMinutesAttribute(): int
    {
        return $this->video_minutes_total + $this->voice_minutes_total;
    }

    /**
     * Get average call duration in minutes
     */
    public function getAverageCallDurationAttribute(): float
    {
        $totalCalls = $this->getTotalCallsAttribute();
        
        if ($totalCalls === 0) {
            return 0;
        }

        return round($this->getTotalMinutesAttribute() / $totalCalls, 1);
    }

    /**
     * Get formatted month
     */
    public function getFormattedMonthAttribute(): string
    {
        return sprintf('%d-%02d', $this->year, $this->month);
    }

    /**
     * Scope for specific month
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    /**
     * Scope for current month
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('year', now()->year)
                     ->where('month', now()->month);
    }

    /**
     * Scope for year
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPrayerTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'fajr_time',
        'dhuhr_time',
        'asr_time',
        'maghrib_time',
        'isha_time',
        'notification_enabled',
        'notification_minutes_before',
        'preferred_calculation_method',
        'timezone',
    ];

    protected $casts = [
        'notification_enabled' => 'boolean',
        'notification_minutes_before' => 'integer',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all prayer times as array
     */
    public function getPrayerTimesAttribute(): array
    {
        return [
            'fajr' => $this->fajr_time,
            'dhuhr' => $this->dhuhr_time,
            'asr' => $this->asr_time,
            'maghrib' => $this->maghrib_time,
            'isha' => $this->isha_time,
        ];
    }

    /**
     * Check if user has set prayer times
     */
    public function hasPrayerTimes(): bool
    {
        return $this->fajr_time || $this->dhuhr_time || $this->asr_time || 
               $this->maghrib_time || $this->isha_time;
    }

    /**
     * Get calculation method name
     */
    public function getCalculationMethodNameAttribute(): string
    {
        $methods = [
            'isna' => 'Islamic Society of North America',
            'mwl' => 'Muslim World League',
            'karachi' => 'University of Islamic Sciences, Karachi',
            'makkah' => 'Umm Al-Qura University, Makkah',
            'egypt' => 'Egyptian General Authority of Survey',
            'custom' => 'Custom',
        ];

        return $methods[$this->preferred_calculation_method] ?? 'Unknown';
    }
}
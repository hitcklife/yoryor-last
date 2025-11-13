<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MatchmakerAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
    ];

    /**
     * Get the matchmaker
     */
    public function matchmaker(): BelongsTo
    {
        return $this->belongsTo(Matchmaker::class);
    }

    /**
     * Get formatted day of week
     */
    public function getFormattedDayAttribute(): string
    {
        return ucfirst($this->day_of_week);
    }

    /**
     * Get formatted time range
     */
    public function getTimeRangeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
               Carbon::parse($this->end_time)->format('g:i A');
    }

    /**
     * Get duration in minutes
     */
    public function getDurationMinutesAttribute(): int
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        return $start->diffInMinutes($end);
    }

    /**
     * Check if time slot is available on a specific date
     */
    public function isAvailableOnDate(Carbon $date): bool
    {
        if (!$this->is_available) {
            return false;
        }

        $dayOfWeek = strtolower($date->format('l'));
        return $this->day_of_week === $dayOfWeek;
    }

    /**
     * Check if a specific time falls within this availability slot
     */
    public function includesTime(string $time): bool
    {
        $checkTime = Carbon::parse($time);
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $checkTime->between($startTime, $endTime);
    }

    /**
     * Scope for available slots
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope for specific day
     */
    public function scopeDay($query, string $day)
    {
        return $query->where('day_of_week', strtolower($day));
    }

    /**
     * Get next occurrence of this availability slot
     */
    public function getNextOccurrence(): ?Carbon
    {
        if (!$this->is_available) {
            return null;
        }

        $now = now();
        $targetDay = ucfirst($this->day_of_week);
        
        // Find next occurrence of this day
        $nextDate = $now->copy()->next($targetDay);
        
        // If it's today and the time hasn't passed yet
        if ($now->format('l') === $targetDay) {
            $todaySlot = $now->copy()->setTimeFromTimeString($this->start_time);
            if ($todaySlot->isFuture()) {
                return $todaySlot;
            }
        }

        return $nextDate->setTimeFromTimeString($this->start_time);
    }

    /**
     * Get all time slots within this availability (e.g., 30-minute slots)
     */
    public function getTimeSlots(int $slotDurationMinutes = 30): array
    {
        $slots = [];
        $current = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        while ($current->addMinutes($slotDurationMinutes)->lte($end)) {
            $slotStart = $current->copy()->subMinutes($slotDurationMinutes);
            $slotEnd = $current->copy();
            
            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'display' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
            ];
        }

        return $slots;
    }

    /**
     * Check for overlap with another availability slot
     */
    public function overlapsWith(MatchmakerAvailability $other): bool
    {
        if ($this->day_of_week !== $other->day_of_week) {
            return false;
        }

        $thisStart = Carbon::parse($this->start_time);
        $thisEnd = Carbon::parse($this->end_time);
        $otherStart = Carbon::parse($other->start_time);
        $otherEnd = Carbon::parse($other->end_time);

        return $thisStart->lt($otherEnd) && $otherStart->lt($thisEnd);
    }
}
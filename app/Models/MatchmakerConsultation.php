<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MatchmakerConsultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'user_id',
        'scheduled_at',
        'duration_minutes',
        'type',
        'status',
        'price',
        'meeting_link',
        'agenda',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the matchmaker
     */
    public function matchmaker(): BelongsTo
    {
        return $this->belongsTo(Matchmaker::class);
    }

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted consultation type
     */
    public function getFormattedTypeAttribute(): string
    {
        return match ($this->type) {
            'initial' => 'Initial Consultation',
            'follow_up' => 'Follow-up Session',
            'strategy' => 'Strategy Planning',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'scheduled' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            'no_show' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Get end time
     */
    public function getEndTimeAttribute(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Get formatted scheduled time
     */
    public function getFormattedScheduledAtAttribute(): string
    {
        return $this->scheduled_at->format('M j, Y \a\t g:i A');
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' minutes';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($minutes === 0) {
            return $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        
        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Check if consultation is upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isFuture();
    }

    /**
     * Check if consultation is today
     */
    public function isToday(): bool
    {
        return $this->scheduled_at->isToday();
    }

    /**
     * Check if consultation is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isPast();
    }

    /**
     * Get minutes until consultation
     */
    public function getMinutesUntilAttribute(): ?int
    {
        if (!$this->isUpcoming()) {
            return null;
        }

        return now()->diffInMinutes($this->scheduled_at, false);
    }

    /**
     * Check if consultation can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at->gt(now()->addHours(24));
    }

    /**
     * Check if consultation can be rescheduled
     */
    public function canBeRescheduled(): bool
    {
        return $this->status === 'scheduled' && 
               $this->scheduled_at->gt(now()->addHours(2));
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null || $this->price == 0) {
            return 'Free';
        }

        return '$' . number_format($this->price, 2);
    }

    /**
     * Scope for scheduled consultations
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for upcoming consultations
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for today's consultations
     */
    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', now()->toDateString());
    }

    /**
     * Scope for overdue consultations
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'scheduled')
                     ->where('scheduled_at', '<', now());
    }

    /**
     * Scope for completed consultations
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for consultations in date range
     */
    public function scopeDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('scheduled_at', [$start, $end]);
    }

    /**
     * Generate meeting link if not exists
     */
    public function generateMeetingLink(): string
    {
        if ($this->meeting_link) {
            return $this->meeting_link;
        }

        // In real implementation, integrate with your video calling service
        $link = 'https://meet.yourapp.com/consultation/' . $this->id . '?token=' . md5($this->id . $this->scheduled_at);
        
        $this->update(['meeting_link' => $link]);
        
        return $link;
    }

    /**
     * Mark as completed
     */
    public function markCompleted(?string $notes = null): bool
    {
        return $this->update([
            'status' => 'completed',
            'notes' => $notes ?: $this->notes,
        ]);
    }

    /**
     * Mark as no show
     */
    public function markNoShow(?string $notes = null): bool
    {
        return $this->update([
            'status' => 'no_show',
            'notes' => $notes ?: $this->notes,
        ]);
    }

    /**
     * Cancel consultation
     */
    public function cancel(?string $reason = null): bool
    {
        $notes = $this->notes;
        if ($reason) {
            $notes = $notes ? $notes . "\n\nCancellation reason: " . $reason : "Cancellation reason: " . $reason;
        }

        return $this->update([
            'status' => 'cancelled',
            'notes' => $notes,
        ]);
    }
}
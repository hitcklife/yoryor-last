<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MatchmakerIntroduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'client_id',
        'suggested_user_id',
        'introduction_message',
        'compatibility_notes',
        'compatibility_score',
        'client_response',
        'suggested_user_response',
        'client_responded_at',
        'suggested_user_responded_at',
        'meeting_arranged',
        'meeting_date',
        'outcome_notes',
        'outcome',
    ];

    protected $casts = [
        'compatibility_score' => 'decimal:2',
        'client_responded_at' => 'datetime',
        'suggested_user_responded_at' => 'datetime',
        'meeting_arranged' => 'boolean',
        'meeting_date' => 'datetime',
    ];

    /**
     * Get the matchmaker
     */
    public function matchmaker(): BelongsTo
    {
        return $this->belongsTo(Matchmaker::class);
    }

    /**
     * Get the client user
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the suggested user
     */
    public function suggestedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_user_id');
    }

    /**
     * Check if both parties have responded
     */
    public function bothResponded(): bool
    {
        return $this->client_response !== 'pending' && 
               $this->suggested_user_response !== 'pending';
    }

    /**
     * Check if both parties are interested
     */
    public function bothInterested(): bool
    {
        return $this->client_response === 'interested' && 
               $this->suggested_user_response === 'interested';
    }

    /**
     * Check if introduction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->outcome === 'successful' || $this->bothInterested();
    }

    /**
     * Check if introduction is pending response
     */
    public function isPending(): bool
    {
        return !$this->bothResponded();
    }

    /**
     * Get days since introduction
     */
    public function getDaysSinceIntroductionAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Get response status for a specific user
     */
    public function getResponseStatusForUser(int $userId): string
    {
        if ($userId === $this->client_id) {
            return $this->client_response;
        }
        
        if ($userId === $this->suggested_user_id) {
            return $this->suggested_user_response;
        }

        return 'not_involved';
    }

    /**
     * Get the other user for a given user ID
     */
    public function getOtherUser(int $userId): ?User
    {
        if ($userId === $this->client_id) {
            return $this->suggestedUser;
        }
        
        if ($userId === $this->suggested_user_id) {
            return $this->client;
        }

        return null;
    }

    /**
     * Check if user can respond to this introduction
     */
    public function canUserRespond(int $userId): bool
    {
        if ($userId === $this->client_id) {
            return $this->client_response === 'pending';
        }
        
        if ($userId === $this->suggested_user_id) {
            return $this->suggested_user_response === 'pending';
        }

        return false;
    }

    /**
     * Get formatted compatibility score
     */
    public function getFormattedCompatibilityScoreAttribute(): string
    {
        if ($this->compatibility_score === null) {
            return 'Not calculated';
        }

        return number_format($this->compatibility_score, 1) . '%';
    }

    /**
     * Get compatibility level based on score
     */
    public function getCompatibilityLevelAttribute(): string
    {
        if ($this->compatibility_score === null) {
            return 'unknown';
        }

        if ($this->compatibility_score >= 80) {
            return 'excellent';
        } elseif ($this->compatibility_score >= 70) {
            return 'very_good';
        } elseif ($this->compatibility_score >= 60) {
            return 'good';
        } elseif ($this->compatibility_score >= 50) {
            return 'moderate';
        } else {
            return 'low';
        }
    }

    /**
     * Scope for pending responses
     */
    public function scopePendingResponse($query)
    {
        return $query->where('client_response', 'pending')
                     ->orWhere('suggested_user_response', 'pending');
    }

    /**
     * Scope for successful introductions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('outcome', 'successful')
                     ->orWhere(function ($q) {
                         $q->where('client_response', 'interested')
                           ->where('suggested_user_response', 'interested');
                     });
    }

    /**
     * Scope for introductions involving a specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('client_id', $userId)
                     ->orWhere('suggested_user_id', $userId);
    }

    /**
     * Scope for recent introductions
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get response time for client
     */
    public function getClientResponseTimeAttribute(): ?int
    {
        if (!$this->client_responded_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->client_responded_at);
    }

    /**
     * Get response time for suggested user
     */
    public function getSuggestedUserResponseTimeAttribute(): ?int
    {
        if (!$this->suggested_user_responded_at) {
            return null;
        }

        return $this->created_at->diffInHours($this->suggested_user_responded_at);
    }
}
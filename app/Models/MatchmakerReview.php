<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchmakerReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'user_id',
        'rating',
        'review',
        'would_recommend',
        'is_verified_client',
        'service_type',
    ];

    protected $casts = [
        'rating' => 'integer',
        'would_recommend' => 'boolean',
        'is_verified_client' => 'boolean',
    ];

    /**
     * Get the matchmaker
     */
    public function matchmaker(): BelongsTo
    {
        return $this->belongsTo(Matchmaker::class);
    }

    /**
     * Get the user who left the review
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Get rating level
     */
    public function getRatingLevelAttribute(): string
    {
        return match ($this->rating) {
            5 => 'excellent',
            4 => 'very_good',
            3 => 'good',
            2 => 'fair',
            1 => 'poor',
            default => 'unknown'
        };
    }

    /**
     * Get service type display name
     */
    public function getServiceTypeDisplayAttribute(): string
    {
        return match ($this->service_type) {
            'full_service' => 'Full Matchmaking Service',
            'consultation' => 'Consultation Only',
            'introduction' => 'Introduction Service',
            default => 'General Service'
        };
    }

    /**
     * Scope for verified client reviews
     */
    public function scopeVerifiedClients($query)
    {
        return $query->where('is_verified_client', true);
    }

    /**
     * Scope for high ratings
     */
    public function scopeHighRatings($query, int $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * Scope for recent reviews
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for recommendations
     */
    public function scopeRecommended($query)
    {
        return $query->where('would_recommend', true);
    }

    /**
     * Check if review has text content
     */
    public function hasReviewText(): bool
    {
        return !empty(trim($this->review));
    }

    /**
     * Get truncated review text
     */
    public function getTruncatedReviewAttribute(): string
    {
        if (!$this->hasReviewText()) {
            return '';
        }

        return strlen($this->review) > 150 
            ? substr($this->review, 0, 147) . '...'
            : $this->review;
    }
}
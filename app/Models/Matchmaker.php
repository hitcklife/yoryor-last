<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Matchmaker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'bio',
        'phone',
        'website',
        'specializations',
        'languages',
        'years_experience',
        'success_rate',
        'successful_matches',
        'total_clients',
        'verification_status',
        'verified_at',
        'is_active',
        'rating',
        'reviews_count',
    ];

    protected $casts = [
        'specializations' => 'array',
        'languages' => 'array',
        'years_experience' => 'integer',
        'success_rate' => 'decimal:2',
        'successful_matches' => 'integer',
        'total_clients' => 'integer',
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
        'rating' => 'decimal:2',
        'reviews_count' => 'integer',
    ];

    /**
     * Get the user account
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get services offered
     */
    public function services(): HasMany
    {
        return $this->hasMany(MatchmakerService::class);
    }

    /**
     * Get clients
     */
    public function clients(): HasMany
    {
        return $this->hasMany(MatchmakerClient::class);
    }

    /**
     * Get introductions made
     */
    public function introductions(): HasMany
    {
        return $this->hasMany(MatchmakerIntroduction::class);
    }

    /**
     * Get reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(MatchmakerReview::class);
    }

    /**
     * Get availability slots
     */
    public function availability(): HasMany
    {
        return $this->hasMany(MatchmakerAvailability::class);
    }

    /**
     * Get consultations
     */
    public function consultations(): HasMany
    {
        return $this->hasMany(MatchmakerConsultation::class);
    }

    /**
     * Check if matchmaker is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if matchmaker is active
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->isVerified();
    }

    /**
     * Calculate success rate
     */
    public function calculateSuccessRate(): float
    {
        if ($this->total_clients === 0) {
            return 0;
        }

        return round(($this->successful_matches / $this->total_clients) * 100, 2);
    }

    /**
     * Get active clients
     */
    public function activeClients(): HasMany
    {
        return $this->clients()->where('status', 'active');
    }

    /**
     * Get successful introductions
     */
    public function successfulIntroductions(): HasMany
    {
        return $this->introductions()->where('outcome', 'successful');
    }

    /**
     * Scope for verified matchmakers
     */
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    /**
     * Scope for active matchmakers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('verification_status', 'verified');
    }

    /**
     * Scope for top rated
     */
    public function scopeTopRated($query)
    {
        return $query->where('rating', '>=', 4.0)
                     ->orderByDesc('rating');
    }

    /**
     * Get formatted specializations
     */
    public function getFormattedSpecializationsAttribute(): string
    {
        $specializations = [
            'traditional' => 'Traditional Matchmaking',
            'modern' => 'Modern Dating',
            'religious' => 'Religious Compatibility',
            'professional' => 'Career-Focused',
            'international' => 'Cross-Cultural',
            'senior' => 'Senior Dating',
            'lgbtq' => 'LGBTQ+ Friendly',
        ];

        $formatted = array_map(function ($spec) use ($specializations) {
            return $specializations[$spec] ?? ucfirst($spec);
        }, $this->specializations ?? []);

        return implode(', ', $formatted);
    }
}
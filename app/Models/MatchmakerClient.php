<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MatchmakerClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'client_id',
        'service_id',
        'status',
        'goals',
        'preferences',
        'contract_start_date',
        'contract_end_date',
        'introductions_made',
        'successful_matches',
        'notes',
    ];

    protected $casts = [
        'preferences' => 'array',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'introductions_made' => 'integer',
        'successful_matches' => 'integer',
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
     * Get the service package
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(MatchmakerService::class, 'service_id');
    }

    /**
     * Get introductions for this client
     */
    public function introductions(): HasMany
    {
        return $this->hasMany(MatchmakerIntroduction::class, 'client_id', 'client_id')
            ->where('matchmaker_id', $this->matchmaker_id);
    }

    /**
     * Check if contract is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->contract_end_date === null || $this->contract_end_date->isFuture());
    }

    /**
     * Check if contract is expired
     */
    public function isExpired(): bool
    {
        return $this->contract_end_date && $this->contract_end_date->isPast();
    }

    /**
     * Get days remaining in contract
     */
    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->contract_end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->contract_end_date, false));
    }

    /**
     * Get contract progress percentage
     */
    public function getContractProgressAttribute(): float
    {
        if (!$this->contract_end_date) {
            return 0;
        }

        $totalDays = $this->contract_start_date->diffInDays($this->contract_end_date);
        $elapsedDays = $this->contract_start_date->diffInDays(now());

        if ($totalDays === 0) {
            return 100;
        }

        return min(100, ($elapsedDays / $totalDays) * 100);
    }

    /**
     * Get success rate for this client
     */
    public function getSuccessRateAttribute(): float
    {
        if ($this->introductions_made === 0) {
            return 0;
        }

        return round(($this->successful_matches / $this->introductions_made) * 100, 2);
    }

    /**
     * Get remaining introductions
     */
    public function getRemainingIntroductionsAttribute(): ?int
    {
        if (!$this->service || !$this->service->hasIntroductionLimit()) {
            return null; // Unlimited
        }

        return max(0, $this->service->max_introductions - $this->introductions_made);
    }

    /**
     * Check if client can receive more introductions
     */
    public function canReceiveIntroductions(): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        return $this->remaining_introductions === null || $this->remaining_introductions > 0;
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for expired contracts
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('contract_end_date')
                     ->where('contract_end_date', '<', now());
    }

    /**
     * Scope for expiring soon (within days)
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->whereNotNull('contract_end_date')
                     ->where('contract_end_date', '>', now())
                     ->where('contract_end_date', '<=', now()->addDays($days));
    }

    /**
     * Get formatted preferences for display
     */
    public function getFormattedPreferencesAttribute(): array
    {
        $preferences = $this->preferences ?? [];
        $formatted = [];

        if (isset($preferences['age_min'], $preferences['age_max'])) {
            $formatted['Age Range'] = $preferences['age_min'] . '-' . $preferences['age_max'] . ' years';
        }

        if (isset($preferences['location_preference'])) {
            $formatted['Location'] = $preferences['location_preference'];
        }

        if (isset($preferences['education_level'])) {
            $formatted['Education'] = ucfirst(str_replace('_', ' ', $preferences['education_level']));
        }

        if (isset($preferences['religion_importance'])) {
            $formatted['Religion Importance'] = ucfirst(str_replace('_', ' ', $preferences['religion_importance']));
        }

        if (isset($preferences['family_plans'])) {
            $formatted['Family Plans'] = ucfirst(str_replace('_', ' ', $preferences['family_plans']));
        }

        return $formatted;
    }
}
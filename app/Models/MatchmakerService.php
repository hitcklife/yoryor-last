<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MatchmakerService extends Model
{
    use HasFactory;

    protected $fillable = [
        'matchmaker_id',
        'name',
        'description',
        'price',
        'currency',
        'duration_unit',
        'duration_value',
        'max_introductions',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_value' => 'integer',
        'max_introductions' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the matchmaker
     */
    public function matchmaker(): BelongsTo
    {
        return $this->belongsTo(Matchmaker::class);
    }

    /**
     * Get clients using this service
     */
    public function clients(): HasMany
    {
        return $this->hasMany(MatchmakerClient::class, 'service_id');
    }

    /**
     * Calculate total duration in days
     */
    public function getTotalDaysAttribute(): int
    {
        return match ($this->duration_unit) {
            'days' => $this->duration_value,
            'weeks' => $this->duration_value * 7,
            'months' => $this->duration_value * 30,
            default => 30,
        };
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'UZS' => 'UZS',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency;
        
        return $symbol . number_format($this->price, 2);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $unit = $this->duration_value === 1 
            ? rtrim($this->duration_unit, 's') 
            : $this->duration_unit;
            
        return $this->duration_value . ' ' . $unit;
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for services by currency
     */
    public function scopeByCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }

    /**
     * Check if service has introduction limit
     */
    public function hasIntroductionLimit(): bool
    {
        return $this->max_introductions !== null && $this->max_introductions > 0;
    }

    /**
     * Get remaining introductions for a client
     */
    public function getRemainingIntroductions(int $clientId): ?int
    {
        if (!$this->hasIntroductionLimit()) {
            return null; // Unlimited
        }

        $client = $this->clients()->where('client_id', $clientId)->first();
        if (!$client) {
            return $this->max_introductions;
        }

        return max(0, $this->max_introductions - $client->introductions_made);
    }
}
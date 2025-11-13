<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanPricing extends Model
{
    use HasFactory;

    protected $table = 'plan_pricing';

    protected $fillable = [
        'plan_id',
        'country_code',
        'currency',
        'price',
        'original_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
    ];

    /**
     * Get the plan for this pricing
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get formatted price with currency symbol
     */
    public function getFormattedPriceAttribute(): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'RUB' => '₽',
            'UZS' => 'сум',
            'TRY' => '₺',
            'AED' => 'د.إ',
            'SAR' => 'ر.س',
            'KZT' => '₸',
        ];

        $symbol = $symbols[$this->currency] ?? $this->currency . ' ';
        
        // Format based on currency
        if (in_array($this->currency, ['UZS', 'KZT'])) {
            // No decimals for these currencies
            return $symbol . ' ' . number_format($this->price, 0, '.', ' ');
        }
        
        return $symbol . number_format($this->price, 2, '.', ',');
    }

    /**
     * Get discount percentage if original price exists
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->original_price || $this->original_price <= $this->price) {
            return null;
        }

        return round((($this->original_price - $this->price) / $this->original_price) * 100);
    }

    /**
     * Check if pricing has discount
     */
    public function hasDiscount(): bool
    {
        return $this->original_price && $this->original_price > $this->price;
    }

    /**
     * Scope for specific country
     */
    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope for specific currency
     */
    public function scopeForCurrency($query, string $currency)
    {
        return $query->where('currency', $currency);
    }
}
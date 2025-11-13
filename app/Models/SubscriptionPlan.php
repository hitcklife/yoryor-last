<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tier',
        'swipes_per_day',
        'video_calls_per_month',
        'voice_calls_per_month',
        'max_call_duration_minutes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'swipes_per_day' => 'integer',
        'video_calls_per_month' => 'integer',
        'voice_calls_per_month' => 'integer',
        'max_call_duration_minutes' => 'integer',
    ];

    /**
     * Get pricing for this plan
     */
    public function pricing(): HasMany
    {
        return $this->hasMany(PlanPricing::class, 'plan_id');
    }

    /**
     * Get subscriptions for this plan
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    /**
     * Get features for this plan
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'subscription_features', 'plan_id', 'feature_id')
                    ->withPivot('limit_value', 'is_unlimited')
                    ->withTimestamps();
    }

    /**
     * Get active subscriptions count
     */
    public function getActiveSubscriptionsCountAttribute(): int
    {
        return $this->subscriptions()
                    ->whereIn('status', ['active', 'trialing'])
                    ->count();
    }

    /**
     * Check if plan has unlimited swipes
     */
    public function hasUnlimitedSwipes(): bool
    {
        return $this->swipes_per_day === -1;
    }

    /**
     * Check if plan has unlimited video calls
     */
    public function hasUnlimitedVideoCalls(): bool
    {
        return $this->video_calls_per_month === -1;
    }

    /**
     * Check if plan has unlimited voice calls
     */
    public function hasUnlimitedVoiceCalls(): bool
    {
        return $this->voice_calls_per_month === -1;
    }

    /**
     * Check if plan has unlimited call duration
     */
    public function hasUnlimitedCallDuration(): bool
    {
        return $this->max_call_duration_minutes === -1;
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $featureKey): bool
    {
        return $this->features()->where('key', $featureKey)->exists();
    }

    /**
     * Get pricing for a specific country
     */
    public function getPricingForCountry(string $countryCode): ?PlanPricing
    {
        return $this->pricing()
                    ->where('country_code', $countryCode)
                    ->first();
    }

    /**
     * Get default pricing (USD)
     */
    public function getDefaultPricing(): ?PlanPricing
    {
        return $this->pricing()
                    ->where('currency', 'USD')
                    ->first();
    }

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered plans
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
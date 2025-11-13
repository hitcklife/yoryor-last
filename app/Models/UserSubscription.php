<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'payment_provider',
        'provider_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'trial_ends_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    /**
     * Get the user for this subscription
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this subscription
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get payment transactions for this subscription
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'subscription_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    /**
     * Check if subscription is canceled
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled' || $this->canceled_at !== null;
    }

    /**
     * Check if subscription is past due
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is in trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trialing' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription period has ended
     */
    public function hasEnded(): bool
    {
        return $this->current_period_end && 
               $this->current_period_end->isPast();
    }

    /**
     * Get days remaining in current period
     */
    public function getDaysRemaining(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return max(0, now()->diffInDays($this->current_period_end, false));
    }

    /**
     * Get days remaining in trial
     */
    public function getTrialDaysRemaining(): int
    {
        if (!$this->trial_ends_at) {
            return 0;
        }

        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Cancel subscription at period end
     */
    public function cancelAtPeriodEnd(): void
    {
        $this->update([
            'canceled_at' => now(),
        ]);
    }

    /**
     * Resume canceled subscription
     */
    public function resume(): void
    {
        if ($this->isCanceled() && !$this->hasEnded()) {
            $this->update([
                'canceled_at' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    /**
     * Scope for canceled subscriptions
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', 'canceled')
                     ->orWhereNotNull('canceled_at');
    }

    /**
     * Scope for expiring soon (within 7 days)
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('current_period_end', '<=', now()->addDays(7))
                     ->where('current_period_end', '>', now());
    }

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute(): string
    {
        $statuses = [
            'active' => 'Active',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
            'past_due' => 'Past Due',
            'trialing' => 'Trial',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get subscription details for display
     */
    public function getDetails(): array
    {
        return [
            'id' => $this->id,
            'plan' => $this->plan ? [
                'id' => $this->plan->id,
                'name' => $this->plan->name,
                'tier' => $this->plan->tier,
            ] : null,
            'status' => $this->status,
            'formatted_status' => $this->formatted_status,
            'is_active' => $this->isActive(),
            'is_canceled' => $this->isCanceled(),
            'is_on_trial' => $this->isOnTrial(),
            'current_period_start' => $this->current_period_start?->toISOString(),
            'current_period_end' => $this->current_period_end?->toISOString(),
            'canceled_at' => $this->canceled_at?->toISOString(),
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'days_remaining' => $this->getDaysRemaining(),
            'trial_days_remaining' => $this->getTrialDaysRemaining(),
            'payment_provider' => $this->payment_provider,
        ];
    }
}
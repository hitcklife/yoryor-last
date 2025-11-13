<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'subscriptions',
            'id' => (string) $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'plan_id' => $this->plan_id,
                'payment_provider' => $this->payment_provider,
                'status' => $this->status,
                'formatted_status' => $this->formatted_status,
                'current_period_start' => $this->current_period_start,
                'current_period_end' => $this->current_period_end,
                'canceled_at' => $this->canceled_at,
                'trial_ends_at' => $this->trial_ends_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                // Computed attributes
                'is_active' => $this->isActive(),
                'is_canceled' => $this->isCanceled(),
                'is_past_due' => $this->isPastDue(),
                'is_on_trial' => $this->isOnTrial(),
                'has_ended' => $this->hasEnded(),
                'days_remaining' => $this->getDaysRemaining(),
                'trial_days_remaining' => $this->getTrialDaysRemaining(),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->user_id,
                    ],
                ],
                'plan' => $this->when($this->plan_id, function () {
                    return [
                        'data' => [
                            'type' => 'subscription_plans',
                            'id' => (string) $this->plan_id,
                        ],
                    ];
                }),
            ],
            'included' => $this->when(
                $this->relationLoaded('plan') || $this->relationLoaded('user'),
                function () {
                    $included = [];

                    // Include plan data if loaded
                    if ($this->relationLoaded('plan') && $this->plan) {
                        $included[] = [
                            'type' => 'subscription_plans',
                            'id' => (string) $this->plan->id,
                            'attributes' => [
                                'name' => $this->plan->name,
                                'tier' => $this->plan->tier,
                                'price' => $this->plan->price ?? null,
                                'currency' => $this->plan->currency ?? 'USD',
                                'interval' => $this->plan->interval ?? 'month',
                                'features' => $this->plan->features ?? null,
                            ],
                        ];
                    }

                    // Include minimal user data if loaded (for admin views)
                    if ($this->relationLoaded('user') && $this->user) {
                        $included[] = [
                            'type' => 'users',
                            'id' => (string) $this->user->id,
                            'attributes' => [
                                'email' => $this->user->email,
                                'full_name' => $this->user->relationLoaded('profile') && $this->user->profile
                                    ? trim($this->user->profile->first_name . ' ' . $this->user->profile->last_name) ?: null
                                    : null,
                            ],
                        ];
                    }

                    return array_filter($included);
                }
            ),
        ];
    }
}

<?php

namespace App\Services;

use App\Services\Payment\PaymentProviderInterface;
use App\Services\Payment\StripePaymentProvider;
use App\Services\Payment\PaymePaymentProvider;
use App\Services\Payment\ClickPaymentProvider;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentManager
{
    private $providers = [];

    public function __construct()
    {
        $this->registerProviders();
    }

    /**
     * Register all payment providers
     */
    private function registerProviders(): void
    {
        $this->providers = [
            'stripe' => new StripePaymentProvider(),
            'payme' => new PaymePaymentProvider(),
            'click' => new ClickPaymentProvider(),
        ];
    }

    /**
     * Get payment provider instance
     */
    public function getProvider(string $provider): PaymentProviderInterface
    {
        if (!isset($this->providers[$provider])) {
            throw new \Exception("Payment provider {$provider} not found");
        }

        return $this->providers[$provider];
    }

    /**
     * Get available payment providers for a country
     */
    public function getAvailableProviders(string $countryCode): array
    {
        $providers = [];

        // Uzbekistan local providers
        if ($countryCode === 'UZ') {
            $providers = ['payme', 'click'];
        }

        // Add Stripe for all countries
        $providers[] = 'stripe';

        return array_unique($providers);
    }

    /**
     * Create subscription
     */
    public function createSubscription(User $user, SubscriptionPlan $plan, string $provider, array $paymentData): array
    {
        try {
            // Get plan pricing for user's country
            $pricing = $plan->pricing()
                ->where('country_code', $user->country_code ?? 'US')
                ->first();

            if (!$pricing) {
                // Fallback to USD pricing
                $pricing = $plan->pricing()
                    ->where('currency', 'USD')
                    ->first();
            }

            if (!$pricing) {
                throw new \Exception('No pricing available for this plan');
            }

            // Check for existing active subscription
            $existingSubscription = $user->subscriptions()
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if ($existingSubscription) {
                throw new \Exception('User already has an active subscription');
            }

            // Provider-specific data preparation
            $providerData = $this->prepareProviderData($provider, $user, $plan, $pricing, $paymentData);

            // Create subscription with provider
            $providerInstance = $this->getProvider($provider);
            $result = $providerInstance->createSubscription($providerData);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to create subscription');
            }

            // Create local subscription record
            $subscription = DB::transaction(function () use ($user, $plan, $provider, $result, $pricing) {
                $subscription = UserSubscription::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_provider' => $provider,
                    'provider_subscription_id' => $result['subscription_id'],
                    'status' => $result['status'] ?? 'pending',
                    'current_period_start' => isset($result['current_period_start']) 
                        ? now()->createFromTimestamp($result['current_period_start']) 
                        : now(),
                    'current_period_end' => isset($result['current_period_end']) 
                        ? now()->createFromTimestamp($result['current_period_end']) 
                        : now()->addMonth(),
                    'metadata' => $result['metadata'] ?? [],
                ]);

                // Record transaction
                PaymentTransaction::create([
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                    'provider' => $provider,
                    'provider_transaction_id' => $result['subscription_id'],
                    'type' => 'subscription',
                    'amount' => $pricing->price,
                    'currency' => $pricing->currency,
                    'status' => $result['status'] === 'active' ? 'succeeded' : 'pending',
                    'provider_data' => $result,
                ]);

                return $subscription;
            });

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$user->id}"]);

            return [
                'success' => true,
                'subscription' => $subscription,
                'checkout_url' => $result['checkout_url'] ?? null,
                'requires_action' => $result['status'] === 'pending',
            ];

        } catch (\Exception $e) {
            Log::error('Subscription creation failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(UserSubscription $subscription): bool
    {
        try {
            $provider = $this->getProvider($subscription->payment_provider);
            
            // Cancel with provider
            if ($subscription->provider_subscription_id) {
                $result = $provider->cancelSubscription($subscription->provider_subscription_id);
                
                if (!$result) {
                    throw new \Exception('Failed to cancel subscription with provider');
                }
            }

            // Update local subscription
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$subscription->user_id}"]);

            return true;

        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Update subscription
     */
    public function updateSubscription(UserSubscription $subscription, SubscriptionPlan $newPlan): array
    {
        try {
            $provider = $this->getProvider($subscription->payment_provider);
            
            // Get new plan pricing
            $pricing = $newPlan->pricing()
                ->where('country_code', $subscription->user->country_code ?? 'US')
                ->first();

            if (!$pricing) {
                throw new \Exception('No pricing available for this plan');
            }

            // Update with provider
            $result = $provider->updateSubscription($subscription->provider_subscription_id, [
                'plan_id' => $newPlan->id,
                'price_id' => $pricing->provider_price_id ?? null,
            ]);

            if (!$result['success']) {
                throw new \Exception($result['error'] ?? 'Failed to update subscription');
            }

            // Update local subscription
            $subscription->update([
                'plan_id' => $newPlan->id,
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'previous_plan_id' => $subscription->plan_id,
                    'updated_at' => now()->toISOString(),
                ]),
            ]);

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$subscription->user_id}"]);

            return [
                'success' => true,
                'subscription' => $subscription->fresh(),
            ];

        } catch (\Exception $e) {
            Log::error('Subscription update failed', [
                'subscription_id' => $subscription->id,
                'new_plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare provider-specific data
     */
    private function prepareProviderData(string $provider, User $user, SubscriptionPlan $plan, $pricing, array $paymentData): array
    {
        $baseData = [
            'user' => $user,
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'amount' => $pricing->price,
            'currency' => $pricing->currency,
        ];

        switch ($provider) {
            case 'stripe':
                return array_merge($baseData, [
                    'price_id' => $pricing->provider_price_id,
                    'payment_method_id' => $paymentData['payment_method_id'] ?? null,
                    'trial_days' => $plan->trial_days ?? null,
                ]);

            case 'payme':
            case 'click':
                return array_merge($baseData, [
                    'phone' => $user->phone ?? $paymentData['phone'] ?? '',
                    'description' => "Subscription: {$plan->name}",
                ]);

            default:
                return array_merge($baseData, $paymentData);
        }
    }

    /**
     * Get subscription price for user
     */
    public function getSubscriptionPrice(SubscriptionPlan $plan, User $user): ?object
    {
        // Get user's country from profile or IP
        $countryCode = $user->country_code ?? 
                      $user->profile->country_code ?? 
                      $this->detectCountryFromIP();

        // Try to get country-specific pricing
        $pricing = $plan->pricing()
            ->where('country_code', $countryCode)
            ->first();

        // Fallback to USD if no local pricing
        if (!$pricing) {
            $pricing = $plan->pricing()
                ->where('currency', 'USD')
                ->first();
        }

        return $pricing;
    }

    /**
     * Detect country from IP address
     */
    private function detectCountryFromIP(): string
    {
        try {
            $ip = request()->ip();
            // You can use a service like ipinfo.io or maxmind
            // For now, return default
            return 'US';
        } catch (\Exception $e) {
            return 'US';
        }
    }

    /**
     * Handle webhook from payment provider
     */
    public function handleWebhook(string $provider, array $payload, string $signature = null): array
    {
        try {
            $providerInstance = $this->getProvider($provider);

            // Verify signature if provided
            if ($signature && !$providerInstance->verifyWebhookSignature(json_encode($payload), $signature)) {
                throw new \Exception('Invalid webhook signature');
            }

            // Process webhook
            $result = $providerInstance->handleWebhook($payload);

            // Handle subscription events
            if (isset($result['action'])) {
                $this->processWebhookAction($provider, $result['action'], $result['data'] ?? []);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process webhook action
     */
    private function processWebhookAction(string $provider, string $action, array $data): void
    {
        switch ($action) {
            case 'customer.subscription.created':
            case 'subscription.created':
                $this->handleSubscriptionCreated($provider, $data);
                break;

            case 'customer.subscription.updated':
            case 'subscription.updated':
                $this->handleSubscriptionUpdated($provider, $data);
                break;

            case 'customer.subscription.deleted':
            case 'subscription.canceled':
                $this->handleSubscriptionCanceled($provider, $data);
                break;

            case 'invoice.payment_succeeded':
            case 'payment.succeeded':
                $this->handlePaymentSucceeded($provider, $data);
                break;

            case 'invoice.payment_failed':
            case 'payment.failed':
                $this->handlePaymentFailed($provider, $data);
                break;
        }
    }

    /**
     * Handle subscription created webhook
     */
    private function handleSubscriptionCreated(string $provider, array $data): void
    {
        $subscription = UserSubscription::where('provider_subscription_id', $data['id'] ?? $data['subscription_id'])
            ->where('payment_provider', $provider)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'active',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'webhook_data' => $data,
                    'activated_at' => now()->toISOString(),
                ]),
            ]);

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$subscription->user_id}"]);
        }
    }

    /**
     * Handle subscription updated webhook
     */
    private function handleSubscriptionUpdated(string $provider, array $data): void
    {
        $subscription = UserSubscription::where('provider_subscription_id', $data['id'] ?? $data['subscription_id'])
            ->where('payment_provider', $provider)
            ->first();

        if ($subscription) {
            $updates = [
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'webhook_data' => $data,
                    'last_webhook_at' => now()->toISOString(),
                ]),
            ];

            // Update status if changed
            if (isset($data['status'])) {
                $updates['status'] = $this->mapProviderStatus($provider, $data['status']);
            }

            // Update period dates if provided
            if (isset($data['current_period_start'])) {
                $updates['current_period_start'] = now()->createFromTimestamp($data['current_period_start']);
            }
            if (isset($data['current_period_end'])) {
                $updates['current_period_end'] = now()->createFromTimestamp($data['current_period_end']);
            }

            $subscription->update($updates);

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$subscription->user_id}"]);
        }
    }

    /**
     * Handle subscription canceled webhook
     */
    private function handleSubscriptionCanceled(string $provider, array $data): void
    {
        $subscription = UserSubscription::where('provider_subscription_id', $data['id'] ?? $data['subscription_id'])
            ->where('payment_provider', $provider)
            ->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'webhook_data' => $data,
                    'canceled_via_webhook' => true,
                ]),
            ]);

            // Clear user's cache
            app(CacheService::class)->flushByTags(["user:{$subscription->user_id}"]);
        }
    }

    /**
     * Handle payment succeeded webhook
     */
    private function handlePaymentSucceeded(string $provider, array $data): void
    {
        // Record successful payment
        $transaction = PaymentTransaction::updateOrCreate(
            [
                'provider' => $provider,
                'provider_transaction_id' => $data['id'] ?? $data['transaction_id'],
            ],
            [
                'status' => 'succeeded',
                'provider_data' => $data,
            ]
        );

        // Extend subscription if it's a renewal payment
        if ($transaction->subscription_id) {
            $subscription = UserSubscription::find($transaction->subscription_id);
            if ($subscription && $subscription->status === 'active') {
                $subscription->update([
                    'current_period_end' => now()->addMonth(),
                ]);
            }
        }
    }

    /**
     * Handle payment failed webhook
     */
    private function handlePaymentFailed(string $provider, array $data): void
    {
        // Record failed payment
        $transaction = PaymentTransaction::updateOrCreate(
            [
                'provider' => $provider,
                'provider_transaction_id' => $data['id'] ?? $data['transaction_id'],
            ],
            [
                'status' => 'failed',
                'failure_reason' => $data['failure_message'] ?? 'Payment failed',
                'provider_data' => $data,
            ]
        );

        // Update subscription status if needed
        if ($transaction->subscription_id) {
            $subscription = UserSubscription::find($transaction->subscription_id);
            if ($subscription) {
                $subscription->update(['status' => 'past_due']);
                
                // Notify user about payment failure
                // ... notification logic
            }
        }
    }

    /**
     * Map provider status to internal status
     */
    private function mapProviderStatus(string $provider, string $providerStatus): string
    {
        $statusMap = [
            'stripe' => [
                'active' => 'active',
                'past_due' => 'past_due',
                'canceled' => 'canceled',
                'incomplete' => 'pending',
                'incomplete_expired' => 'expired',
                'trialing' => 'trialing',
                'unpaid' => 'past_due',
            ],
            'payme' => [
                'paid' => 'active',
                'pending' => 'pending',
                'canceled' => 'canceled',
            ],
            'click' => [
                'paid' => 'active',
                'created' => 'pending',
                'canceled' => 'canceled',
            ],
        ];

        return $statusMap[$provider][$providerStatus] ?? 'unknown';
    }
}
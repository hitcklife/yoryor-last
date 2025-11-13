<?php

namespace App\Services\Payment;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripePaymentProvider implements PaymentProviderInterface
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create a new subscription
     */
    public function createSubscription(array $data): array
    {
        try {
            // Create or get customer
            $customer = $this->getOrCreateCustomer($data['user']);

            // Create subscription
            $subscription = Subscription::create([
                'customer' => $customer->id,
                'items' => [[
                    'price' => $data['price_id'], // Stripe price ID
                ]],
                'payment_method' => $data['payment_method_id'],
                'default_payment_method' => $data['payment_method_id'],
                'metadata' => [
                    'user_id' => $data['user']->id,
                    'plan_id' => $data['plan_id'],
                ],
                'trial_period_days' => $data['trial_days'] ?? null,
            ]);

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'metadata' => [
                    'customer_id' => $customer->id,
                    'subscription' => $subscription->toArray(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Stripe subscription creation failed', [
                'error' => $e->getMessage(),
                'user_id' => $data['user']->id ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update an existing subscription
     */
    public function updateSubscription(string $subscriptionId, array $data): array
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);

            if (isset($data['price_id'])) {
                // Update subscription items
                Subscription::update($subscriptionId, [
                    'items' => [[
                        'id' => $subscription->items->data[0]->id,
                        'price' => $data['price_id'],
                    ]],
                    'proration_behavior' => $data['prorate'] ?? 'create_prorations',
                ]);
            }

            if (isset($data['payment_method_id'])) {
                Subscription::update($subscriptionId, [
                    'default_payment_method' => $data['payment_method_id'],
                ]);
            }

            $updatedSubscription = Subscription::retrieve($subscriptionId);

            return [
                'success' => true,
                'subscription_id' => $updatedSubscription->id,
                'status' => $updatedSubscription->status,
                'metadata' => [
                    'subscription' => $updatedSubscription->toArray(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Stripe subscription update failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);
            $subscription->cancel_at_period_end = true;
            $subscription->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Stripe subscription cancellation failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
            ]);

            return false;
        }
    }

    /**
     * Get subscription details
     */
    public function getSubscription(string $subscriptionId): array
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);

            return [
                'success' => true,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
                'metadata' => $subscription->metadata->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process a one-time payment
     */
    public function processPayment(array $data): array
    {
        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $data['amount'] * 100, // Convert to cents
                'currency' => $data['currency'],
                'customer' => $data['customer_id'] ?? null,
                'payment_method' => $data['payment_method_id'],
                'confirmation_method' => 'manual',
                'confirm' => true,
                'metadata' => [
                    'user_id' => $data['user_id'],
                    'description' => $data['description'] ?? '',
                ],
            ]);

            return [
                'success' => true,
                'transaction_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount / 100,
                'currency' => $paymentIntent->currency,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create a payment method
     */
    public function createPaymentMethod(array $data): array
    {
        try {
            $paymentMethod = PaymentMethod::create([
                'type' => 'card',
                'card' => [
                    'token' => $data['token'],
                ],
            ]);

            // Attach to customer if provided
            if (isset($data['customer_id'])) {
                $paymentMethod->attach(['customer' => $data['customer_id']]);
            }

            return [
                'success' => true,
                'payment_method_id' => $paymentMethod->id,
                'type' => $paymentMethod->type,
                'card' => [
                    'brand' => $paymentMethod->card->brand,
                    'last4' => $paymentMethod->card->last4,
                    'exp_month' => $paymentMethod->card->exp_month,
                    'exp_year' => $paymentMethod->card->exp_year,
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(string $paymentMethodId, array $data): array
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            
            if (isset($data['billing_details'])) {
                $paymentMethod->billing_details = $data['billing_details'];
                $paymentMethod->save();
            }

            return [
                'success' => true,
                'payment_method_id' => $paymentMethod->id,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        try {
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->detach();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array
    {
        $event = $payload['type'] ?? null;

        switch ($event) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
            case 'invoice.payment_succeeded':
            case 'invoice.payment_failed':
                return [
                    'success' => true,
                    'action' => $event,
                    'data' => $payload['data']['object'],
                ];
            
            default:
                return [
                    'success' => false,
                    'error' => 'Unhandled event type',
                ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'stripe';
    }

    /**
     * Get or create Stripe customer
     */
    private function getOrCreateCustomer($user)
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->profile->full_name ?? $user->email,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }
}
<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickPaymentProvider implements PaymentProviderInterface
{
    private $merchantId;
    private $secretKey;
    private $serviceId;
    private $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.click.merchant_id');
        $this->secretKey = config('services.click.secret_key');
        $this->serviceId = config('services.click.service_id');
        $this->baseUrl = config('services.click.base_url', 'https://api.click.uz/v2');
    }

    /**
     * Create a new subscription
     */
    public function createSubscription(array $data): array
    {
        try {
            // Click doesn't have native subscriptions, we create a payment link
            $invoiceData = [
                'service_id' => $this->serviceId,
                'amount' => $data['amount'],
                'phone_number' => $data['user']->phone ?? '',
                'merchant_trans_id' => $this->generateTransactionId($data['user']->id, $data['plan_id']),
                'merchant_user_id' => $data['user']->id,
                'merchant_plan_id' => $data['plan_id'],
            ];

            $response = Http::withHeaders([
                'Auth' => $this->merchantId . ':' . $this->generateSignature($invoiceData),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/merchant/invoice/create', $invoiceData);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'subscription_id' => $result['invoice_id'],
                    'status' => 'pending',
                    'checkout_url' => $result['payment_url'] ?? null,
                    'metadata' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error_note'] ?? 'Failed to create subscription',
            ];
        } catch (\Exception $e) {
            Log::error('Click subscription creation failed', [
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
        // Click doesn't support subscription updates
        return [
            'success' => false,
            'error' => 'Subscription updates not supported. Please cancel and create a new subscription.',
        ];
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            // For Click, we mark the recurring payment as canceled in our system
            // Actual cancellation is handled internally
            return true;
        } catch (\Exception $e) {
            Log::error('Click subscription cancellation failed', [
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
            $response = Http::withHeaders([
                'Auth' => $this->merchantId . ':' . hash('sha256', $subscriptionId . $this->secretKey),
            ])->get($this->baseUrl . '/merchant/invoice/status/' . $subscriptionId);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'subscription_id' => $subscriptionId,
                    'status' => $this->mapClickStatus($result['status']),
                    'amount' => $result['amount'],
                    'metadata' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get subscription details',
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
            $paymentData = [
                'service_id' => $this->serviceId,
                'amount' => $data['amount'],
                'phone_number' => $data['phone_number'] ?? '',
                'merchant_trans_id' => uniqid('payment_'),
                'merchant_user_id' => $data['user_id'],
            ];

            $response = Http::withHeaders([
                'Auth' => $this->merchantId . ':' . $this->generateSignature($paymentData),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/merchant/invoice/create', $paymentData);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'transaction_id' => $result['invoice_id'],
                    'status' => 'pending',
                    'checkout_url' => $result['payment_url'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => 'UZS',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error_note'] ?? 'Payment failed',
            ];
        } catch (\Exception $e) {
            Log::error('Click payment failed', [
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
        // Click uses phone numbers as primary payment method
        return [
            'success' => true,
            'payment_method_id' => $data['phone_number'],
            'type' => 'phone',
            'phone' => [
                'number' => $data['phone_number'],
                'masked' => $this->maskPhoneNumber($data['phone_number']),
            ],
        ];
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(string $paymentMethodId, array $data): array
    {
        // Click doesn't support updating payment methods
        return [
            'success' => false,
            'error' => 'Payment method updates not supported',
        ];
    }

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(string $paymentMethodId): bool
    {
        // Click doesn't store payment methods
        return true;
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array
    {
        // Click sends two types of requests: prepare and complete
        $action = $payload['action'] ?? null;

        switch ($action) {
            case 0: // Prepare
                return $this->handlePrepare($payload);
            
            case 1: // Complete
                return $this->handleComplete($payload);
            
            default:
                return [
                    'error' => -8,
                    'error_note' => 'Invalid action',
                ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $data = json_decode($payload, true);
        
        if (!$data) {
            return false;
        }

        $signString = $data['click_trans_id'] . 
                     $data['service_id'] . 
                     $data['click_paydoc_id'] . 
                     $data['amount'] . 
                     $data['action'] . 
                     $data['sign_time'];

        $expectedSign = md5($signString . $this->secretKey);

        return $signature === $expectedSign;
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'click';
    }

    /**
     * Generate transaction ID
     */
    private function generateTransactionId(int $userId, int $planId): string
    {
        return sprintf('sub_%d_%d_%d', $userId, $planId, time());
    }

    /**
     * Generate signature for API requests
     */
    private function generateSignature(array $data): string
    {
        $signString = implode('', array_values($data)) . $this->secretKey;
        return hash('sha256', $signString);
    }

    /**
     * Map Click status to internal status
     */
    private function mapClickStatus($status): string
    {
        $statusMap = [
            'created' => 'pending',
            'paid' => 'active',
            'canceled' => 'canceled',
            'rejected' => 'failed',
        ];

        return $statusMap[$status] ?? 'unknown';
    }

    /**
     * Mask phone number for display
     */
    private function maskPhoneNumber(string $phone): string
    {
        if (strlen($phone) < 7) {
            return $phone;
        }

        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }

    /**
     * Handle prepare request from Click
     */
    private function handlePrepare(array $payload): array
    {
        // Validate merchant trans ID
        $parts = explode('_', $payload['merchant_trans_id']);
        
        if (count($parts) < 3 || $parts[0] !== 'sub') {
            return [
                'error' => -5,
                'error_note' => 'Invalid merchant_trans_id format',
            ];
        }

        $userId = $parts[1];
        $planId = $parts[2];

        // Check if user exists
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return [
                'error' => -5,
                'error_note' => 'User not found',
            ];
        }

        // Check if plan exists
        $plan = \App\Models\SubscriptionPlan::find($planId);
        if (!$plan) {
            return [
                'error' => -5,
                'error_note' => 'Plan not found',
            ];
        }

        // Get plan price for user's country
        $pricing = $plan->pricing()->where('country_code', $user->country_code ?? 'UZ')->first();
        if (!$pricing || $pricing->price != $payload['amount']) {
            return [
                'error' => -2,
                'error_note' => 'Invalid amount',
            ];
        }

        // Check for duplicate payment
        $existingTransaction = \App\Models\PaymentTransaction::where('provider_transaction_id', $payload['click_trans_id'])->first();
        if ($existingTransaction) {
            return [
                'error' => -4,
                'error_note' => 'Transaction already exists',
            ];
        }

        // Create pending transaction
        $transaction = \App\Models\PaymentTransaction::create([
            'user_id' => $userId,
            'provider' => 'click',
            'provider_transaction_id' => $payload['click_trans_id'],
            'type' => 'subscription',
            'amount' => $payload['amount'],
            'currency' => 'UZS',
            'status' => 'pending',
            'provider_data' => $payload,
        ]);

        return [
            'click_trans_id' => $payload['click_trans_id'],
            'merchant_trans_id' => $payload['merchant_trans_id'],
            'merchant_prepare_id' => $transaction->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }

    /**
     * Handle complete request from Click
     */
    private function handleComplete(array $payload): array
    {
        // Find transaction
        $transaction = \App\Models\PaymentTransaction::where('provider_transaction_id', $payload['click_trans_id'])->first();
        
        if (!$transaction) {
            return [
                'error' => -6,
                'error_note' => 'Transaction not found',
            ];
        }

        // Check if already processed
        if ($transaction->status === 'succeeded') {
            return [
                'click_trans_id' => $payload['click_trans_id'],
                'merchant_trans_id' => $payload['merchant_trans_id'],
                'merchant_confirm_id' => $transaction->id,
                'error' => -4,
                'error_note' => 'Transaction already completed',
            ];
        }

        // Check error status
        if ($payload['error'] < 0) {
            $transaction->update([
                'status' => 'failed',
                'failure_reason' => $payload['error_note'] ?? 'Payment failed',
            ]);

            return [
                'click_trans_id' => $payload['click_trans_id'],
                'merchant_trans_id' => $payload['merchant_trans_id'],
                'error' => -9,
                'error_note' => 'Transaction canceled',
            ];
        }

        // Complete transaction
        $transaction->update([
            'status' => 'succeeded',
            'provider_data' => array_merge($transaction->provider_data ?? [], $payload),
        ]);

        // Activate subscription
        $parts = explode('_', $payload['merchant_trans_id']);
        $userId = $parts[1];
        $planId = $parts[2];

        $subscription = \App\Models\UserSubscription::create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'payment_provider' => 'click',
            'provider_subscription_id' => $payload['click_trans_id'],
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'metadata' => [
                'click_trans_id' => $payload['click_trans_id'],
                'merchant_trans_id' => $payload['merchant_trans_id'],
            ],
        ]);

        return [
            'click_trans_id' => $payload['click_trans_id'],
            'merchant_trans_id' => $payload['merchant_trans_id'],
            'merchant_confirm_id' => $transaction->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }
}
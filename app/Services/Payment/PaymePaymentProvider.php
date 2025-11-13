<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymePaymentProvider implements PaymentProviderInterface
{
    private $merchantId;
    private $secretKey;
    private $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.payme.merchant_id');
        $this->secretKey = config('services.payme.secret_key');
        $this->baseUrl = config('services.payme.base_url', 'https://checkout.paycom.uz/api');
    }

    /**
     * Create a new subscription
     */
    public function createSubscription(array $data): array
    {
        try {
            // Payme uses a different approach - create a recurring payment
            $response = Http::withHeaders([
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/receipts/create', [
                'amount' => $data['amount'] * 100, // Convert to tiyin
                'account' => [
                    'user_id' => $data['user']->id,
                    'plan_id' => $data['plan_id'],
                ],
                'description' => $data['description'] ?? 'Subscription payment',
                'detail' => [
                    'receipt_type' => 1, // Recurring
                    'items' => [[
                        'title' => $data['plan_name'],
                        'price' => $data['amount'] * 100,
                        'count' => 1,
                        'vat' => 0,
                    ]],
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'subscription_id' => $result['result']['receipt']['_id'],
                    'status' => 'pending',
                    'checkout_url' => $result['result']['receipt']['checkout_url'] ?? null,
                    'metadata' => $result,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Failed to create subscription',
            ];
        } catch (\Exception $e) {
            Log::error('Payme subscription creation failed', [
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
        // Payme doesn't support direct subscription updates
        // Need to cancel old and create new
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
            $response = Http::withHeaders([
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/receipts/cancel', [
                'id' => $subscriptionId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Payme subscription cancellation failed', [
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
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/receipts/get', [
                'id' => $subscriptionId,
            ]);

            if ($response->successful()) {
                $result = $response->json()['result']['receipt'];

                return [
                    'success' => true,
                    'subscription_id' => $result['_id'],
                    'status' => $this->mapPaymeStatus($result['state']),
                    'amount' => $result['amount'] / 100,
                    'created_at' => $result['create_time'],
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
            $response = Http::withHeaders([
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/receipts/create', [
                'amount' => $data['amount'] * 100, // Convert to tiyin
                'account' => [
                    'user_id' => $data['user_id'],
                ],
                'description' => $data['description'] ?? 'One-time payment',
                'detail' => [
                    'receipt_type' => 0, // One-time
                ],
            ]);

            if ($response->successful()) {
                $result = $response->json();

                return [
                    'success' => true,
                    'transaction_id' => $result['result']['receipt']['_id'],
                    'status' => 'pending',
                    'checkout_url' => $result['result']['receipt']['checkout_url'] ?? null,
                    'amount' => $data['amount'],
                    'currency' => 'UZS',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Payment failed',
            ];
        } catch (\Exception $e) {
            Log::error('Payme payment failed', [
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
     * Create a payment method (cards management)
     */
    public function createPaymentMethod(array $data): array
    {
        try {
            $response = Http::withHeaders([
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/cards/create', [
                'card' => [
                    'number' => $data['card_number'],
                    'expire' => $data['expire'], // YYMM format
                ],
                'account' => [
                    'user_id' => $data['user_id'],
                ],
                'save' => true,
            ]);

            if ($response->successful()) {
                $result = $response->json()['result']['card'];

                return [
                    'success' => true,
                    'payment_method_id' => $result['token'],
                    'type' => 'card',
                    'card' => [
                        'brand' => $this->detectCardBrand($result['number']),
                        'last4' => substr($result['number'], -4),
                        'masked' => $result['number'],
                    ],
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to create payment method',
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
        // Payme doesn't support updating cards
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
        try {
            $response = Http::withHeaders([
                'X-Auth' => $this->merchantId . ':' . $this->secretKey,
            ])->post($this->baseUrl . '/cards/remove', [
                'token' => $paymentMethodId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array
    {
        $method = $payload['method'] ?? null;

        switch ($method) {
            case 'CheckPerformTransaction':
                return $this->checkPerformTransaction($payload);
            
            case 'CreateTransaction':
                return $this->createTransaction($payload);
            
            case 'PerformTransaction':
                return $this->performTransaction($payload);
            
            case 'CancelTransaction':
                return $this->cancelTransaction($payload);
            
            case 'CheckTransaction':
                return $this->checkTransaction($payload);
            
            default:
                return [
                    'error' => [
                        'code' => -32601,
                        'message' => 'Method not found',
                    ],
                ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        // Payme uses Basic Auth for webhooks
        $expectedAuth = base64_encode($this->merchantId . ':' . $this->secretKey);
        return $signature === 'Basic ' . $expectedAuth;
    }

    /**
     * Get provider name
     */
    public function getProviderName(): string
    {
        return 'payme';
    }

    /**
     * Map Payme status to internal status
     */
    private function mapPaymeStatus(int $state): string
    {
        $statusMap = [
            0 => 'pending',
            1 => 'waiting',
            2 => 'preauth',
            3 => 'postauth',
            4 => 'active',
            50 => 'canceled',
            51 => 'refunded',
        ];

        return $statusMap[$state] ?? 'unknown';
    }

    /**
     * Detect card brand from number
     */
    private function detectCardBrand(string $number): string
    {
        $cleaned = preg_replace('/\D/', '', $number);
        
        if (preg_match('/^4/', $cleaned)) return 'Visa';
        if (preg_match('/^5[1-5]/', $cleaned)) return 'Mastercard';
        if (preg_match('/^9860/', $cleaned)) return 'Humo';
        if (preg_match('/^8600/', $cleaned)) return 'UzCard';
        
        return 'Unknown';
    }

    /**
     * Payme webhook handlers
     */
    private function checkPerformTransaction(array $payload): array
    {
        // Validate transaction possibility
        $params = $payload['params'];
        $account = $params['account'];
        
        // Check if user exists and can make payment
        $user = \App\Models\User::find($account['user_id'] ?? 0);
        
        if (!$user) {
            return [
                'error' => [
                    'code' => -31050,
                    'message' => 'User not found',
                ],
            ];
        }

        return [
            'result' => [
                'allow' => true,
            ],
        ];
    }

    private function createTransaction(array $payload): array
    {
        $params = $payload['params'];
        
        // Create transaction record
        $transaction = \App\Models\PaymentTransaction::create([
            'user_id' => $params['account']['user_id'],
            'provider' => 'payme',
            'provider_transaction_id' => $params['id'],
            'type' => 'subscription',
            'amount' => $params['amount'] / 100,
            'currency' => 'UZS',
            'status' => 'pending',
            'provider_data' => $params,
        ]);

        return [
            'result' => [
                'create_time' => $transaction->created_at->timestamp * 1000,
                'transaction' => (string) $transaction->id,
                'state' => 1,
            ],
        ];
    }

    private function performTransaction(array $payload): array
    {
        $params = $payload['params'];
        
        $transaction = \App\Models\PaymentTransaction::where('provider_transaction_id', $params['id'])->first();
        
        if (!$transaction) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found',
                ],
            ];
        }

        $transaction->update(['status' => 'succeeded']);

        // Activate subscription
        // ... subscription activation logic

        return [
            'result' => [
                'transaction' => (string) $transaction->id,
                'perform_time' => now()->timestamp * 1000,
                'state' => 2,
            ],
        ];
    }

    private function cancelTransaction(array $payload): array
    {
        $params = $payload['params'];
        
        $transaction = \App\Models\PaymentTransaction::where('provider_transaction_id', $params['id'])->first();
        
        if (!$transaction) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found',
                ],
            ];
        }

        $transaction->update([
            'status' => 'failed',
            'failure_reason' => $params['reason']['message'] ?? 'Canceled',
        ]);

        return [
            'result' => [
                'transaction' => (string) $transaction->id,
                'cancel_time' => now()->timestamp * 1000,
                'state' => -1,
            ],
        ];
    }

    private function checkTransaction(array $payload): array
    {
        $params = $payload['params'];
        
        $transaction = \App\Models\PaymentTransaction::where('provider_transaction_id', $params['id'])->first();
        
        if (!$transaction) {
            return [
                'error' => [
                    'code' => -31003,
                    'message' => 'Transaction not found',
                ],
            ];
        }

        $stateMap = [
            'pending' => 1,
            'succeeded' => 2,
            'failed' => -1,
            'refunded' => -2,
        ];

        return [
            'result' => [
                'create_time' => $transaction->created_at->timestamp * 1000,
                'perform_time' => $transaction->status === 'succeeded' ? $transaction->updated_at->timestamp * 1000 : 0,
                'cancel_time' => $transaction->status === 'failed' ? $transaction->updated_at->timestamp * 1000 : 0,
                'transaction' => (string) $transaction->id,
                'state' => $stateMap[$transaction->status] ?? 0,
                'reason' => $transaction->failure_reason ? ['message' => $transaction->failure_reason] : null,
            ],
        ];
    }
}
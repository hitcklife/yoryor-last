<?php

namespace App\Services\Payment;

interface PaymentProviderInterface
{
    /**
     * Create a new subscription
     */
    public function createSubscription(array $data): array;

    /**
     * Update an existing subscription
     */
    public function updateSubscription(string $subscriptionId, array $data): array;

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool;

    /**
     * Get subscription details
     */
    public function getSubscription(string $subscriptionId): array;

    /**
     * Process a one-time payment
     */
    public function processPayment(array $data): array;

    /**
     * Create a payment method
     */
    public function createPaymentMethod(array $data): array;

    /**
     * Update payment method
     */
    public function updatePaymentMethod(string $paymentMethodId, array $data): array;

    /**
     * Delete payment method
     */
    public function deletePaymentMethod(string $paymentMethodId): bool;

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): array;

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Get provider name
     */
    public function getProviderName(): string;
}
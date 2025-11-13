<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
                Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions');

            // Payment provider
            $table->enum('provider', ['stripe', 'payme', 'click']);
            $table->string('provider_transaction_id')->unique();

            // Transaction details
            $table->enum('type', ['subscription', 'one_time', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);

            // Transaction status
            $table->enum('status', [
                'pending',
                'succeeded',
                'failed',
                'refunded'
            ]);

            // Additional data
            $table->json('provider_data')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('subscription_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('provider_transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

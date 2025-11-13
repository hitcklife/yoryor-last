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
                Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');

            // Payment provider
            $table->enum('payment_provider', ['stripe', 'payme', 'click', 'manual']);
            $table->string('provider_subscription_id')->nullable();

            // Subscription status
            $table->enum('status', [
                'active',
                'canceled',
                'expired',
                'past_due',
                'trialing'
            ])->default('active');

            // Billing periods
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');

            // Cancellation and trial
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('current_period_end');
            $table->index('provider_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};

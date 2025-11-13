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
        // Subscription plans table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->enum('tier', ['free', 'basic', 'gold', 'platinum']);
            $table->integer('swipes_per_day')->default(0);
            $table->integer('video_calls_per_month')->default(0);
            $table->integer('voice_calls_per_month')->default(0);
            $table->integer('max_call_duration_minutes')->default(0);
            $table->jsonb('features')->nullable(); // Additional features as JSON
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['tier', 'is_active']);
        });

        // Regional pricing for plans
        Schema::create('plan_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->string('country_code', 2);
            $table->string('currency', 3);
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable(); // For showing discounts
            $table->timestamps();
            
            $table->unique(['plan_id', 'country_code']);
            $table->index('country_code');
        });

        // User subscriptions
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans');
            $table->enum('payment_provider', ['stripe', 'payme', 'click', 'manual']);
            $table->string('provider_subscription_id')->nullable();
            $table->enum('status', ['active', 'canceled', 'expired', 'past_due', 'trialing']);
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->jsonb('metadata')->nullable(); // Provider-specific data
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('provider_subscription_id');
            $table->index('current_period_end');
        });

        // Payment transactions
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('user_subscriptions');
            $table->enum('provider', ['stripe', 'payme', 'click']);
            $table->string('provider_transaction_id');
            $table->enum('type', ['subscription', 'one_time', 'refund']);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded']);
            $table->jsonb('provider_data')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('provider_transaction_id');
        });

        // Usage limits tracking
        Schema::create('user_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('swipes_used')->default(0);
            $table->integer('likes_used')->default(0);
            $table->integer('video_calls_used')->default(0);
            $table->integer('voice_calls_used')->default(0);
            $table->integer('video_minutes_used')->default(0);
            $table->integer('voice_minutes_used')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
            $table->index('date');
        });

        // Monthly usage tracking for calls
        Schema::create('user_monthly_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('month');
            $table->integer('video_calls_count')->default(0);
            $table->integer('voice_calls_count')->default(0);
            $table->integer('video_minutes_total')->default(0);
            $table->integer('voice_minutes_total')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });

        // Subscription features
        Schema::create('subscription_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Plan features pivot table
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('subscription_features')->onDelete('cascade');
            $table->string('value')->nullable(); // For features with values like "100 per day"
            $table->timestamps();
            
            $table->unique(['plan_id', 'feature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
        Schema::dropIfExists('subscription_features');
        Schema::dropIfExists('user_monthly_usage');
        Schema::dropIfExists('user_usage_limits');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('plan_pricing');
        Schema::dropIfExists('subscription_plans');
    }
};
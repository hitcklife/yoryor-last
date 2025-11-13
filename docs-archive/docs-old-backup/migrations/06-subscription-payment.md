# Subscription and Payment System - Consolidated Migrations

This document contains consolidated migration files for the subscription and payment system.

## 1. Subscription Plans Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            // Plan details
            $table->string('name', 50);
            $table->enum('tier', ['free', 'basic', 'gold', 'platinum']);

            // Plan limits
            $table->integer('swipes_per_day')->default(10);
            $table->integer('video_calls_per_month')->default(0);
            $table->integer('voice_calls_per_month')->default(0);
            $table->integer('max_call_duration_minutes')->default(0);

            // Features
            $table->jsonb('features')->nullable();

            // Plan management
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index('tier');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
```

## 2. Plan Pricing Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');

            // Pricing details
            $table->string('country_code', 2);
            $table->string('currency', 3);
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2)->nullable();

            $table->timestamps();

            // Unique constraint for one price per plan per country
            $table->unique(['plan_id', 'country_code']);

            // Indexes
            $table->index('country_code');
            $table->index(['plan_id', 'country_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_pricing');
    }
};
```

## 3. User Subscriptions Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->jsonb('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
            $table->index(['user_id', 'status']);
            $table->index('current_period_end');
            $table->index('provider_subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
```

## 4. Payment Transactions Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->jsonb('provider_data')->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
```

## 5. User Usage Limits Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');

            // Daily usage tracking
            $table->integer('swipes_used')->default(0);
            $table->integer('likes_used')->default(0);
            $table->integer('video_calls_used')->default(0);
            $table->integer('voice_calls_used')->default(0);

            // Call minutes tracking
            $table->integer('video_minutes_used')->default(0);
            $table->integer('voice_minutes_used')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per day
            $table->unique(['user_id', 'date']);

            // Indexes
            $table->index('date');
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_usage_limits');
    }
};
```

## 6. User Monthly Usage Table Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_monthly_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Period
            $table->integer('year');
            $table->integer('month');

            // Monthly totals
            $table->integer('video_calls_count')->default(0);
            $table->integer('voice_calls_count')->default(0);
            $table->integer('video_minutes_total')->default(0);
            $table->integer('voice_minutes_total')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per month
            $table->unique(['user_id', 'year', 'month']);

            // Indexes
            $table->index(['year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_monthly_usage');
    }
};
```

## Features JSON Structure

The `features` column in subscription_plans can contain:

```json
{
  "unlimited_swipes": false,
  "see_who_likes_you": false,
  "super_likes_per_day": 0,
  "boost_per_month": 0,
  "passport": false,
  "rewind": false,
  "hide_ads": false,
  "priority_likes": false,
  "message_before_match": false,
  "advanced_filters": false,
  "incognito_mode": false,
  "profile_controls": false,
  "see_who_viewed_profile": false,
  "unlimited_backtracks": false,
  "top_picks": 0,
  "beeline_access": false
}
```

## Payment Provider Data Structure

The `provider_data` column in payment_transactions can contain provider-specific data:

```json
{
  "stripe": {
    "charge_id": "ch_xxx",
    "payment_method": "card",
    "last4": "4242"
  },
  "payme": {
    "transaction_id": "xxx",
    "card_number": "****1234"
  },
  "click": {
    "click_trans_id": "xxx",
    "merchant_trans_id": "xxx"
  }
}
```

## Usage Tracking Strategy

1. **Daily Limits** (`user_usage_limits`): Tracks daily usage against plan limits
2. **Monthly Aggregates** (`user_monthly_usage`): Stores monthly totals for billing
3. **Real-time Checks**: System checks current usage against plan limits before allowing actions

## Migration Dependencies

These tables should be created in this order:

1. subscription_plans
2. plan_pricing (depends on subscription_plans)
3. user_subscriptions (depends on users and subscription_plans)
4. payment_transactions (depends on users and user_subscriptions)
5. user_usage_limits (depends on users)
6. user_monthly_usage (depends on users)
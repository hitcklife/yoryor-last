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
            $table->json('features')->nullable();

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_pricing');
    }
};

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
                Schema::create('matchmaker_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');

            // Service details
            $table->string('name', 100);
            $table->text('description');
            $table->enum('type', [
                'basic',
                'premium',
                'vip',
                'custom'
            ]);

            // Pricing
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_period', [
                'one_time',
                'monthly',
                'quarterly',
                'yearly'
            ]);

            // Service scope
            $table->integer('duration_days');
            $table->integer('max_introductions');
            $table->json('features')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'is_active']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_services');
    }
};

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
                Schema::create('matchmakers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Business information
            $table->string('business_name')->nullable();
            $table->text('bio');
            $table->string('phone', 20)->nullable();
            $table->string('website')->nullable();

            // Expertise
            $table->json('specializations')->nullable();
            $table->json('languages')->nullable();
            $table->integer('years_experience')->default(0);

            // Performance metrics
            $table->integer('successful_matches')->default(0);
            $table->integer('total_clients')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);

            // Verification
            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected'
            ])->default('pending');
            $table->timestamp('verified_at')->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index('verification_status');
            $table->index('is_active');
            $table->index(['is_active', 'rating']);
            $table->index('success_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmakers');
    }
};

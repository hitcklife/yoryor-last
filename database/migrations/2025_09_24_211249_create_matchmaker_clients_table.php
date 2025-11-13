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
                Schema::create('matchmaker_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matchmaker_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('service_id')->constrained('matchmaker_services');

            // Contract details
            $table->date('contract_start');
            $table->date('contract_end');
            $table->enum('status', [
                'active',
                'paused',
                'completed',
                'terminated'
            ])->default('active');

            // Client preferences
            $table->json('match_preferences')->nullable();
            $table->text('special_requirements')->nullable();

            // Progress tracking
            $table->integer('introductions_made')->default(0);
            $table->integer('successful_dates')->default(0);
            $table->boolean('found_match')->default(false);

            // Notes
            $table->text('matchmaker_notes')->nullable();
            $table->text('client_feedback')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['matchmaker_id', 'status']);
            $table->index(['client_id', 'status']);
            $table->index('contract_end');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matchmaker_clients');
    }
};

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
                Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            // Report participants
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_id')->constrained('users')->onDelete('cascade');

            // Report details
            $table->string('reason');
            $table->text('description')->nullable();

            // Report status
            $table->enum('status', [
                'pending',
                'reviewing',
                'resolved',
                'dismissed'
            ])->default('pending');

            // Additional data
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes for moderation workflow
            $table->index(['reporter_id', 'created_at']);
            $table->index(['reported_id', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};

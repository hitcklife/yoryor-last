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
            $table->unsignedBigInteger('reporter_id'); // User who is reporting
            $table->unsignedBigInteger('reported_id'); // User being reported
            $table->string('reason'); // Reason for reporting (required)
            $table->text('description')->nullable(); // Additional details
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending');
            $table->json('metadata')->nullable(); // Additional data like screenshots, evidence
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reported_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add indexes for better performance
            $table->index(['reporter_id', 'created_at']);
            $table->index(['reported_id', 'created_at']);
            $table->index(['status', 'created_at']);
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

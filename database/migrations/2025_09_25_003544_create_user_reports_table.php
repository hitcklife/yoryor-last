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
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade');

            // Report details
            $table->string('category'); // Main category
            $table->string('subcategory')->nullable(); // Optional subcategory
            $table->text('description');
            $table->jsonb('evidence')->nullable(); // Array of evidence (URLs, screenshots, etc.)
            $table->jsonb('incident_details')->nullable(); // Additional incident context

            // Severity and priority
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->integer('priority_score')->default(0);
            $table->boolean('is_anonymous')->default(false);

            // Status and review
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // Admin actions
            $table->text('admin_notes')->nullable();
            $table->jsonb('actions_taken')->nullable(); // Array of actions (warning, ban, etc.)

            $table->timestamps();

            // Indexes for performance
            $table->index(['reported_user_id', 'status']);
            $table->index(['reporter_id', 'created_at']);
            $table->index(['status', 'priority_score']);
            $table->index('severity');
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

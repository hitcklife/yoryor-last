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
                Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Feedback categorization
            $table->enum('type', ['bug', 'feature', 'complaint', 'suggestion', 'other']);
            $table->string('subject');
            $table->text('message');

            // Feedback status
            $table->enum('status', [
                'pending',
                'acknowledged',
                'in_progress',
                'resolved',
                'closed'
            ])->default('pending');

            // Response
            $table->text('admin_response')->nullable();
            $table->timestamp('responded_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};

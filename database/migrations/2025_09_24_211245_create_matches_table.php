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
                Schema::create('matches', function (Blueprint $table) {
            $table->id();

            // Match participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('matched_user_id')->constrained('users')->onDelete('cascade');

            // Match details
            $table->timestamp('matched_at')->default(now());

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('matched_user_id');
            $table->index(['user_id', 'matched_at']);
            $table->index(['matched_user_id', 'matched_at']);

            // Ensure unique matches
            $table->unique(['user_id', 'matched_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};

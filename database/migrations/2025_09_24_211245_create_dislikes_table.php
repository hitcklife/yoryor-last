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
                Schema::create('dislikes', function (Blueprint $table) {
            $table->id();

            // Dislike participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('disliked_user_id')->constrained('users')->onDelete('cascade');

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('disliked_user_id');
            $table->index(['user_id', 'created_at']);

            // Ensure unique dislikes
            $table->unique(['user_id', 'disliked_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dislikes');
    }
};

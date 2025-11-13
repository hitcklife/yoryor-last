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
                Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // Like participants
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('liked_user_id')->constrained('users')->onDelete('cascade');

            // Like details
            $table->timestamp('liked_at')->default(now());

            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('liked_user_id');
            $table->index(['user_id', 'liked_at']);
            $table->index(['liked_user_id', 'created_at']);

            // Ensure unique likes
            $table->unique(['user_id', 'liked_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};

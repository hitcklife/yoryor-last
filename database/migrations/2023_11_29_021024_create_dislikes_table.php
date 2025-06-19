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
            $table->unsignedBigInteger('user_id'); // ID of the user who is giving the dislike
            $table->unsignedBigInteger('disliked_user_id'); // ID of the user being disliked
            $table->timestamps();

            // Indexing for faster lookups
            $table->index('user_id');
            $table->index('disliked_user_id');

            // Foreign keys should reference 'id' on the 'users' table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('disliked_user_id')->references('id')->on('users')->onDelete('cascade');

            // Prevent the same user from disliking the same person more than once
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

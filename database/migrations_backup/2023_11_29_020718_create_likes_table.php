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
            $table->unsignedBigInteger('user_id'); // ID of the user who is giving the like
            $table->unsignedBigInteger('liked_user_id'); // ID of the user being liked
            $table->timestamp('liked_at')->useCurrent();
            $table->timestamps();

            // Indexing for faster lookups
            $table->index('user_id');
            $table->index('liked_user_id');

            // Foreign keys should reference 'id' on the 'users' table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('liked_user_id')->references('id')->on('users')->onDelete('cascade');

            // Prevent the same user from liking the same person more than once
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

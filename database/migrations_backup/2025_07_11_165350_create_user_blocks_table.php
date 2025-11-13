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
        Schema::create('user_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blocker_id'); // User who is blocking
            $table->unsignedBigInteger('blocked_id'); // User being blocked
            $table->string('reason')->nullable(); // Optional reason for blocking
            $table->timestamps();

            // Add foreign key constraints
            $table->foreign('blocker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('users')->onDelete('cascade');
            
            // Prevent duplicate blocks
            $table->unique(['blocker_id', 'blocked_id']);
            
            // Add indexes for better performance
            $table->index(['blocker_id', 'created_at']);
            $table->index(['blocked_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blocks');
    }
};

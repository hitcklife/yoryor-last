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

            // Block participants
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade');

            // Block details
            $table->string('reason')->nullable();

            $table->timestamps();

            // Ensure unique blocks
            $table->unique(['blocker_id', 'blocked_id']);

            // Indexes for performance
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

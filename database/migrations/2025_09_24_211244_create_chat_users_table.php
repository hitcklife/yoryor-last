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
                Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // User settings
            $table->boolean('is_muted')->default(false);
            $table->timestamp('last_read_at')->nullable();

            // Participation tracking
            $table->timestamp('joined_at')->default(now());
            $table->timestamp('left_at')->nullable();
            $table->string('role')->default('member');

            $table->timestamps();

            // Unique constraint
            $table->unique(['chat_id', 'user_id']);

            // Performance indexes
            $table->index(['user_id', 'is_muted']);
            $table->index(['chat_id', 'left_at']);
            $table->index(['user_id', 'left_at', 'last_read_at']);
            $table->index(['chat_id', 'left_at', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};

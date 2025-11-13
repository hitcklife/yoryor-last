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
                Schema::create('chats', function (Blueprint $table) {
            $table->id();

            // Chat properties
            $table->string('type')->default('private');
            $table->string('name')->nullable();
            $table->string('description')->nullable();

            // Activity tracking
            $table->timestamp('last_activity_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('message_count')->default(0);
            $table->string('last_message_type')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['type', 'is_active']);
            $table->index('last_activity_at');
            $table->index(['is_active', 'last_activity_at']);
            $table->index(['type', 'is_active', 'last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};

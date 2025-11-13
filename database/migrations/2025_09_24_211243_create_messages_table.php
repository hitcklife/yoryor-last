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
                Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');

            // Reply functionality
            $table->foreignId('reply_to_message_id')
                  ->nullable()
                  ->constrained('messages')
                  ->onDelete('set null');

            // Call integration
            $table->foreignId('call_id')
                  ->nullable()
                  ->constrained('calls')
                  ->onDelete('set null');

            // Message content
            $table->text('content')->nullable();
            $table->string('message_type')->default('text');

            // Media support
            $table->json('media_data')->nullable();
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();

            // Status tracking
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('sent_at')->default(now());

            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['chat_id', 'sent_at']);
            $table->index(['sender_id', 'sent_at']);
            $table->index(['chat_id', 'message_type']);
            $table->index(['chat_id', 'deleted_at', 'sent_at']);
            $table->index(['call_id', 'sent_at']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['message_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

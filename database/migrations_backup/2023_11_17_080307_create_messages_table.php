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

            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reply_to_message_id')->nullable()->constrained('messages')->onDelete('set null');

            $table->text('content')->nullable();
            $table->string('message_type')->default('text'); // 'text', 'image', 'video', 'audio', 'file', 'location'
            $table->json('media_data')->nullable(); // Store media info, dimensions, etc.
            $table->string('media_url')->nullable();
            $table->string('thumbnail_url')->nullable();

            // Message status
            $table->enum('status', ['sent', 'delivered', 'failed'])->default('sent');
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();

            $table->timestamp('sent_at')->useCurrent();
            $table->softDeletes(); // Allow message deletion
            $table->timestamps();

            // Indexes for performance
            $table->index(['chat_id', 'sent_at']);
            $table->index(['sender_id', 'sent_at']);
            $table->index(['chat_id', 'message_type']);
            $table->index(['chat_id', 'deleted_at', 'sent_at']);
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

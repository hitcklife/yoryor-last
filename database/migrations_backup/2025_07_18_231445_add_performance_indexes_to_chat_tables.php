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
        // Note: The chats table already has an index on last_activity_at from the original migration
        // We'll add additional performance indexes here

        // Add indexes for messages table
        Schema::table('messages', function (Blueprint $table) {
            // Composite index for chat messages ordered by creation
            $table->index(['chat_id', 'created_at'], 'idx_messages_chat_created');
            
            // Index for sender lookups
            $table->index(['sender_id', 'created_at'], 'idx_messages_sender_created');
            
            // Index for sent_at ordering
            $table->index('sent_at', 'idx_messages_sent_at');
            
            // Index for unread message queries
            $table->index(['chat_id', 'sender_id'], 'idx_messages_chat_sender');
        });

        // Add indexes for message_reads table
        Schema::table('message_reads', function (Blueprint $table) {
            // Composite index for read status checks
            $table->index(['message_id', 'user_id'], 'idx_message_reads_message_user');
            
            // Index for user's read messages
            $table->index(['user_id', 'read_at'], 'idx_message_reads_user_read');
        });

        // Add indexes for chat_users table if it exists
        if (Schema::hasTable('chat_users')) {
            Schema::table('chat_users', function (Blueprint $table) {
                // Index for user's chats
                $table->index(['user_id', 'created_at'], 'idx_chat_users_user_created');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove indexes from messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_chat_created');
            $table->dropIndex('idx_messages_sender_created');
            $table->dropIndex('idx_messages_sent_at');
            $table->dropIndex('idx_messages_chat_sender');
        });

        // Remove indexes from message_reads table
        Schema::table('message_reads', function (Blueprint $table) {
            $table->dropIndex('idx_message_reads_message_user');
            $table->dropIndex('idx_message_reads_user_read');
        });

        // Remove indexes from chat_users table if it exists
        if (Schema::hasTable('chat_users')) {
            Schema::table('chat_users', function (Blueprint $table) {
                $table->dropIndex('idx_chat_users_user_created');
            });
        }
    }
};

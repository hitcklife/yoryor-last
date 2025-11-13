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
        Schema::table('messages', function (Blueprint $table) {
            // Remove the read column since we're using message_reads table
            if (Schema::hasColumn('messages', 'read')) {
                $table->dropColumn('read');
            }
            
            // Add additional indexes for better performance
            $table->index(['chat_id', 'sender_id', 'sent_at'], 'messages_chat_sender_sent_idx');
            $table->index(['sender_id', 'message_type'], 'messages_sender_type_idx');
        });
        
        // Optimize message_reads table indexes
        Schema::table('message_reads', function (Blueprint $table) {
            // Add composite index for better performance on unread queries
            $table->index(['user_id', 'message_id'], 'message_reads_user_message_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Add back the read column
            $table->boolean('read')->default(false)->after('status');
            
            // Drop the indexes we added
            $table->dropIndex('messages_chat_sender_sent_idx');
            $table->dropIndex('messages_sender_type_idx');
        });
        
        Schema::table('message_reads', function (Blueprint $table) {
            $table->dropIndex('message_reads_user_message_idx');
        });
    }
};
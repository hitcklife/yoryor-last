<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Optimize user_activities table with additional indexes
        Schema::table('user_activities', function (Blueprint $table) {
            // Add composite indexes for efficient querying
            $table->index(['user_id', 'activity_type', 'created_at'], 'activities_user_type_time');
            $table->index(['activity_type', 'user_id'], 'activities_type_user');
            
            // Index for recent activities queries
            $table->index(['created_at', 'activity_type'], 'activities_time_type');
            
            // Index for chat-related activity queries
            $table->index(['activity_type', 'created_at'], 'activities_type_time');
        });

        // Add computed columns to users table for better performance
        Schema::table('users', function (Blueprint $table) {
            // Add computed online status column
            $table->boolean('is_currently_online')->default(false)->after('last_active_at');
            
            // Add engagement score cache column
            $table->decimal('engagement_score', 8, 2)->default(0)->after('is_currently_online');
            
            // Add last activity type for quick reference
            $table->string('last_activity_type')->nullable()->after('engagement_score');
            
            // Add composite indexes for online users and engagement
            $table->index(['is_currently_online', 'last_active_at'], 'users_online_status');
            $table->index(['engagement_score', 'is_currently_online'], 'users_engagement_online');
        });

        // Optimize chats table for better activity tracking
        Schema::table('chats', function (Blueprint $table) {
            // Add message count cache
            $table->integer('message_count')->default(0)->after('is_active');
            
            // Add last message type for quick reference
            $table->string('last_message_type')->nullable()->after('message_count');
            
            // Add composite indexes for active chats ordering
            $table->index(['is_active', 'last_activity_at'], 'chats_active_activity');
            $table->index(['type', 'is_active', 'last_activity_at'], 'chats_type_active_activity');
        });

        // Optimize messages table for activity queries
        Schema::table('messages', function (Blueprint $table) {
            // Add index for recent messages per user
            $table->index(['sender_id', 'created_at'], 'messages_sender_time');
            
            // Add index for message type analytics
            $table->index(['message_type', 'created_at'], 'messages_type_time');
        });

        // Create indexes on chat_users table for activity queries
        Schema::table('chat_users', function (Blueprint $table) {
            // Add index for active users in chats
            $table->index(['user_id', 'left_at', 'last_read_at'], 'chat_users_activity');
            $table->index(['chat_id', 'left_at', 'role'], 'chat_users_chat_active');
        });

        // Add trigger to automatically update online status (MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('
                CREATE TRIGGER update_user_online_status 
                BEFORE UPDATE ON users 
                FOR EACH ROW 
                BEGIN
                    IF NEW.last_active_at != OLD.last_active_at THEN
                        SET NEW.is_currently_online = (NEW.last_active_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE));
                    END IF;
                END
            ');
        }

        // Add trigger to update chat message count (MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('
                CREATE TRIGGER update_chat_message_count 
                AFTER INSERT ON messages 
                FOR EACH ROW 
                BEGIN
                    UPDATE chats 
                    SET message_count = message_count + 1,
                        last_message_type = NEW.message_type
                    WHERE id = NEW.chat_id;
                END
            ');
        }

        // Create a view for active user statistics (MySQL/PostgreSQL)
        if (in_array(DB::getDriverName(), ['mysql', 'pgsql'])) {
            DB::statement('
                CREATE VIEW active_user_stats AS
                SELECT 
                    u.id,
                    u.email,
                    u.is_currently_online,
                    u.last_active_at,
                    u.engagement_score,
                    COUNT(DISTINCT c.id) as active_chats_count,
                    COUNT(DISTINCT m.id) as messages_sent_today,
                    COUNT(DISTINCT ua.id) as activities_today
                FROM users u
                LEFT JOIN chat_users cu ON u.id = cu.user_id AND cu.left_at IS NULL
                LEFT JOIN chats c ON cu.chat_id = c.id AND c.is_active = 1
                LEFT JOIN messages m ON u.id = m.sender_id AND DATE(m.created_at) = CURDATE()
                LEFT JOIN user_activities ua ON u.id = ua.user_id AND DATE(ua.created_at) = CURDATE()
                WHERE u.registration_completed = 1 
                AND u.deleted_at IS NULL
                GROUP BY u.id, u.email, u.is_currently_online, u.last_active_at, u.engagement_score
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views first
        if (in_array(DB::getDriverName(), ['mysql', 'pgsql'])) {
            DB::statement('DROP VIEW IF EXISTS active_user_stats');
        }

        // Drop triggers (MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::unprepared('DROP TRIGGER IF EXISTS update_user_online_status');
            DB::unprepared('DROP TRIGGER IF EXISTS update_chat_message_count');
        }

        // Remove indexes and columns from user_activities
        Schema::table('user_activities', function (Blueprint $table) {
            $table->dropIndex('activities_user_type_time');
            $table->dropIndex('activities_type_user');
            $table->dropIndex('activities_time_type');
            $table->dropIndex('activities_type_time');
        });

        // Remove columns and indexes from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_online_status');
            $table->dropIndex('users_engagement_online');
            $table->dropColumn(['is_currently_online', 'engagement_score', 'last_activity_type']);
        });

        // Remove columns and indexes from chats
        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex('chats_active_activity');
            $table->dropIndex('chats_type_active_activity');
            $table->dropColumn(['message_count', 'last_message_type']);
        });

        // Remove indexes from messages
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('messages_sender_time');
            $table->dropIndex('messages_type_time');
        });

        // Remove indexes from chat_users
        Schema::table('chat_users', function (Blueprint $table) {
            $table->dropIndex('chat_users_activity');
            $table->dropIndex('chat_users_chat_active');
        });
    }
};
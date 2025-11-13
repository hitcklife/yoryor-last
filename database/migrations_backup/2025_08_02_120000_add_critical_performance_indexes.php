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
        // User activity and status indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('last_active_at', 'idx_users_last_active');
            $table->index(['registration_completed', 'disabled_at'], 'idx_users_registration_status');
            $table->index(['last_active_at', 'registration_completed'], 'idx_users_active_registration');
            $table->index(['created_at', 'registration_completed'], 'idx_users_created_completed');
        });

        // Likes and dislikes performance indexes
        Schema::table('likes', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_likes_user_created');
            $table->index(['liked_user_id', 'created_at'], 'idx_likes_target_created');
            $table->index(['user_id', 'liked_user_id', 'created_at'], 'idx_likes_user_target_created');
        });

        Schema::table('dislikes', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_dislikes_user_created');
            $table->index(['disliked_user_id', 'created_at'], 'idx_dislikes_target_created');
        });

        // Matches optimization
        Schema::table('matches', function (Blueprint $table) {
            $table->index(['user_id', 'matched_user_id', 'created_at'], 'idx_matches_mutual_created');
            $table->index(['user_id', 'created_at'], 'idx_matches_user_created');
            $table->index(['matched_user_id', 'created_at'], 'idx_matches_target_created');
        });

        // Stories performance
        Schema::table('user_stories', function (Blueprint $table) {
            $table->index(['status', 'expires_at', 'created_at'], 'idx_stories_active_expires');
            $table->index(['user_id', 'status', 'expires_at'], 'idx_stories_user_active');
            $table->index(['created_at', 'status'], 'idx_stories_created_status');
        });

        // Photos optimization
        Schema::table('user_photos', function (Blueprint $table) {
            $table->index(['user_id', 'is_profile_photo', 'status'], 'idx_photos_profile_status');
            $table->index(['user_id', 'is_private', 'status', 'order'], 'idx_photos_visibility');
            $table->index(['status', 'created_at'], 'idx_photos_status_created');
        });

        // Chat and messaging performance
        Schema::table('chats', function (Blueprint $table) {
            $table->index(['is_active', 'updated_at'], 'idx_chats_active_updated');
            $table->index(['type', 'is_active', 'created_at'], 'idx_chats_type_active');
        });

        // Skip messages indexes if they already exist from previous migration
        if (!$this->indexExists('messages', 'idx_messages_chat_created')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['chat_id', 'created_at'], 'idx_messages_chat_created');
            });
        }
        
        if (!$this->indexExists('messages', 'idx_messages_sender_created')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->index(['sender_id', 'created_at'], 'idx_messages_sender_created');
            });
        }
        
        Schema::table('messages', function (Blueprint $table) {
            if (!$this->indexExists('messages', 'idx_messages_chat_type')) {
                $table->index(['chat_id', 'message_type', 'created_at'], 'idx_messages_chat_type');
            }
        });

        // Skip message_reads indexes if they already exist from previous migration
        if (!$this->indexExists('message_reads', 'idx_message_reads_message_user')) {
            Schema::table('message_reads', function (Blueprint $table) {
                $table->index(['message_id', 'user_id'], 'idx_message_reads_message_user');
            });
        }
        
        Schema::table('message_reads', function (Blueprint $table) {
            if (!$this->indexExists('message_reads', 'idx_message_reads_user_read')) {
                $table->index(['user_id', 'read_at'], 'idx_message_reads_user_read');
            }
        });

        // Profile and preferences
        Schema::table('profiles', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_profiles_status_created');
            $table->index(['gender', 'status'], 'idx_profiles_gender_status');
            $table->index(['country_id', 'status'], 'idx_profiles_country_status');
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->index(['user_id', 'updated_at'], 'idx_preferences_user_updated');
        });

        // Panic and safety system
        Schema::table('panic_activations', function (Blueprint $table) {
            $table->index(['status', 'triggered_at'], 'idx_panic_status_triggered');
            $table->index(['user_id', 'status', 'triggered_at'], 'idx_panic_user_status');
            $table->index(['trigger_type', 'status'], 'idx_panic_type_status');
        });

        Schema::table('user_emergency_contacts', function (Blueprint $table) {
            $table->index(['user_id', 'priority_order', 'is_verified'], 'idx_emergency_priority');
            $table->index(['user_id', 'is_verified'], 'idx_emergency_verified');
        });

        // Verification system
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->index(['status', 'submitted_at'], 'idx_verification_status_submitted');
            $table->index(['user_id', 'verification_type', 'status'], 'idx_verification_user_type');
            $table->index(['reviewed_by', 'reviewed_at'], 'idx_verification_reviewer');
        });

        // Device tokens for notifications
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_device_tokens_user_created');
            $table->index(['device_type', 'user_id'], 'idx_device_tokens_type_user');
        });

        // Blocked users (only if table exists)
        if (Schema::hasTable('blocked_users')) {
            Schema::table('blocked_users', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'idx_blocked_users_user_created');
                $table->index(['blocked_user_id', 'created_at'], 'idx_blocked_users_target_created');
            });
        }

        // Settings optimization (only if table exists)
        if (Schema::hasTable('user_settings')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->index(['user_id', 'updated_at'], 'idx_settings_user_updated');
            });
        }

        // Calls optimization (only if table exists)
        if (Schema::hasTable('calls')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->index(['status', 'created_at'], 'idx_calls_status_created');
                $table->index(['caller_id', 'status'], 'idx_calls_caller_status');
                $table->index(['receiver_id', 'status'], 'idx_calls_receiver_status');
                $table->index(['channel_name', 'status'], 'idx_calls_channel_status');
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $driver = DB::getDriverName();
            
            if ($driver === 'pgsql') {
                return DB::table('pg_indexes')
                    ->where('tablename', $table)
                    ->where('indexname', $indexName)
                    ->exists();
            } elseif ($driver === 'mysql') {
                return DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]) !== [];
            } elseif ($driver === 'sqlite') {
                // For SQLite, check sqlite_master table
                $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name = ?", [$indexName]);
                return !empty($result);
            } else {
                // For other drivers, assume index doesn't exist
                return false;
            }
        } catch (\Exception $e) {
            // If there's any error checking, assume index doesn't exist
            return false;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes for users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_last_active');
            $table->dropIndex('idx_users_registration_status');
            $table->dropIndex('idx_users_online_status');
            $table->dropIndex('idx_users_created_completed');
        });

        // Drop indexes for likes table
        Schema::table('likes', function (Blueprint $table) {
            $table->dropIndex('idx_likes_user_created');
            $table->dropIndex('idx_likes_target_created');
            $table->dropIndex('idx_likes_user_target_created');
        });

        // Drop indexes for dislikes table
        Schema::table('dislikes', function (Blueprint $table) {
            $table->dropIndex('idx_dislikes_user_created');
            $table->dropIndex('idx_dislikes_target_created');
        });

        // Drop indexes for matches table
        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex('idx_matches_mutual_created');
            $table->dropIndex('idx_matches_user_created');
            $table->dropIndex('idx_matches_target_created');
        });

        // Drop indexes for user_stories table
        Schema::table('user_stories', function (Blueprint $table) {
            $table->dropIndex('idx_stories_active_expires');
            $table->dropIndex('idx_stories_user_active');
            $table->dropIndex('idx_stories_created_status');
        });

        // Drop indexes for user_photos table
        Schema::table('user_photos', function (Blueprint $table) {
            $table->dropIndex('idx_photos_profile_status');
            $table->dropIndex('idx_photos_visibility');
            $table->dropIndex('idx_photos_status_created');
        });

        // Drop indexes for chats table
        Schema::table('chats', function (Blueprint $table) {
            $table->dropIndex('idx_chats_active_updated');
            $table->dropIndex('idx_chats_type_active');
        });

        // Drop indexes for messages table
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex('idx_messages_chat_created');
            $table->dropIndex('idx_messages_sender_created');
            $table->dropIndex('idx_messages_chat_type');
        });

        // Drop indexes for message_reads table
        Schema::table('message_reads', function (Blueprint $table) {
            $table->dropIndex('idx_message_reads_user_chat');
            $table->dropIndex('idx_message_reads_message_user');
        });

        // Drop indexes for profiles table
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropIndex('idx_profiles_status_created');
            $table->dropIndex('idx_profiles_gender_status');
            $table->dropIndex('idx_profiles_country_status');
        });

        // Drop indexes for user_preferences table
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropIndex('idx_preferences_user_updated');
        });

        // Drop indexes for panic_activations table
        Schema::table('panic_activations', function (Blueprint $table) {
            $table->dropIndex('idx_panic_status_triggered');
            $table->dropIndex('idx_panic_user_status');
            $table->dropIndex('idx_panic_type_status');
        });

        // Drop indexes for user_emergency_contacts table
        Schema::table('user_emergency_contacts', function (Blueprint $table) {
            $table->dropIndex('idx_emergency_priority');
            $table->dropIndex('idx_emergency_verified');
        });

        // Drop indexes for verification_requests table
        Schema::table('verification_requests', function (Blueprint $table) {
            $table->dropIndex('idx_verification_status_submitted');
            $table->dropIndex('idx_verification_user_type');
            $table->dropIndex('idx_verification_reviewer');
        });

        // Drop indexes for device_tokens table
        Schema::table('device_tokens', function (Blueprint $table) {
            $table->dropIndex('idx_device_tokens_user_active');
            $table->dropIndex('idx_device_tokens_platform');
        });

        // Drop indexes for blocked_users table
        Schema::table('blocked_users', function (Blueprint $table) {
            $table->dropIndex('idx_blocked_users_user_created');
            $table->dropIndex('idx_blocked_users_target_created');
        });

        // Drop indexes for user_settings table
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropIndex('idx_settings_user_updated');
        });

        // Drop indexes for calls table
        Schema::table('calls', function (Blueprint $table) {
            $table->dropIndex('idx_calls_status_created');
            $table->dropIndex('idx_calls_caller_status');
            $table->dropIndex('idx_calls_receiver_status');
            $table->dropIndex('idx_calls_channel_status');
        });
    }
};
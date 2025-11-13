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
        // Drop and recreate the column with all activity types
        // This approach works for both PostgreSQL and MySQL
        if (Schema::hasTable('user_activities') && Schema::hasColumn('user_activities', 'activity_type')) {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->dropColumn('activity_type');
            });
        }
        
        if (Schema::hasTable('user_activities')) {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->enum('activity_type', [
                    'login', 'logout', 'swipe_right', 'swipe_left',
                    'message_sent', 'profile_view', 'photo_upload',
                    'match_made', 'profile_updated', 'chat_opened', 
                    'messages_read', 'typing', 'online_status', 
                    'dating_browsing', 'chat_presence_joined', 
                    'api_request'
                ])->after('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original activity types
        if (Schema::hasTable('user_activities') && Schema::hasColumn('user_activities', 'activity_type')) {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->dropColumn('activity_type');
            });
        }
        
        if (Schema::hasTable('user_activities')) {
            Schema::table('user_activities', function (Blueprint $table) {
                $table->enum('activity_type', [
                    'login', 'logout', 'swipe_right', 'swipe_left',
                    'message_sent', 'profile_view', 'photo_upload',
                    'match_made', 'profile_updated'
                ])->after('user_id');
            });
        }
    }
}; 
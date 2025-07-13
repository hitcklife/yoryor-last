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
        Schema::table('user_preferences', function (Blueprint $table) {
            // Check if fields exist before adding them
            if (!Schema::hasColumn('user_preferences', 'must_haves')) {
                $table->json('must_haves')->nullable()->after('deal_breakers');
            }
            
            if (!Schema::hasColumn('user_preferences', 'distance_unit')) {
                $table->enum('distance_unit', ['km', 'miles'])->default('km')->after('must_haves');
            }
            
            if (!Schema::hasColumn('user_preferences', 'show_me_globally')) {
                $table->boolean('show_me_globally')->default(false)->after('distance_unit');
            }
            
            if (!Schema::hasColumn('user_preferences', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable()->after('show_me_globally');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn([
                'must_haves',
                'distance_unit',
                'show_me_globally',
                'notification_preferences'
            ]);
        });
    }
};

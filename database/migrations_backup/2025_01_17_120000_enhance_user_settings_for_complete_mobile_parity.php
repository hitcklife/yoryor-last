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
        Schema::table('user_settings', function (Blueprint $table) {
            // Emergency Contacts (new feature)
            $table->json('emergency_contacts')->nullable()->after('share_with_partners');
            
            // Missing mobile app fields that need to be added to fillable/UI
            // Note: Most fields already exist in the table but need UI implementation
            
            // Account Information Display
            $table->string('device_token')->nullable()->after('emergency_contacts');
            $table->timestamp('last_login_at')->nullable()->after('device_token');
            $table->json('login_devices')->nullable()->after('last_login_at'); // Store recent login devices
            
            // Enhanced Safety Features  
            $table->boolean('panic_button_enabled')->default(false)->after('login_devices');
            $table->json('safety_contacts')->nullable()->after('panic_button_enabled'); // Different from emergency contacts
            
            // Enhanced Discovery Settings
            $table->string('show_me_gender')->default('all')->after('safety_contacts'); // men, women, non_binary, all
            
            // Data Export Tracking
            $table->timestamp('last_data_export_at')->nullable()->after('show_me_gender');
            $table->boolean('data_export_requested')->default(false)->after('last_data_export_at');
            
            // Verification Status Tracking
            $table->timestamp('photo_verified_at')->nullable()->after('data_export_requested');
            $table->timestamp('id_verified_at')->nullable()->after('photo_verified_at');
            $table->timestamp('phone_verified_at')->nullable()->after('id_verified_at');
            $table->timestamp('social_verified_at')->nullable()->after('phone_verified_at');
            
            // Enhanced Notification Controls
            $table->boolean('quiet_hours_enabled')->default(false)->after('social_verified_at');
            $table->json('notification_methods')->nullable()->after('quiet_hours_enabled'); // push, email, sms, etc.
            
            // Indexes for new searchable fields
            $table->index('panic_button_enabled');
            $table->index('show_me_gender'); 
            $table->index('quiet_hours_enabled');
            $table->index('data_export_requested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_settings', function (Blueprint $table) {
            $table->dropColumn([
                'emergency_contacts',
                'device_token',
                'last_login_at', 
                'login_devices',
                'panic_button_enabled',
                'safety_contacts',
                'show_me_gender',
                'last_data_export_at',
                'data_export_requested',
                'photo_verified_at',
                'id_verified_at', 
                'phone_verified_at',
                'social_verified_at',
                'quiet_hours_enabled',
                'notification_methods'
            ]);
        });
    }
};
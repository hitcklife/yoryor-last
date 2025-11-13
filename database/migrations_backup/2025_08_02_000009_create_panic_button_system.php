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
        // Panic button activations
        Schema::create('panic_activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('trigger_type', [
                'emergency_contact',
                'location_sharing',
                'fake_call',
                'silent_alarm',
                'safe_word',
                'date_check_in'
            ]);
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address')->nullable();
            $table->decimal('location_accuracy', 10, 2)->nullable(); // In meters
            $table->jsonb('device_info')->nullable(); // Device details
            $table->jsonb('context_data')->nullable(); // Date info, match details, etc.
            $table->text('user_message')->nullable(); // Optional message from user
            $table->enum('status', ['active', 'resolved', 'false_alarm', 'escalated'])->default('active');
            $table->timestamp('triggered_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->boolean('authorities_contacted')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'triggered_at']);
            $table->index(['status', 'triggered_at']);
            $table->index(['latitude', 'longitude']);
        });

        // Emergency contacts
        Schema::create('user_emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('relationship'); // 'parent', 'sibling', 'friend', 'partner', etc.
            $table->string('phone');
            $table->string('email')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('receives_panic_alerts')->default(true);
            $table->boolean('receives_location_updates')->default(false);
            $table->boolean('receives_date_check_ins')->default(false);
            $table->jsonb('notification_preferences')->nullable();
            $table->integer('priority_order')->default(1);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verification_code')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'phone']);
            $table->index(['user_id', 'is_primary']);
        });

        // Date safety check-ins
        Schema::create('date_check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_user_id')->nullable()->constrained('users');
            $table->string('date_type'); // 'first_date', 'meet_up', 'video_call', etc.
            $table->timestamp('planned_start_time');
            $table->timestamp('planned_end_time');
            $table->timestamp('check_in_required_at'); // When user should check in
            $table->decimal('planned_latitude', 10, 8)->nullable();
            $table->decimal('planned_longitude', 11, 8)->nullable();
            $table->string('planned_location_name')->nullable();
            $table->text('date_details')->nullable();
            $table->enum('status', ['scheduled', 'active', 'checked_in', 'overdue', 'panic_triggered', 'completed'])->default('scheduled');
            $table->timestamp('actual_check_in_at')->nullable();
            $table->decimal('check_in_latitude', 10, 8)->nullable();
            $table->decimal('check_in_longitude', 11, 8)->nullable();
            $table->text('check_in_message')->nullable();
            $table->enum('safety_status', ['safe', 'concerned', 'help_needed'])->nullable();
            $table->boolean('auto_check_in_enabled')->default(true);
            $table->integer('reminder_minutes_before')->default(30);
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['check_in_required_at', 'status']);
            $table->index(['planned_latitude', 'planned_longitude']);
            $table->index(['check_in_latitude', 'check_in_longitude']);
        });

        // Safety features configuration
        Schema::create('user_safety_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('panic_button_enabled')->default(true);
            $table->boolean('location_sharing_enabled')->default(false);
            $table->boolean('emergency_contacts_enabled')->default(true);
            $table->boolean('date_check_ins_enabled')->default(false);
            $table->boolean('fake_call_enabled')->default(true);
            $table->string('safe_word')->nullable();
            $table->integer('check_in_interval_minutes')->default(60); // How often to check in during dates
            $table->boolean('auto_location_sharing')->default(false);
            $table->jsonb('trigger_phrases')->nullable(); // Phrases that trigger panic
            $table->boolean('share_with_family')->default(false);
            $table->boolean('share_with_friends')->default(false);
            $table->jsonb('advanced_settings')->nullable();
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Panic button notifications log
        Schema::create('panic_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('panic_activation_id')->constrained()->onDelete('cascade');
            $table->string('recipient_type'); // 'emergency_contact', 'admin', 'authorities'
            $table->string('recipient_identifier'); // phone, email, or user_id
            $table->enum('notification_method', ['sms', 'call', 'email', 'push', 'whatsapp']);
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'read'])->default('pending');
            $table->text('message_content')->nullable();
            $table->jsonb('response_data')->nullable(); // API response
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['panic_activation_id', 'status']);
            $table->index(['recipient_type', 'status']);
        });

        // Safety tips and educational content
        Schema::create('safety_tips', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'first_date', 'online_safety', 'meeting_strangers', etc.
            $table->string('title');
            $table->text('content');
            $table->text('short_description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->jsonb('applicable_situations')->nullable(); // When to show this tip
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['priority', 'is_active']);
        });

        // User safety tip interactions
        Schema::create('user_safety_tip_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('safety_tip_id')->constrained()->onDelete('cascade');
            $table->enum('interaction_type', ['viewed', 'liked', 'shared', 'dismissed']);
            $table->timestamp('interacted_at');
            $table->timestamps();
            
            $table->unique(['user_id', 'safety_tip_id', 'interaction_type']);
            $table->index(['safety_tip_id', 'interaction_type']);
        });

        // Add panic-related fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('panic_button_active')->default(false);
            $table->timestamp('last_panic_activation')->nullable();
            $table->integer('panic_activation_count')->default(0);
            $table->boolean('safety_features_enabled')->default(true);
            $table->timestamp('last_safety_check_in')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'panic_button_active',
                'last_panic_activation',
                'panic_activation_count',
                'safety_features_enabled',
                'last_safety_check_in'
            ]);
        });

        Schema::dropIfExists('user_safety_tip_interactions');
        Schema::dropIfExists('safety_tips');
        Schema::dropIfExists('panic_notifications');
        Schema::dropIfExists('user_safety_settings');
        Schema::dropIfExists('date_check_ins');
        Schema::dropIfExists('user_emergency_contacts');
        Schema::dropIfExists('panic_activations');
    }
};
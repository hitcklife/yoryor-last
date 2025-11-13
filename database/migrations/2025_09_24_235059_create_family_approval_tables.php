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
        // Family member accounts linked to main user
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('family_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('relationship', ['parent', 'sibling', 'guardian', 'relative']);
            $table->string('relationship_detail')->nullable(); // e.g., "Mother", "Elder Brother"
            $table->boolean('can_approve_matches')->default(true);
            $table->boolean('can_view_chats')->default(false);
            $table->boolean('can_block_users')->default(true);
            $table->enum('status', ['pending', 'active', 'deactivated'])->default('pending');
            $table->timestamp('invited_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'family_user_id']);
            $table->index(['family_user_id', 'status']);
        });

        // Family approval requests for matches
        Schema::create('family_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('match_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->text('reason')->nullable(); // Reason for approval/rejection
            $table->text('notes')->nullable(); // Family member notes
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'match_user_id', 'status']);
            $table->index(['family_member_id', 'status']);
        });

        // Family approval settings
        Schema::create('family_approval_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('require_approval_before_chat')->default(false);
            $table->boolean('require_approval_before_meeting')->default(true);
            $table->integer('approval_timeout_hours')->default(72); // Auto-approve after timeout
            $table->integer('min_approvals_required')->default(1);
            $table->boolean('notify_family_on_match')->default(true);
            $table->boolean('notify_family_on_first_message')->default(true);
            $table->boolean('show_family_approved_badge')->default(true);
            $table->jsonb('approval_criteria')->nullable(); // Custom criteria for auto-approval
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Family member activity logs
        Schema::create('family_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_member_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'approved_match', 'rejected_match', 'blocked_user', etc.
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->jsonb('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['family_member_id', 'created_at']);
            $table->index(['user_id', 'action']);
        });

        // Add family-related columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_family_member')->default(false);
            $table->boolean('family_approval_enabled')->default(false);
            $table->timestamp('family_verified_at')->nullable();
        });

        // Add family approval status to matches
        Schema::table('matches', function (Blueprint $table) {
            $table->enum('family_approval_status', ['not_required', 'pending', 'approved', 'rejected'])
                  ->default('not_required');
            $table->timestamp('family_approved_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['family_approval_status', 'family_approved_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_family_member', 'family_approval_enabled', 'family_verified_at']);
        });

        Schema::dropIfExists('family_activity_logs');
        Schema::dropIfExists('family_approval_settings');
        Schema::dropIfExists('family_approvals');
        Schema::dropIfExists('family_members');
    }
};

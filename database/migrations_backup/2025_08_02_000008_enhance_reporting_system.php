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
        // Enhanced user reports with more categories and better tracking
        Schema::create('enhanced_user_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('cascade');
            $table->enum('category', [
                'inappropriate_behavior',
                'harassment',
                'fake_profile',
                'spam',
                'inappropriate_photos',
                'scam_attempt',
                'hate_speech',
                'violence_threat',
                'underage',
                'stolen_photos',
                'catfishing',
                'inappropriate_messages',
                'offline_behavior',
                'other'
            ]);
            $table->enum('subcategory', [
                // Inappropriate behavior
                'sexual_harassment', 'verbal_abuse', 'persistent_messaging',
                // Fake profile
                'fake_photos', 'false_information', 'impersonation',
                // Inappropriate photos
                'nudity', 'sexually_explicit', 'violent_content',
                // Scam
                'financial_scam', 'romance_scam', 'identity_theft',
                // Other
                'technical_issue', 'policy_violation', 'other_reason'
            ])->nullable();
            $table->text('description');
            $table->jsonb('evidence')->nullable(); // Screenshots, chat logs, etc.
            $table->jsonb('incident_details')->nullable(); // Date, location, context
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'under_review', 'resolved', 'dismissed', 'escalated'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->jsonb('actions_taken')->nullable(); // What actions were taken
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->integer('priority_score')->default(0); // Calculated priority
            $table->timestamps();
            
            $table->index(['reporter_id', 'created_at']);
            $table->index(['reported_user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index(['severity', 'status']);
            $table->index(['priority_score', 'status']);
        });

        // Report evidence/attachments
        Schema::create('report_evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('enhanced_user_reports')->onDelete('cascade');
            $table->enum('evidence_type', ['screenshot', 'chat_log', 'photo', 'video', 'document', 'audio']);
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->text('description')->nullable();
            $table->jsonb('metadata')->nullable(); // Additional info like timestamp, location
            $table->timestamps();
            
            $table->index(['report_id', 'evidence_type']);
        });

        // Safety incidents (broader than just reports)
        Schema::create('safety_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // User involved
            $table->foreignId('reported_by')->nullable()->constrained('users'); // Who reported it
            $table->enum('incident_type', [
                'harassment',
                'safety_concern',
                'policy_violation',
                'technical_abuse',
                'community_guidelines',
                'terms_violation',
                'privacy_breach',
                'security_issue'
            ]);
            $table->text('description');
            $table->jsonb('incident_data')->nullable(); // Structured data about incident
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->jsonb('investigation_notes')->nullable();
            $table->jsonb('actions_taken')->nullable();
            $table->timestamp('incident_date');
            $table->foreignId('assigned_to')->nullable()->constrained('users'); // Admin assigned
            $table->timestamps();
            
            $table->index(['user_id', 'incident_type']);
            $table->index(['risk_level', 'status']);
            $table->index(['assigned_to', 'status']);
        });

        // User safety scores
        Schema::create('user_safety_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('trust_score')->default(100); // 0-100, starts at 100
            $table->integer('report_count')->default(0);
            $table->integer('verified_report_count')->default(0);
            $table->integer('false_report_count')->default(0);
            $table->integer('community_flags')->default(0);
            $table->integer('positive_feedback')->default(0);
            $table->date('last_incident_date')->nullable();
            $table->enum('risk_category', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->jsonb('score_breakdown')->nullable(); // Detailed scoring
            $table->timestamp('last_calculated_at');
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index(['trust_score', 'risk_category']);
        });

        // Automated safety flags
        Schema::create('automated_safety_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('flag_type'); // 'suspicious_activity', 'inappropriate_content', etc.
            $table->string('detection_method'); // 'ai_content_scan', 'behavior_analysis', etc.
            $table->text('flag_reason');
            $table->jsonb('detection_data')->nullable(); // AI confidence, patterns detected
            $table->decimal('confidence_score', 5, 2)->default(0); // 0-100
            $table->enum('status', ['active', 'reviewed', 'dismissed', 'confirmed'])->default('active');
            $table->boolean('requires_human_review')->default(true);
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'flag_type']);
            $table->index(['status', 'confidence_score']);
            $table->index(['requires_human_review', 'created_at']);
        });

        // Report categories configuration
        Schema::create('report_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_key')->unique();
            $table->string('name');
            $table->text('description');
            $table->jsonb('subcategories')->nullable();
            $table->integer('severity_weight')->default(1); // For calculating priority
            $table->boolean('requires_immediate_action')->default(false);
            $table->boolean('auto_escalate')->default(false);
            $table->jsonb('required_evidence')->nullable(); // What evidence is needed
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add safety-related fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_flagged')->default(false);
            $table->integer('safety_score')->default(100);
            $table->enum('account_status', ['active', 'warning', 'restricted', 'suspended', 'banned'])->default('active');
            $table->timestamp('last_safety_check')->nullable();
            $table->jsonb('safety_restrictions')->nullable(); // What they can't do
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_flagged',
                'safety_score',
                'account_status',
                'last_safety_check',
                'safety_restrictions'
            ]);
        });

        Schema::dropIfExists('report_categories');
        Schema::dropIfExists('automated_safety_flags');
        Schema::dropIfExists('user_safety_scores');
        Schema::dropIfExists('safety_incidents');
        Schema::dropIfExists('report_evidence');
        Schema::dropIfExists('enhanced_user_reports');
    }
};
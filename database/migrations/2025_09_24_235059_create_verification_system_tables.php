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
        // User verification badges
        Schema::create('user_verified_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('badge_type', [
                'phone_verified',
                'email_verified', 
                'identity_verified',
                'photo_verified',
                'employment_verified',
                'education_verified',
                'income_verified',
                'address_verified',
                'social_verified',
                'background_check',
                'premium_member',
                'influencer',
                'matchmaker_verified'
            ]);
            $table->enum('status', ['pending', 'verified', 'rejected', 'expired'])->default('pending');
            $table->jsonb('verification_data')->nullable(); // Store verification documents/info
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users'); // Admin who verified
            $table->timestamps();
            
            $table->unique(['user_id', 'badge_type']);
            $table->index(['user_id', 'status']);
            $table->index(['badge_type', 'status']);
        });

        // Verification requests/documents
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('verification_type', [
                'identity',
                'photo',
                'employment',
                'education', 
                'income',
                'address',
                'social_media',
                'background_check'
            ]);
            $table->enum('status', ['pending', 'approved', 'rejected', 'needs_review'])->default('pending');
            $table->jsonb('documents')->nullable(); // Store file paths/URLs
            $table->jsonb('submitted_data')->nullable(); // Form data submitted
            $table->text('user_notes')->nullable(); // User's explanation
            $table->text('admin_feedback')->nullable(); // Admin's feedback/rejection reason
            $table->timestamp('submitted_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['user_id', 'verification_type']);
            $table->index(['status', 'submitted_at']);
        });

        // Add verification fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('verification_score')->default(0); // Overall verification score
            $table->boolean('is_verified')->default(false); // Basic verification status
            $table->boolean('is_premium_verified')->default(false); // Premium verification
            $table->timestamp('last_verification_check')->nullable();
        });

        // Verification requirements configuration
        Schema::create('verification_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('requirement_key')->unique(); // e.g., 'basic_verification', 'premium_verification'
            $table->string('name');
            $table->text('description');
            $table->jsonb('required_badges'); // List of badge types required
            $table->integer('min_score')->default(0); // Minimum verification score
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'verification_score',
                'is_verified',
                'is_premium_verified',
                'last_verification_check'
            ]);
        });

        Schema::dropIfExists('verification_requirements');
        Schema::dropIfExists('verification_requests');
        Schema::dropIfExists('user_verified_badges');
    }
};

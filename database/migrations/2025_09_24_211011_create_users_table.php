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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('profile_uuid')->unique()->nullable();

            // Authentication fields
            $table->string('email', 255)->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('password', 60)->nullable(); // Nullable for social login
            $table->string('remember_token')->nullable();

            // Verification fields
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Social login fields
            $table->string('avatar')->nullable();
            $table->string('provider')->nullable();
            $table->string('google_id', 100)->nullable()->index();
            $table->string('facebook_id', 100)->nullable()->index();

            // User status fields
            $table->boolean('registration_completed')->default(false);
            $table->boolean('is_private')->default(false);
            $table->timestamp('disabled_at')->nullable()->index();

            // Activity tracking
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_currently_online')->default(false);
            $table->decimal('engagement_score', 8, 2)->default(0);
            $table->string('last_activity_type')->nullable();

            // Preferences
            $table->string('language_preference', 10)->default('en');

            // Matchmaker system (FK added later after matchmakers table)
            $table->boolean('is_matchmaker')->default(false);
            $table->boolean('prefers_matchmaker')->default(false);
            $table->unsignedBigInteger('assigned_matchmaker_id')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for performance
            $table->index(['registration_completed', 'disabled_at', 'is_private'], 'users_active_profiles_index');
            $table->index(['last_active_at', 'registration_completed'], 'users_activity_index');
            $table->index(['is_currently_online', 'last_active_at'], 'users_online_status');
            $table->index(['engagement_score', 'is_currently_online'], 'users_engagement_online');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

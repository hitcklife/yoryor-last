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
            $table->string('email', 255)->unique()->nullable();
            $table->string('phone', 20)->unique()->nullable();
            $table->string('google_id', 100)->nullable()->index();
            $table->string('facebook_id', 100)->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('disabled_at')->nullable()->index();
            $table->boolean('registration_completed')->default(false);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_private')->default(false);
            $table->string('password', 60)->nullable();
            $table->rememberToken();
            $table->string('profile_photo_path', 1024)->nullable();
            $table->timestamp('last_active_at')->nullable(); // New field for activity tracking
            $table->softDeletes();
            $table->timestamps();

            // Composite indexes for dating app queries
            $table->index(['registration_completed', 'disabled_at', 'is_private'], 'users_active_profiles_index');
            $table->index(['last_active_at', 'registration_completed'], 'users_activity_index');
            $table->index('created_at'); // For new user queries
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

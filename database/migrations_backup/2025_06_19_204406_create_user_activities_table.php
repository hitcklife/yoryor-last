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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('activity_type', [
                'login', 'logout', 'swipe_right', 'swipe_left',
                'message_sent', 'profile_view', 'photo_upload',
                'match_made', 'profile_updated'
            ]);
            $table->json('metadata')->nullable(); // Additional activity data
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Partitioning-ready indexes
            $table->index(['user_id', 'created_at'], 'activities_user_timeline');
            $table->index(['activity_type', 'created_at'], 'activities_type_timeline');
            $table->index('created_at'); // For cleanup jobs
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};

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

            // Activity details
            $table->enum('activity_type', [
                'login',
                'logout',
                'swipe_right',
                'swipe_left',
                'message_sent',
                'profile_view',
                'photo_upload',
                'match_made',
                'profile_updated'
            ]);

            // Additional data
            $table->json('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamp('created_at')->default(now());

            // Extensive indexing for analytics
            $table->index(['user_id', 'created_at']);
            $table->index(['activity_type', 'created_at']);
            $table->index('created_at');
            $table->index(['user_id', 'activity_type', 'created_at']);
            $table->index(['activity_type', 'user_id']);
            $table->index(['created_at', 'activity_type']);
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

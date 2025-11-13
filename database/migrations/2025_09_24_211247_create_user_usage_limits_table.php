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
                Schema::create('user_usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');

            // Daily usage tracking
            $table->integer('swipes_used')->default(0);
            $table->integer('likes_used')->default(0);
            $table->integer('video_calls_used')->default(0);
            $table->integer('voice_calls_used')->default(0);

            // Call minutes tracking
            $table->integer('video_minutes_used')->default(0);
            $table->integer('voice_minutes_used')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per day
            $table->unique(['user_id', 'date']);

            // Indexes
            $table->index('date');
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_usage_limits');
    }
};

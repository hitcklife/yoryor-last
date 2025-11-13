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
                Schema::create('user_monthly_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Period
            $table->integer('year');
            $table->integer('month');

            // Monthly totals
            $table->integer('video_calls_count')->default(0);
            $table->integer('voice_calls_count')->default(0);
            $table->integer('video_minutes_total')->default(0);
            $table->integer('voice_minutes_total')->default(0);

            $table->timestamps();

            // Unique constraint for one record per user per month
            $table->unique(['user_id', 'year', 'month']);

            // Indexes
            $table->index(['year', 'month']);
            $table->index(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_monthly_usage');
    }
};

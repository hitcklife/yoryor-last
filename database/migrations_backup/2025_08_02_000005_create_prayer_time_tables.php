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
        // User prayer time preferences
        Schema::create('user_prayer_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('fajr_time')->nullable();
            $table->time('dhuhr_time')->nullable();
            $table->time('asr_time')->nullable();
            $table->time('maghrib_time')->nullable();
            $table->time('isha_time')->nullable();
            $table->boolean('notification_enabled')->default(false);
            $table->integer('notification_minutes_before')->default(15);
            $table->string('preferred_calculation_method')->default('isna');
            $table->string('timezone')->default('UTC');
            $table->timestamps();
            
            $table->unique('user_id');
        });

        // Add prayer-related fields to cultural profiles
        Schema::table('user_cultural_profiles', function (Blueprint $table) {
            $table->boolean('observes_ramadan')->nullable();
            $table->boolean('prefers_halal_dates')->nullable();
            $table->enum('mosque_attendance', ['weekly', 'occasionally', 'rarely', 'never'])->nullable();
            $table->boolean('quran_reading')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_cultural_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'observes_ramadan',
                'prefers_halal_dates',
                'mosque_attendance',
                'quran_reading'
            ]);
        });
        
        Schema::dropIfExists('user_prayer_times');
    }
};
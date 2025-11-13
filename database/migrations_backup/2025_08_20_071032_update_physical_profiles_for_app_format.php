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
        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Drop existing enum columns to recreate with updated values
            $table->dropColumn([
                'fitness_level',
                'drinking_status'
            ]);
        });

        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Add new fields that your app sends
            $table->enum('smoking_habit', [
                'never', 
                'socially', 
                'regularly', 
                'trying_to_quit'
            ])->nullable();
            
            $table->enum('drinking_habit', [
                'never', 
                'socially', 
                'occasionally', 
                'regularly'
            ])->nullable();
            
            $table->enum('exercise_frequency', [
                'never', 
                'rarely', 
                '1_2_week', 
                '3_4_week', 
                'daily'
            ])->nullable();
            
            $table->enum('diet_preference', [
                'everything', 
                'vegetarian', 
                'vegan', 
                'halal', 
                'kosher', 
                'pescatarian', 
                'keto'
            ])->nullable();
            
            $table->enum('pet_preference', [
                'love_pets', 
                'have_pets', 
                'allergic', 
                'dont_like', 
                'no_preference'
            ])->nullable();
            
            $table->json('hobbies')->nullable();
            
            $table->string('sleep_schedule')->nullable();
            
            // Update fitness_level to match exercise_frequency
            $table->enum('fitness_level', [
                'never', 
                'rarely', 
                '1_2_week', 
                '3_4_week', 
                'daily'
            ])->nullable();
            
            // Keep legacy drinking_status but with updated values
            $table->enum('drinking_status', [
                'never', 
                'socially', 
                'occasionally', 
                'regularly'
            ])->nullable();
            
            // Add diet field for legacy mapping
            $table->enum('diet', [
                'everything', 
                'vegetarian', 
                'vegan', 
                'halal', 
                'kosher', 
                'pescatarian', 
                'keto'
            ])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'smoking_habit',
                'drinking_habit',
                'exercise_frequency',
                'diet_preference',
                'pet_preference',
                'hobbies',
                'sleep_schedule',
                'fitness_level',
                'drinking_status',
                'diet'
            ]);
        });

        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Restore original columns
            $table->enum('fitness_level', ['very_active', 'active', 'moderate', 'sedentary'])->nullable();
            $table->enum('drinking_status', ['never', 'socially', 'regularly', 'only_special_occasions'])->nullable();
        });
    }
};

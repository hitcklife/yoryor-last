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
                Schema::create('user_physical_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Physical attributes
            $table->unsignedSmallInteger('height')->nullable(); // in centimeters
            $table->decimal('weight', 5, 2)->nullable(); // in kg

            // Lifestyle habits
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

            // Health preferences
            $table->json('dietary_restrictions')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('height');
            $table->index('fitness_level');
            $table->index('smoking_habit');
            $table->index('drinking_habit');
            $table->index('diet_preference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_physical_profiles');
    }
};

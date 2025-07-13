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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('height')->nullable(); // in cm
            $table->enum('body_type', ['slim', 'athletic', 'average', 'curvy', 'plus_size'])->nullable();
            $table->string('hair_color', 50)->nullable();
            $table->string('eye_color', 50)->nullable();
            $table->enum('fitness_level', ['very_active', 'active', 'moderate', 'sedentary'])->nullable();
            $table->json('dietary_restrictions')->nullable();
            $table->enum('smoking_status', ['never', 'socially', 'regularly', 'trying_to_quit'])->nullable();
            $table->enum('drinking_status', ['never', 'socially', 'regularly', 'only_special_occasions'])->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['body_type', 'fitness_level']);
            $table->index(['smoking_status', 'drinking_status']);
            $table->index('height');
            $table->unique('user_id'); // One physical profile per user
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

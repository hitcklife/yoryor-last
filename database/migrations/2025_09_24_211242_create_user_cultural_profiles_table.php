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
                Schema::create('user_cultural_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Language preferences
            $table->json('native_languages')->nullable();
            $table->json('spoken_languages')->nullable();
            $table->string('preferred_communication_language', 50)->nullable();

            // Religious preferences
            $table->enum('religion', [
                'muslim',
                'christian',
                'secular',
                'other',
                'prefer_not_to_say'
            ])->nullable();

            $table->enum('religiousness_level', [
                'very_religious',
                'moderately_religious',
                'not_religious',
                'prefer_not_to_say'
            ])->nullable();

            // Cultural identity
            $table->string('ethnicity', 100)->nullable();
            $table->string('uzbek_region', 100)->nullable();

            // Lifestyle preferences
            $table->enum('lifestyle_type', [
                'traditional',
                'modern',
                'mix_of_both'
            ])->nullable();

            $table->enum('gender_role_views', [
                'traditional',
                'modern',
                'flexible'
            ])->nullable();

            // Cultural practices
            $table->string('traditional_clothing_comfort')->nullable();
            $table->enum('uzbek_cuisine_knowledge', [
                'expert',
                'good',
                'basic',
                'learning'
            ])->nullable();

            $table->enum('cultural_events_participation', [
                'very_active',
                'active',
                'sometimes',
                'rarely'
            ])->nullable();

            // Religious practices
            $table->boolean('halal_lifestyle')->nullable();
            $table->string('quran_reading')->nullable();

            $table->timestamps();

            // Indexes for matching
            $table->index(['religion', 'religiousness_level']);
            $table->index(['lifestyle_type', 'gender_role_views']);
            $table->index('uzbek_region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cultural_profiles');
    }
};

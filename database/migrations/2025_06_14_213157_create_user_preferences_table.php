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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('search_radius')->default(10);
            $table->string('country', 2)->nullable(); // Using ISO 2-letter country code
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->unsignedTinyInteger('min_age')->nullable()->check('min_age >= 18');
            $table->unsignedTinyInteger('max_age')->nullable()->check('max_age <= 120');
            $table->json('languages_spoken')->nullable();
            $table->json('hobbies_interests')->nullable();
            $table->timestamps();

            // Composite index for age range queries
            $table->index(['min_age', 'max_age', 'gender', 'country'], 'user_preferences_search_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

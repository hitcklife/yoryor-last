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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->enum('gender', ['male', 'female', 'non-binary', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable(); // Computed age for faster queries
            $table->string('city', 85)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->unsignedInteger('country_id')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('profession')->nullable(); // Dating app bio
            $table->text('bio')->nullable(); // Dating app bio
            $table->json('interests')->nullable(); // User interests as JSON
            $table->enum('looking_for', ['casual', 'serious', 'friendship', 'all'])->default('all');
            $table->unsignedInteger('profile_views')->default(0); // Track profile views
            $table->timestamp('profile_completed_at')->nullable(); // When profile was completed

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');

            // Optimized indexes for matching algorithms
            $table->index(['gender', 'age', 'city'], 'profiles_matching_index');
            $table->index(['latitude', 'longitude', 'gender'], 'profiles_location_index');
            $table->index(['looking_for', 'age'], 'profiles_intent_index');
            $table->index('profile_completed_at');
            $table->index('profile_views'); // For popularity sorting

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};

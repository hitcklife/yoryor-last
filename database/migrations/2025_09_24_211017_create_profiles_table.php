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
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Basic information
            $table->string('first_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->enum('gender', ['male', 'female', 'non-binary', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->unsignedTinyInteger('age')->nullable();

            // Location
            $table->string('city', 85)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('province', 50)->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('country_code', 10)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Profile content
            $table->text('bio')->nullable();
            $table->json('interests')->nullable();
            $table->enum('looking_for_relationship', ['casual', 'serious', 'friendship', 'open'])
                ->default('open');

            // Professional
            $table->string('occupation', 100)->nullable();
            $table->string('profession', 100)->nullable();
            $table->string('status', 50)->nullable();

            // Profile metrics
            $table->integer('profile_views')->default(0);
            $table->timestamp('profile_completed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes for matching and search
            $table->index(['gender', 'age', 'city'], 'profiles_matching_index');
            $table->index(['latitude', 'longitude', 'gender'], 'profiles_location_index');
            $table->index(['looking_for_relationship', 'age'], 'profiles_intent_index');
            $table->index('profile_completed_at');
            $table->index('profile_views');
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

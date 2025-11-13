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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Search preferences
            $table->unsignedSmallInteger('search_radius')->default(25);
            $table->string('country', 2)->nullable();
            $table->json('preferred_genders')->nullable();
            $table->json('hobbies_interests')->nullable();

            // Age preferences
            $table->unsignedTinyInteger('min_age')->nullable()->default(18);
            $table->unsignedTinyInteger('max_age')->nullable()->default(35);

            // Language preferences
            $table->json('languages_spoken')->nullable();

            // Matching preferences
            $table->json('deal_breakers')->nullable();
            $table->json('must_haves')->nullable();

            // Display preferences
            $table->enum('distance_unit', ['km', 'miles'])->default('km');
            $table->boolean('show_me_globally')->default(false);

            // Notification preferences
            $table->json('notification_preferences')->nullable();

            $table->timestamps();

            // Indexes for search
            $table->index(['search_radius', 'country']);
            $table->index(['min_age', 'max_age']);
            $table->index('show_me_globally');
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

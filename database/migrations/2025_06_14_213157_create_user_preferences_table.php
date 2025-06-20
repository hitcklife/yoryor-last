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
            $table->unsignedSmallInteger('search_radius_km')->default(25); // More specific naming
            $table->string('country', 2)->nullable(); // Using ISO 2-letter country code
            $table->json('preferred_genders')->nullable(); // Support multiple gender preferences
            $table->unsignedTinyInteger('min_age')->nullable()->check('min_age >= 18');
            $table->unsignedTinyInteger('max_age')->nullable()->check('max_age <= 120');
            $table->json('languages_spoken')->nullable();
            $table->json('deal_breakers')->nullable(); // What they don't want
            $table->json('must_haves')->nullable(); // What they require
            $table->enum('distance_unit', ['km', 'miles'])->default('km');
            $table->boolean('show_me_globally')->default(false); // For premium users
            $table->json('notification_preferences')->nullable(); // Push notification settings
            $table->timestamps();

            // Optimized composite indexes
            $table->index(['search_radius_km', 'country'], 'preferences_location_index');
            $table->index(['min_age', 'max_age'], 'preferences_age_index');
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

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
        Schema::create('user_family_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('family_importance', ['very_important', 'important', 'somewhat_important', 'not_important'])->nullable();
            $table->enum('wants_children', ['yes', 'no', 'maybe', 'have_and_want_more', 'have_and_dont_want_more'])->nullable();
            $table->unsignedTinyInteger('number_of_children_wanted')->nullable();
            $table->boolean('living_with_family')->nullable();
            $table->boolean('family_approval_important')->nullable();
            $table->enum('marriage_timeline', ['within_1_year', '1_2_years', '2_5_years', 'someday', 'never'])->nullable();
            $table->unsignedTinyInteger('previous_marriages')->default(0);
            $table->enum('homemaker_preference', ['yes', 'no', 'flexible', 'both_work'])->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['family_importance', 'wants_children']);
            $table->index(['marriage_timeline', 'previous_marriages']);
            $table->index('homemaker_preference');
            $table->unique('user_id'); // One family preference per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_family_preferences');
    }
};

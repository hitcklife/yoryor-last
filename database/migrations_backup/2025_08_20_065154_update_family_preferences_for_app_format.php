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
        Schema::table('user_family_preferences', function (Blueprint $table) {
            // Drop existing enum columns to recreate with new values
            $table->dropColumn([
                'family_importance',
                'wants_children',
                'marriage_timeline',
                'homemaker_preference'
            ]);
        });

        Schema::table('user_family_preferences', function (Blueprint $table) {
            // Add new fields that your app sends
            $table->enum('marriage_intention', [
                'seeking_marriage', 
                'open_to_marriage', 
                'not_ready_yet', 
                'undecided'
            ])->nullable();
            
            $table->enum('children_preference', [
                'want_children', 
                'have_and_want_more', 
                'have_and_dont_want_more', 
                'dont_want_children', 
                'undecided'
            ])->nullable();
            
            $table->unsignedTinyInteger('current_children')->default(0);
            
            $table->json('family_values')->nullable();
            
            $table->enum('living_situation', [
                'alone', 
                'with_family', 
                'with_roommates', 
                'with_partner', 
                'other'
            ])->nullable();
            
            $table->text('family_involvement')->nullable();
            
            $table->enum('marriage_timeline', [
                'within_6_months', 
                'within_1_year', 
                'within_2_years', 
                'within_5_years', 
                'no_timeline'
            ])->nullable();
            
            $table->enum('family_importance', [
                'extremely_important', 
                'very_important', 
                'moderately_important', 
                'somewhat_important', 
                'not_important'
            ])->nullable();
            
            $table->enum('homemaker_preference', [
                'prefer_traditional_roles', 
                'both_work_equally', 
                'flexible_arrangement', 
                'career_focused', 
                'no_preference'
            ])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_family_preferences', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'marriage_intention',
                'children_preference',
                'current_children',
                'family_values',
                'living_situation',
                'family_involvement',
                'marriage_timeline',
                'family_importance',
                'homemaker_preference'
            ]);
        });

        Schema::table('user_family_preferences', function (Blueprint $table) {
            // Restore original columns
            $table->enum('family_importance', ['very_important', 'important', 'somewhat_important', 'not_important'])->nullable();
            $table->enum('wants_children', ['yes', 'no', 'maybe', 'have_and_want_more', 'have_and_dont_want_more'])->nullable();
            $table->enum('marriage_timeline', ['within_1_year', '1_2_years', '2_5_years', 'someday', 'never'])->nullable();
            $table->enum('homemaker_preference', ['yes', 'no', 'flexible', 'both_work'])->nullable();
        });
    }
};

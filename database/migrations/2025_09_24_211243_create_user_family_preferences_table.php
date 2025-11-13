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
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Marriage intentions
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
            $table->unsignedTinyInteger('number_of_children_wanted')->nullable();
            
            $table->json('family_values')->nullable();
            
            $table->enum('living_situation', [
                'alone', 
                'with_family', 
                'with_roommates', 
                'with_partner', 
                'other'
            ])->nullable();
            
            $table->text('family_involvement')->nullable();
            $table->boolean('living_with_family')->nullable();
            $table->boolean('family_approval_important')->nullable();

            // Marriage timeline
            $table->enum('marriage_timeline', [
                'within_6_months', 
                'within_1_year', 
                'within_2_years', 
                'within_5_years', 
                'no_timeline'
            ])->nullable();

            // Family values
            $table->enum('family_importance', [
                'extremely_important', 
                'very_important', 
                'moderately_important', 
                'somewhat_important', 
                'not_important'
            ])->nullable();

            // Previous relationships
            $table->unsignedTinyInteger('previous_marriages')->default(0);

            // Work preferences
            $table->enum('homemaker_preference', [
                'prefer_traditional_roles', 
                'both_work_equally', 
                'flexible_arrangement', 
                'undecided'
            ])->nullable();

            $table->timestamps();

            // Indexes
            $table->index('family_importance');
            $table->index('marriage_intention');
            $table->index('children_preference');
            $table->index('marriage_timeline');
            $table->index('living_situation');
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

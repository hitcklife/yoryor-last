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
        Schema::table('user_career_profiles', function (Blueprint $table) {
            // Drop existing enum columns to recreate with updated values
            $table->dropColumn([
                'education_level',
                'income_range'
            ]);
        });

        Schema::table('user_career_profiles', function (Blueprint $table) {
            // Add new fields that your app sends
            $table->enum('education_level', [
                'high_school', 
                'associate', 
                'bachelor', 
                'master', 
                'doctorate', 
                'professional', 
                'trade_school', 
                'other'
            ])->nullable();
            
            $table->string('field_of_study')->nullable();
            
            $table->enum('work_status', [
                'full_time', 
                'part_time', 
                'self_employed', 
                'freelance', 
                'student', 
                'unemployed', 
                'retired'
            ])->nullable();
            
            $table->string('occupation')->nullable();
            $table->string('employer')->nullable();
            
            $table->json('career_goals')->nullable();
            
            $table->enum('income_range', [
                'under_25k', 
                '25k_50k', 
                '50k_75k', 
                '75k_100k', 
                '100k_150k', 
                '150k_plus', 
                'prefer_not_to_say'
            ])->nullable();
            
            // Legacy fields for backward compatibility
            $table->string('profession')->nullable(); // Maps to occupation
            $table->string('company')->nullable(); // Maps to employer
            $table->string('job_title')->nullable(); // Maps to occupation
            $table->string('income')->nullable(); // Maps to income_range
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_career_profiles', function (Blueprint $table) {
            // Drop new columns
            $table->dropColumn([
                'education_level',
                'field_of_study',
                'work_status',
                'occupation',
                'employer',
                'career_goals',
                'income_range',
                'profession',
                'company',
                'job_title',
                'income'
            ]);
        });

        Schema::table('user_career_profiles', function (Blueprint $table) {
            // Restore original columns
            $table->enum('education_level', ['high_school', 'bachelors', 'masters', 'phd', 'vocational', 'other'])->nullable();
            $table->enum('income_range', ['prefer_not_to_say', 'under_25k', '25k_50k', '50k_75k', '75k_100k', '100k_plus'])->nullable();
        });
    }
};

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
                Schema::create('user_career_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Education
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
            $table->string('university_name', 200)->nullable();
            
            // Work information
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
            $table->string('job_title')->nullable();
            
            $table->json('career_goals')->nullable();

            // Financial information
            $table->enum('income_range', [
                'under_25k', 
                '25k_50k', 
                '50k_75k', 
                '75k_100k', 
                '100k_150k', 
                '150k_plus', 
                'prefer_not_to_say'
            ])->nullable();

            $table->boolean('owns_property')->nullable();
            $table->text('financial_goals')->nullable();
            
            // Legacy fields for backward compatibility
            $table->string('profession')->nullable(); // Maps to occupation
            $table->string('company')->nullable(); // Maps to employer
            $table->string('income')->nullable(); // Maps to income_range

            $table->timestamps();

            // Indexes
            $table->index('education_level');
            $table->index('income_range');
            $table->index('work_status');
            $table->index('field_of_study');
            $table->index('occupation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_career_profiles');
    }
};

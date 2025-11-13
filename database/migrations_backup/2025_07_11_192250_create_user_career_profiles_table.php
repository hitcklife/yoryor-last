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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('education_level', ['high_school', 'bachelors', 'masters', 'phd', 'vocational', 'other'])->nullable();
            $table->string('university_name', 200)->nullable();
            $table->enum('income_range', ['prefer_not_to_say', 'under_25k', '25k_50k', '50k_75k', '75k_100k', '100k_plus'])->nullable();
            $table->boolean('owns_property')->nullable();
            $table->text('financial_goals')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['education_level', 'income_range']);
            $table->index('owns_property');
            $table->index('university_name');
            $table->unique('user_id'); // One career profile per user
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

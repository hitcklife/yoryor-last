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
        Schema::create('user_location_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('immigration_status', ['citizen', 'permanent_resident', 'work_visa', 'student', 'other'])->nullable();
            $table->unsignedTinyInteger('years_in_current_country')->nullable();
            $table->enum('plans_to_return_uzbekistan', ['yes', 'no', 'maybe', 'for_visits'])->nullable();
            $table->enum('uzbekistan_visit_frequency', ['yearly', 'every_few_years', 'rarely', 'never'])->nullable();
            $table->boolean('willing_to_relocate')->nullable();
            $table->json('relocation_countries')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['immigration_status', 'willing_to_relocate']);
            $table->index(['plans_to_return_uzbekistan', 'uzbekistan_visit_frequency']);
            $table->index('years_in_current_country');
            $table->unique('user_id'); // One location preference per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_location_preferences');
    }
};

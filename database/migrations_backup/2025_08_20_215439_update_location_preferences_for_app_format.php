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
        Schema::table('user_location_preferences', function (Blueprint $table) {
            // Drop existing enum columns to recreate with updated values
            $table->dropColumn([
                'immigration_status',
                'plans_to_return_uzbekistan',
                'uzbekistan_visit_frequency',
                'willing_to_relocate'
            ]);
        });

        Schema::table('user_location_preferences', function (Blueprint $table) {
            // Add updated enum fields with new values
            $table->enum('immigration_status', [
                'citizen',
                'permanent_resident', 
                'work_visa',
                'student_visa',
                'tourist_visa',
                'asylum_refugee',
                'other'
            ])->nullable();
            
            $table->enum('plans_to_return_uzbekistan', [
                'definitely_yes',
                'probably_yes',
                'maybe',
                'probably_no',
                'definitely_no',
                'undecided'
            ])->nullable();
            
            $table->enum('uzbekistan_visit_frequency', [
                'never',
                'rarely',
                'annually',
                'twice_yearly',
                'quarterly',
                'monthly',
                'frequently'
            ])->nullable();
            
            $table->enum('willing_to_relocate', [
                'no',
                'within_city',
                'within_state',
                'within_country',
                'internationally',
                'for_right_person'
            ])->nullable();
            
            // Add new fields
            $table->json('preferred_locations')->nullable();
            $table->boolean('live_with_family')->nullable();
            $table->text('future_location_plans')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_location_preferences', function (Blueprint $table) {
            // Drop new fields
            $table->dropColumn([
                'immigration_status',
                'plans_to_return_uzbekistan',
                'uzbekistan_visit_frequency',
                'willing_to_relocate',
                'preferred_locations',
                'live_with_family',
                'future_location_plans'
            ]);
        });

        Schema::table('user_location_preferences', function (Blueprint $table) {
            // Restore original columns
            $table->enum('immigration_status', ['citizen', 'permanent_resident', 'work_visa', 'student', 'other'])->nullable();
            $table->enum('plans_to_return_uzbekistan', ['yes', 'no', 'maybe', 'for_visits'])->nullable();
            $table->enum('uzbekistan_visit_frequency', ['yearly', 'every_few_years', 'rarely', 'never'])->nullable();
            $table->boolean('willing_to_relocate')->nullable();
        });
    }
};

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
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            // Immigration status
            $table->enum('immigration_status', [
                'citizen',
                'permanent_resident', 
                'work_visa',
                'student_visa',
                'tourist_visa',
                'asylum_refugee',
                'other'
            ])->nullable();

            $table->unsignedTinyInteger('years_in_current_country')->nullable();

            // Future plans
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

            // Relocation preferences
            $table->enum('willing_to_relocate', [
                'no',
                'within_city',
                'within_state',
                'within_country',
                'internationally',
                'for_right_person'
            ])->nullable();
            
            $table->json('relocation_countries')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->boolean('live_with_family')->nullable();
            $table->text('future_location_plans')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('immigration_status');
            $table->index('willing_to_relocate');
            $table->index('plans_to_return_uzbekistan');
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

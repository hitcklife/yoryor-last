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
        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Add weight field (in kg)
            $table->decimal('weight', 5, 2)->nullable(); // e.g., 75.50 kg
            
            // Remove appearance-related fields that are not needed
            $table->dropColumn([
                'body_type',
                'hair_color', 
                'eye_color'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_physical_profiles', function (Blueprint $table) {
            // Remove weight field
            $table->dropColumn('weight');
            
            // Restore appearance fields
            $table->enum('body_type', ['slim', 'athletic', 'average', 'curvy', 'plus_size'])->nullable();
            $table->string('hair_color', 50)->nullable();
            $table->string('eye_color', 50)->nullable();
        });
    }
};

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
        Schema::table('user_cultural_profiles', function (Blueprint $table) {
            // Change traditional_clothing_comfort from boolean to string
            $table->string('traditional_clothing_comfort')->nullable()->change();
            
            // Change quran_reading from boolean to string  
            $table->string('quran_reading')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_cultural_profiles', function (Blueprint $table) {
            // Revert traditional_clothing_comfort back to boolean
            $table->boolean('traditional_clothing_comfort')->nullable()->change();
            
            // Revert quran_reading back to boolean
            $table->boolean('quran_reading')->nullable()->change();
        });
    }
};

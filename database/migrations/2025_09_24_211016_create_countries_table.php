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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique();
            $table->string('flag')->nullable();
            $table->string('phone_code', 10);
            $table->string('phone_template')->nullable();

            // Localization fields
            $table->string('timezone')->nullable();
            $table->enum('time_format', ['12', '24'])->default('24');
            $table->json('unit_measurements')->nullable(); // {distance: 'km', temperature: 'celsius'}

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
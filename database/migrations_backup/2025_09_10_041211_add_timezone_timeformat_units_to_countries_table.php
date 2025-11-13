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
        Schema::table('countries', function (Blueprint $table) {
            $table->string('timezone')->nullable()->after('phone_template');
            $table->enum('time_format', ['12', '24'])->default('24')->after('timezone');
            $table->json('unit_measurements')->nullable()->after('time_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['timezone', 'time_format', 'unit_measurements']);
        });
    }
};

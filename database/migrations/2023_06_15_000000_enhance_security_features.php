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
        Schema::table('users', function (Blueprint $table) {
            // Increase password column size for future hashing algorithms
            $table->string('password', 100)->change();

            // Add two-factor authentication fields
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_secret', 100)->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert password column size
            $table->string('password', 60)->change();

            // Remove two-factor authentication fields
            $table->dropColumn([
                'two_factor_enabled',
                'two_factor_secret',
                'two_factor_recovery_codes'
            ]);
        });
    }
};

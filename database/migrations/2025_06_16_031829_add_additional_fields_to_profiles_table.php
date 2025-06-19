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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('status', 50)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('profession', 100)->nullable();
            $table->text('bio')->nullable();
            $table->json('interests')->nullable();
            $table->string('country_code', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'occupation',
                'profession',
                'bio',
                'interests',
                'country_code'
            ]);
        });
    }
};

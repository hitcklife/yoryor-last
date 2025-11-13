<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration adds foreign key constraints that couldn't be added earlier due to table dependencies
     */
    public function up(): void
    {
        // Add foreign key for users.assigned_matchmaker_id after matchmakers table is created
        if (Schema::hasTable('matchmakers')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('assigned_matchmaker_id')
                      ->references('id')
                      ->on('matchmakers')
                      ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_matchmaker_id']);
        });
    }
};
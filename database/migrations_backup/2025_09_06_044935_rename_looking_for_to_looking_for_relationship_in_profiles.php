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
            // Check if the column exists before renaming
            if (Schema::hasColumn('profiles', 'looking_for') && !Schema::hasColumn('profiles', 'looking_for_relationship')) {
                $table->renameColumn('looking_for', 'looking_for_relationship');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'looking_for_relationship') && !Schema::hasColumn('profiles', 'looking_for')) {
                $table->renameColumn('looking_for_relationship', 'looking_for');
            }
        });
    }
};
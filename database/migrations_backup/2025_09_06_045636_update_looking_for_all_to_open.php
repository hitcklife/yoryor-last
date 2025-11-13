<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the old check constraint if it exists
        DB::statement('ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_looking_for_check');
        DB::statement('ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_looking_for_relationship_check');
        
        // Update all 'all' values to 'open' in the looking_for_relationship column
        DB::table('profiles')
            ->where('looking_for_relationship', 'all')
            ->update(['looking_for_relationship' => 'open']);
            
        // Add new check constraint with correct values
        DB::statement("ALTER TABLE profiles ADD CONSTRAINT profiles_looking_for_relationship_check CHECK (looking_for_relationship IN ('casual', 'serious', 'friendship', 'open'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new constraint
        DB::statement('ALTER TABLE profiles DROP CONSTRAINT IF EXISTS profiles_looking_for_relationship_check');
        
        // Revert 'open' back to 'all'
        DB::table('profiles')
            ->where('looking_for_relationship', 'open')
            ->update(['looking_for_relationship' => 'all']);
            
        // Restore original constraint
        DB::statement("ALTER TABLE profiles ADD CONSTRAINT profiles_looking_for_check CHECK (looking_for_relationship IN ('casual', 'serious', 'friendship', 'all'))");
    }
};
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PopulateUserUuidsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users without profile_uuid
        $users = User::whereNull('profile_uuid')->get();
        
        foreach ($users as $user) {
            $user->profile_uuid = Str::uuid();
            $user->save();
        }
        
        $this->command->info("Generated profile UUIDs for {$users->count()} users.");
    }
}

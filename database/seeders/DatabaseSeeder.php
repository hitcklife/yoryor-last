<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the CountrySeeder first since UserSeeder depends on countries
        $this->call(CountrySeeder::class);

        // Create 500 dummy users with profiles and photos
        $this->call(UserSeeder::class);

        // Create a test user for development
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'registration_completed' => true,
        ]);
    }
}

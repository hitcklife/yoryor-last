<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        // Run the CountrySeeder first since UserSeeder depends on countries
        $this->call(CountrySeeder::class);

        // Create 500 dummy users with profiles, photos, preferences, likes, matches, chats, and messages
        $this->call(UserSeeder::class);

        // Create stories for users
        $this->call(UserStorySeeder::class);

        $this->command->info('Database seeding completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- 500 Users with complete profiles');
        $this->command->info('- User photos and preferences');
        $this->command->info('- Likes and matches between users');
        $this->command->info('- Private chats between matched users');
        $this->command->info('- Realistic conversation messages');
        $this->command->info('- Instagram-like stories for users');
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserStory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserStorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users with completed registration
        $users = User::where('registration_completed', true)
                     ->whereNull('disabled_at')
                     ->get();

        if ($users->isEmpty()) {
            $this->command->info('No active users found. Skipping story seeding.');
            return;
        }

        $this->command->info('Creating stories for ' . $users->count() . ' users...');

        // Sample image URLs for stories (placeholder images)
        $imageUrls = [
            'https://picsum.photos/800/1200',
            'https://picsum.photos/800/1200?random=1',
            'https://picsum.photos/800/1200?random=2',
            'https://picsum.photos/800/1200?random=3',
            'https://picsum.photos/800/1200?random=4',
            'https://picsum.photos/800/1200?random=5',
            'https://picsum.photos/800/1200?random=6',
            'https://picsum.photos/800/1200?random=7',
            'https://picsum.photos/800/1200?random=8',
            'https://picsum.photos/800/1200?random=9',
        ];

        // Sample captions for stories
        $captions = [
            'Having a great day!',
            'Out with friends',
            'Beautiful sunset today',
            'Just chilling',
            'At the gym',
            'Coffee time',
            'Working hard',
            'Weekend vibes',
            'New haircut',
            'Feeling good',
            null, // Some stories without captions
        ];

        // Create stories for each user
        foreach ($users as $user) {
            // Create 1-3 stories per user
            $storyCount = rand(1, 3);

            for ($i = 0; $i < $storyCount; $i++) {
                // Randomly decide if the story is active or expired
                $isActive = (rand(0, 10) > 3); // 70% chance of being active

                // Set expiration time
                $expiresAt = $isActive
                    ? now()->addHours(rand(1, 23)) // Active stories expire in 1-23 hours
                    : now()->subHours(rand(1, 48)); // Expired stories expired 1-48 hours ago

                // Create the story
                try {
                    UserStory::create([
                        'user_id' => $user->id,
                        'media_url' => $imageUrls[array_rand($imageUrls)],
                        'thumbnail_url' => $imageUrls[array_rand($imageUrls)],
                        'type' => 'image',
                        'caption' => $captions[array_rand($captions)],
                        'expires_at' => $expiresAt,
                        'status' => $isActive ? 'active' : 'expired',
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error creating story for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        }

        $this->command->info('Stories created successfully!');
    }
}

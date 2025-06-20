<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\UserPhoto;
use App\Models\Preference;
use App\Models\Country;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MatchModel;
use App\Models\Like;
use App\Models\Dislike;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 500 users with complete profiles...');

        // Get available countries
        $countries = Country::all();

        if ($countries->isEmpty()) {
            $this->command->error('No countries found. Please run CountrySeeder first.');
            return;
        }

        $genders = ['male', 'female', 'non-binary', 'other'];
        $lookingFor = ['casual', 'serious', 'friendship', 'all'];
        $languages = ['English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Russian', 'Chinese', 'Japanese', 'Arabic'];
        $interests = [
            'Travel', 'Photography', 'Music', 'Movies', 'Reading', 'Sports', 'Cooking', 'Art',
            'Dancing', 'Hiking', 'Gaming', 'Fitness', 'Yoga', 'Swimming', 'Running', 'Cycling',
            'Food', 'Wine', 'Coffee', 'Technology', 'Science', 'Nature', 'Animals', 'Fashion',
            'Writing', 'Meditation', 'Painting', 'Guitar', 'Piano', 'Singing', 'Theater'
        ];

        $cities = [
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio',
            'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'Fort Worth', 'Columbus',
            'Charlotte', 'San Francisco', 'Indianapolis', 'Seattle', 'Denver', 'Washington',
            'Boston', 'El Paso', 'Nashville', 'Detroit', 'Oklahoma City', 'Portland', 'Las Vegas',
            'Memphis', 'Louisville', 'Baltimore', 'Milwaukee', 'Albuquerque', 'Tucson', 'Fresno',
            'Sacramento', 'Kansas City', 'Mesa', 'Virginia Beach', 'Atlanta', 'Colorado Springs'
        ];

        $professions = [
            'Software Engineer', 'Doctor', 'Teacher', 'Nurse', 'Marketing Manager', 'Sales Representative',
            'Graphic Designer', 'Accountant', 'Lawyer', 'Chef', 'Photographer', 'Writer', 'Artist',
            'Consultant', 'Entrepreneur', 'Student', 'Engineer', 'Architect', 'Therapist', 'Musician'
        ];

        // Sample photo URLs
        $malePhotoUrls = [
            'https://randomuser.me/api/portraits/men/1.jpg',
            'https://randomuser.me/api/portraits/men/2.jpg',
            'https://randomuser.me/api/portraits/men/3.jpg',
            'https://randomuser.me/api/portraits/men/4.jpg',
            'https://randomuser.me/api/portraits/men/5.jpg',
        ];

        $femalePhotoUrls = [
            'https://randomuser.me/api/portraits/women/1.jpg',
            'https://randomuser.me/api/portraits/women/2.jpg',
            'https://randomuser.me/api/portraits/women/3.jpg',
            'https://randomuser.me/api/portraits/women/4.jpg',
            'https://randomuser.me/api/portraits/women/5.jpg',
        ];

        $progressBar = $this->command->getOutput()->createProgressBar(500);
        $progressBar->start();

        for ($i = 0; $i < 500; $i++) {
            $gender = fake()->randomElement($genders);
            $country = $countries->random();
            $birthDate = Carbon::now()->subYears(rand(18, 65))->subDays(rand(0, 365));

            // Create user
            $user = User::create([
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->unique()->phoneNumber(),
                'email_verified_at' => rand(0, 1) ? now() : null,
                'phone_verified_at' => rand(0, 1) ? now() : null,
                'password' => Hash::make('password'),
                'registration_completed' => true,
                'is_private' => rand(0, 10) > 8, // 20% private profiles
                'last_active_at' => Carbon::now()->subMinutes(rand(0, 10080)), // Random activity within last week
                'created_at' => Carbon::now()->subDays(rand(0, 365)),
            ]);

            // Create profile
            $firstName = $gender === 'female' ? fake()->firstNameFemale() : fake()->firstNameMale();
            $lastName = fake()->lastName();

            $profile = Profile::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $birthDate,
                'city' => fake()->randomElement($cities),
                'state' => fake()->state(),
                'country_id' => $country->id,
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'bio' => fake()->paragraph(rand(2, 4)),
                'interests' => fake()->randomElements($interests, rand(3, 8)),
                'looking_for' => fake()->randomElement($lookingFor),
                'occupation' => fake()->randomElement($professions),
                'profile_views' => rand(0, 1000),
                'profile_completed_at' => now(),
            ]);

            // Create user preferences
            Preference::create([
                'user_id' => $user->id,
                'search_radius' => rand(5, 100),
                'country' => $country->code ?? 'US',
                'preferred_genders' => $gender === 'male' ? 'female' : ($gender === 'female' ? 'male' : fake()->randomElement($genders)),
                'min_age' => $minAge = rand(18, 35),
                'max_age' => rand($minAge + 5, 65),
                'languages_spoken' => fake()->randomElements($languages, rand(1, 3)),
                'hobbies_interests' => fake()->randomElements($interests, rand(2, 6)),
            ]);

            // Create user photos
            $photoUrls = $gender === 'female' ? $femalePhotoUrls : $malePhotoUrls;
            $numPhotos = rand(1, 5);

            for ($j = 0; $j < $numPhotos; $j++) {
                UserPhoto::create([
                    'user_id' => $user->id,
                    'original_url' => fake()->randomElement($photoUrls),
                    'thumbnail_url' => fake()->randomElement($photoUrls),
                    'medium_url' => fake()->randomElement($photoUrls),
                    'is_profile_photo' => $j === 0, // First photo is profile photo
                    'order' => $j,
                    'is_private' => rand(0, 10) > 7, // 30% private photos
                    'is_verified' => rand(0, 1),
                    'status' => fake()->randomElement(['approved', 'pending', 'rejected']),
                    'uploaded_at' => Carbon::now()->subDays(rand(0, 30)),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Successfully created 500 users with profiles, preferences, and photos!');

        // Create interactions
        $this->createLikesAndMatches();
        $this->createChatsAndMessages();
    }

    /**
     * Create some likes and matches between users
     */
    private function createLikesAndMatches(): void
    {
        $this->command->info('Creating likes and matches...');

        $users = User::all();
        $progressBar = $this->command->getOutput()->createProgressBar(1000);
        $progressBar->start();

        // Create 1000 random likes
        for ($i = 0; $i < 1000; $i++) {
            $user = $users->random();
            $likedUser = $users->where('id', '!=', $user->id)->random();

            // Check if like already exists
            if (!Like::where('user_id', $user->id)->where('liked_user_id', $likedUser->id)->exists()) {
                $like = Like::create([
                    'user_id' => $user->id,
                    'liked_user_id' => $likedUser->id,
                ]);

                // Check for mutual like and create matches
                $mutualLike = Like::where('user_id', $likedUser->id)
                                 ->where('liked_user_id', $user->id)
                                 ->exists();

                if ($mutualLike) {
                    // Create matches for both users
                    MatchModel::firstOrCreate([
                        'user_id' => $user->id,
                        'matched_user_id' => $likedUser->id
                    ]);

                    MatchModel::firstOrCreate([
                        'user_id' => $likedUser->id,
                        'matched_user_id' => $user->id
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();

        // Create some dislikes
        $this->command->info('Creating dislikes...');
        $dislikeProgressBar = $this->command->getOutput()->createProgressBar(500);
        $dislikeProgressBar->start();

        for ($i = 0; $i < 500; $i++) {
            $user = $users->random();
            $dislikedUser = $users->where('id', '!=', $user->id)->random();

            // Check if dislike already exists and user hasn't liked this person
            if (!Dislike::where('user_id', $user->id)->where('disliked_user_id', $dislikedUser->id)->exists() &&
                !Like::where('user_id', $user->id)->where('liked_user_id', $dislikedUser->id)->exists()) {
                Dislike::create([
                    'user_id' => $user->id,
                    'disliked_user_id' => $dislikedUser->id,
                ]);
            }

            $dislikeProgressBar->advance();
        }

        $dislikeProgressBar->finish();
        $this->command->newLine();
        $this->command->info('Likes, matches, and dislikes created successfully!');
    }

    /**
     * Create chats and messages between matched users
     */
    private function createChatsAndMessages(): void
    {
        $this->command->info('Creating chats and messages...');

        // Get all matches to create chats
        $matches = MatchModel::with(['user', 'matchedUser'])->get();
        $createdChats = collect();

        // Group matches by user pairs to avoid duplicate chats
        $userPairs = collect();

        foreach ($matches as $match) {
            $userId1 = min($match->user_id, $match->matched_user_id);
            $userId2 = max($match->user_id, $match->matched_user_id);
            $pairKey = "{$userId1}-{$userId2}";

            if (!$userPairs->has($pairKey)) {
                $userPairs->put($pairKey, [$userId1, $userId2]);
            }
        }

        $this->command->info("Found {$userPairs->count()} unique matched pairs. Creating chats...");

        $chatProgressBar = $this->command->getOutput()->createProgressBar($userPairs->count());
        $chatProgressBar->start();

        foreach ($userPairs as $pair) {
            [$userId1, $userId2] = $pair;

            // Create chat
            $chat = Chat::create([
                'type' => 'private',
                'is_active' => true,
                'last_activity_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);

            // Add users to chat
            $chat->users()->attach($userId1, [
                'joined_at' => $chat->created_at,
                'role' => 'member',
                'is_muted' => false,
            ]);

            $chat->users()->attach($userId2, [
                'joined_at' => $chat->created_at,
                'role' => 'member',
                'is_muted' => false,
            ]);

            $createdChats->push($chat);
            $chatProgressBar->advance();
        }

        $chatProgressBar->finish();
        $this->command->newLine();

        // Now create messages for these chats
        $this->command->info("Creating messages for {$createdChats->count()} chats...");

        $messageProgressBar = $this->command->getOutput()->createProgressBar($createdChats->count() * 10);
        $messageProgressBar->start();

        // Dating app conversation starters and responses
        $conversationStarters = [
            "Hey! How's your day going?",
            "Hi there! I loved your photos, especially the one with the sunset!",
            "Hello! I see we both love hiking. Do you have a favorite trail?",
            "Hey! Your profile caught my eye. What's your favorite way to spend weekends?",
            "Hi! I noticed we both enjoy traveling. What's the best place you've visited?",
            "Hello! Coffee or tea person? ☕",
            "Hey! What's the most interesting thing that happened to you this week?",
            "Hi there! I see you're into photography. Do you have any tips for a beginner?",
        ];

        $responses = [
            "Hey! It's going great, thanks for asking! How about yours?",
            "Thank you! That was from my trip to Santorini last summer 😊",
            "Oh awesome! I love the trails in Yosemite. Have you been there?",
            "Usually I like to explore new restaurants or go to farmers markets. You?",
            "Definitely Japan! The culture and food were incredible. Where's next on your list?",
            "Definitely a coffee person! ☕ Can't function without my morning cup",
            "I actually tried rock climbing for the first time! It was terrifying but fun 😅",
            "Sure! Start with natural lighting and don't be afraid to take lots of shots",
            "That sounds amazing! I've always wanted to try that",
            "Really? That's so cool! Tell me more about it",
            "Haha, I can relate to that! 😂",
            "That's awesome! I'm definitely going to check that out",
            "No way! What a coincidence, I was just thinking about that",
            "That sounds like so much fun! I'd love to try it sometime",
            "Thanks for the recommendation! I'll add it to my list",
        ];

        $followUpQuestions = [
            "What do you like to do for fun?",
            "Any plans for the weekend?",
            "What's your favorite type of cuisine?",
            "Do you have any pets?",
            "What's your dream vacation destination?",
            "Are you more of a morning or night person?",
            "What's the last book you read?",
            "Do you prefer movies or TV shows?",
            "What's your favorite season and why?",
            "Any hidden talents I should know about? 😄",
        ];

        foreach ($createdChats as $chat) {
            $users = $chat->users->shuffle();
            $user1 = $users->first();
            $user2 = $users->last();

            // 70% chance this chat will have messages
            if (rand(1, 10) <= 7) {
                $messageCount = rand(3, 25); // Random conversation length
                $currentSender = $users->random();
                $lastMessageTime = Carbon::now()->subDays(rand(0, 15));

                for ($j = 0; $j < $messageCount; $j++) {
                    $content = '';

                    if ($j === 0) {
                        // First message is always a conversation starter
                        $content = fake()->randomElement($conversationStarters);
                    } elseif ($j % 3 === 0 && $j > 1) {
                        // Every third message ask a follow-up question
                        $content = fake()->randomElement($followUpQuestions);
                    } else {
                        // Regular responses
                        $content = fake()->randomElement($responses);
                    }

                    $messageTime = $lastMessageTime->copy()->addMinutes(rand(5, 120));

                    $message = Message::create([
                        'chat_id' => $chat->id,
                        'sender_id' => $currentSender->id,
                        'content' => $content,
                        'message_type' => 'text',
                        'status' => 'sent',
                        'sent_at' => $messageTime,
                        'created_at' => $messageTime,
                        'updated_at' => $messageTime,
                    ]);

                    // Update chat's last activity
                    $chat->update(['last_activity_at' => $messageTime]);

                    // Switch sender for next message (realistic conversation flow)
                    $currentSender = $currentSender->id === $user1->id ? $user2 : $user1;
                    $lastMessageTime = $messageTime;

                    // Some messages have media (5% chance)
                    if (rand(1, 20) === 1) {
                        $message->update([
                            'message_type' => fake()->randomElement(['image', 'video']),
                            'media_url' => 'https://picsum.photos/400/300',
                            'thumbnail_url' => 'https://picsum.photos/100/100',
                            'media_data' => [
                                'width' => 400,
                                'height' => 300,
                                'size' => rand(100000, 2000000)
                            ]
                        ]);
                    }

                    $messageProgressBar->advance();
                }

                // Update last_read_at for users (some messages are unread)
                foreach ($chat->users as $user) {
                    $readUntil = $lastMessageTime->copy()->subMinutes(rand(0, 1440)); // Read until random time

                    DB::table('chat_users')
                        ->where('chat_id', $chat->id)
                        ->where('user_id', $user->id)
                        ->update(['last_read_at' => $readUntil]);
                }
            } else {
                // Skip messages for this chat, just advance progress bar
                for ($k = 0; $k < 10; $k++) {
                    $messageProgressBar->advance();
                }
            }
        }

        $messageProgressBar->finish();
        $this->command->newLine();

        $totalMessages = Message::count();
        $this->command->info("Successfully created {$createdChats->count()} chats with {$totalMessages} messages!");
    }
}

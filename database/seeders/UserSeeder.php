<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create();

        // Get all country IDs for random assignment
        $countryIds = Country::pluck('id')->toArray();

        // Create 500 users with profiles and photos
        for ($i = 0; $i < 500; $i++) {
            // Create user
            $user = User::create([
                'email' => $faker->unique()->safeEmail(),
                'phone' => $faker->unique()->e164PhoneNumber(),
                'password' => Hash::make('password'),
                'registration_completed' => true,
            ]);

            // Create profile for user
            $countryId = $faker->randomElement($countryIds);
            $country = Country::find($countryId);

            $gender = $faker->randomElement(['male', 'female', 'other']);

            Profile::create([
                'user_id' => $user->id,
                'first_name' => $faker->firstName($gender === 'male' ? 'male' : ($gender === 'female' ? 'female' : null)),
                'last_name' => $faker->lastName(),
                'gender' => $gender,
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
                'city' => $faker->city(),
                'country_id' => $countryId,
                'country_code' => $country->code,
                'latitude' => $faker->latitude(),
                'longitude' => $faker->longitude(),
                'occupation' => $faker->jobTitle(),
                'bio' => $faker->paragraphs(2, true),
                'interests' => json_encode($faker->randomElements([
                    'Travel', 'Music', 'Movies', 'Books', 'Sports', 'Cooking',
                    'Photography', 'Art', 'Technology', 'Fashion', 'Fitness',
                    'Gaming', 'Dancing', 'Hiking', 'Swimming', 'Yoga', 'Meditation'
                ], $faker->numberBetween(3, 8)))
            ]);

            // Create profile photos for user
            $numPhotos = $faker->numberBetween(1, 5);

            // Create one profile photo (is_profile_photo = true)
            $profilePhotoUrl = $faker->randomElement([
                'https://randomuser.me/api/portraits/' . ($gender === 'male' ? 'men' : 'women') . '/' . $faker->numberBetween(1, 99) . '.jpg',
                'https://i.pravatar.cc/500?img=' . $faker->numberBetween(1, 70),
                'https://picsum.photos/id/' . $faker->numberBetween(1, 1000) . '/500/500'
            ]);

            $profilePhoto = UserPhoto::create([
                'user_id' => $user->id,
                'photo_url' => $profilePhotoUrl,
                'is_profile_photo' => true,
                'order' => 1,
                'is_private' => false
            ]);

            // If we want more than one photo, create additional photos
            if ($numPhotos > 1) {
                // Create one non-profile photo (is_profile_photo = false)
                $nonProfilePhotoUrl = $faker->randomElement([
                    'https://randomuser.me/api/portraits/' . ($gender === 'male' ? 'men' : 'women') . '/' . $faker->numberBetween(1, 99) . '.jpg',
                    'https://i.pravatar.cc/500?img=' . $faker->numberBetween(1, 70),
                    'https://picsum.photos/id/' . $faker->numberBetween(1, 1000) . '/500/500'
                ]);

                UserPhoto::create([
                    'user_id' => $user->id,
                    'photo_url' => $nonProfilePhotoUrl,
                    'is_profile_photo' => false,
                    'order' => 2,
                    'is_private' => $faker->boolean(10) // 10% chance of being private
                ]);
            }

            // Update user's profile_photo_path with the first photo
            $profilePhoto = $user->photos()->where('is_profile_photo', true)->first();
            if ($profilePhoto) {
                $user->update([
                    'profile_photo_path' => $profilePhoto->photo_url
                ]);
            }
        }
    }
}

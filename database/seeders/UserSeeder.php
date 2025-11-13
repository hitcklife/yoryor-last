<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\UserPhoto;
use App\Models\UserPreference;
use App\Models\UserCulturalProfile;
use App\Models\UserFamilyPreference;
use App\Models\UserLocationPreference;
use App\Models\UserCareerProfile;
use App\Models\UserPhysicalProfile;
use App\Models\UserStory;
use App\Models\DeviceToken;
use App\Models\UserEmergencyContact;
use App\Models\Matchmaker;
use App\Models\VerificationRequest;
use App\Models\UserVerifiedBadge;
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
        $this->command->info('Creating 500 users with complete enhanced profiles...');

        // Get available countries
        $countries = Country::all();

        if ($countries->isEmpty()) {
            $this->command->error('No countries found. Please run CountrySeeder first.');
            return;
        }

        // Define all the data arrays
        $genders = ['male', 'female', 'non-binary', 'other'];
        $lookingFor = ['casual', 'serious', 'friendship', 'open'];
        $marriageStatuses = ['single', 'divorced', 'widowed', 'separated', 'never_married'];
        $languages = ['English', 'Uzbek', 'Russian', 'Spanish', 'French', 'German', 'Italian', 'Portuguese', 'Chinese', 'Japanese', 'Arabic', 'Turkish', 'Persian'];
        
        $interests = [
            'Travel', 'Photography', 'Music', 'Movies', 'Reading', 'Sports', 'Cooking', 'Art',
            'Dancing', 'Hiking', 'Gaming', 'Fitness', 'Yoga', 'Swimming', 'Running', 'Cycling',
            'Food', 'Wine', 'Coffee', 'Technology', 'Science', 'Nature', 'Animals', 'Fashion',
            'Writing', 'Meditation', 'Painting', 'Guitar', 'Piano', 'Singing', 'Theater',
            'Uzbek Cuisine', 'Traditional Music', 'Calligraphy', 'Pottery', 'Embroidery'
        ];

        $cities = [
            'Tashkent', 'Samarkand', 'Bukhara', 'Andijan', 'Namangan', 'Fergana', 'Nukus',
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio',
            'San Diego', 'Dallas', 'San Jose', 'Austin', 'Jacksonville', 'Fort Worth', 'Columbus',
            'Charlotte', 'San Francisco', 'Indianapolis', 'Seattle', 'Denver', 'Washington',
            'Boston', 'El Paso', 'Nashville', 'Detroit', 'Oklahoma City', 'Portland', 'Las Vegas',
            'Memphis', 'Louisville', 'Baltimore', 'Milwaukee', 'Albuquerque', 'Tucson', 'Fresno',
            'Sacramento', 'Kansas City', 'Mesa', 'Virginia Beach', 'Atlanta', 'Colorado Springs',
            'London', 'Paris', 'Berlin', 'Madrid', 'Rome', 'Amsterdam', 'Vienna', 'Prague',
            'Moscow', 'Istanbul', 'Dubai', 'Doha', 'Kuala Lumpur', 'Singapore', 'Tokyo', 'Seoul'
        ];

        $professions = [
            'Software Engineer', 'Doctor', 'Teacher', 'Nurse', 'Marketing Manager', 'Sales Representative',
            'Graphic Designer', 'Accountant', 'Lawyer', 'Chef', 'Photographer', 'Writer', 'Artist',
            'Consultant', 'Entrepreneur', 'Student', 'Engineer', 'Architect', 'Therapist', 'Musician',
            'Business Owner', 'Financial Advisor', 'Real Estate Agent', 'Translator', 'Tour Guide',
            'Fashion Designer', 'Interior Designer', 'Event Planner', 'Journalist', 'Researcher'
        ];

        // Cultural data
        $religions = ['muslim', 'christian', 'secular', 'other', 'prefer_not_to_say'];
        $religiousnessLevels = ['very_religious', 'moderately_religious', 'not_religious', 'prefer_not_to_say'];
        $lifestyleTypes = ['traditional', 'modern', 'mix_of_both'];
        $genderRoleViews = ['traditional', 'modern', 'flexible'];
        $ethnicities = ['uzbek', 'russian', 'tajik', 'kazakh', 'tatar', 'kyrgyz', 'korean', 'other'];
        $uzbekRegions = ['tashkent', 'samarkand', 'bukhara', 'andijan', 'namangan', 'fergana', 'khorezm', 'karakalpakstan', 'kashkadarya', 'surkhandarya', 'navoiy', 'jizzakh', 'sirdaryo'];
        $cuisineKnowledge = ['expert', 'good', 'basic', 'learning'];
        $culturalParticipation = ['very_active', 'active', 'sometimes', 'rarely'];

        // Family preferences data
        $childrenPreferences = ['want_children', 'have_and_want_more', 'have_and_dont_want_more', 'dont_want_children', 'undecided'];
        $marriageTimelines = ['within_6_months', 'within_1_year', 'within_2_years', 'within_5_years', 'no_timeline'];
        $familyImportance = ['extremely_important', 'very_important', 'moderately_important', 'somewhat_important', 'not_important'];
        $homemakerPreferences = ['prefer_traditional_roles', 'both_work_equally', 'flexible_arrangement', 'undecided'];

        // Career data
        $educationLevels = ['high_school', 'associate', 'bachelor', 'master', 'doctorate', 'professional', 'trade_school', 'other'];
        $incomeRanges = ['under_25k', '25k_50k', '50k_75k', '75k_100k', '100k_150k', '150k_plus', 'prefer_not_to_say'];

        // Physical profile data
        $bodyTypes = ['slim', 'athletic', 'average', 'curvy', 'plus_size'];
        $hairColors = ['black', 'brown', 'blonde', 'red', 'gray', 'white', 'other'];
        $eyeColors = ['brown', 'blue', 'green', 'hazel', 'gray', 'amber', 'other'];
        $fitnessLevels = ['never', 'rarely', '1_2_week', '3_4_week', 'daily'];
        $smokingStatuses = ['never', 'socially', 'regularly', 'trying_to_quit'];
        $drinkingStatuses = ['never', 'socially', 'occasionally', 'regularly'];

        // Location preferences data
        $immigrationStatuses = ['citizen', 'permanent_resident', 'work_visa', 'student_visa', 'tourist_visa', 'asylum_refugee', 'other'];
        $returnPlans = ['definitely_yes', 'probably_yes', 'maybe', 'probably_no', 'definitely_no', 'undecided'];
        $visitFrequencies = ['never', 'rarely', 'annually', 'twice_yearly', 'quarterly', 'monthly', 'frequently'];

        // Sample photo URLs
        $malePhotoUrls = [
            'https://randomuser.me/api/portraits/men/1.jpg',
            'https://randomuser.me/api/portraits/men/2.jpg',
            'https://randomuser.me/api/portraits/men/3.jpg',
            'https://randomuser.me/api/portraits/men/4.jpg',
            'https://randomuser.me/api/portraits/men/5.jpg',
            'https://randomuser.me/api/portraits/men/6.jpg',
            'https://randomuser.me/api/portraits/men/7.jpg',
            'https://randomuser.me/api/portraits/men/8.jpg',
            'https://randomuser.me/api/portraits/men/9.jpg',
            'https://randomuser.me/api/portraits/men/10.jpg',
        ];

        $femalePhotoUrls = [
            'https://randomuser.me/api/portraits/women/1.jpg',
            'https://randomuser.me/api/portraits/women/2.jpg',
            'https://randomuser.me/api/portraits/women/3.jpg',
            'https://randomuser.me/api/portraits/women/4.jpg',
            'https://randomuser.me/api/portraits/women/5.jpg',
            'https://randomuser.me/api/portraits/women/6.jpg',
            'https://randomuser.me/api/portraits/women/7.jpg',
            'https://randomuser.me/api/portraits/women/8.jpg',
            'https://randomuser.me/api/portraits/women/9.jpg',
            'https://randomuser.me/api/portraits/women/10.jpg',
        ];

        // Story content data
        $storyCaptions = [
            "Beautiful sunset today! 🌅",
            "Coffee and good vibes ☕",
            "Exploring new places ✨",
            "Family time is the best time 👨‍👩‍👧‍👦",
            "Cooking up something delicious! 👨‍🍳",
            "Morning workout complete! 💪",
            "Reading a great book 📚",
            "Art gallery visit today 🎨",
            "Music makes everything better 🎵",
            "Grateful for this beautiful day 🙏",
            "New adventure awaits! 🗺️",
            "Learning something new every day 📖",
            "Nature is so peaceful 🌿",
            "Good food, good company 🍽️",
            "Making memories that last forever 📸"
        ];

        $progressBar = $this->command->getOutput()->createProgressBar(500);
        $progressBar->start();

        for ($i = 0; $i < 500; $i++) {
            $gender = fake()->randomElement($genders);
            $country = $countries->random();
            $birthDate = Carbon::now()->subYears(rand(18, 65))->subDays(rand(0, 365));
            $age = $birthDate->age;

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

            $profile =             Profile::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $birthDate,
                'age' => $age,
                'city' => fake()->randomElement($cities),
                'state' => fake()->state(),
                'country_id' => $country->id,
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'bio' => fake()->paragraph(rand(2, 4)),
                'interests' => fake()->randomElements($interests, rand(3, 8)),
                'looking_for_relationship' => fake()->randomElement($lookingFor),
                'status' => fake()->randomElement($marriageStatuses),
                'profession' => fake()->randomElement($professions),
                'occupation' => fake()->randomElement($professions),
                'profile_views' => rand(0, 1000),
                'profile_completed_at' => now(),
            ]);

            // Create user preferences
            UserPreference::create([
                'user_id' => $user->id,
                'search_radius' => rand(5, 100),
                'country' => $country->code ?? 'US',
                'preferred_genders' => $gender === 'male' ? ['female'] : ($gender === 'female' ? ['male'] : fake()->randomElements($genders, rand(1, 2))),
                'min_age' => $minAge = rand(18, 35),
                'max_age' => rand($minAge + 5, 65),
                'languages_spoken' => fake()->randomElements($languages, rand(1, 3)),
                'hobbies_interests' => fake()->randomElements($interests, rand(2, 6)),
                'deal_breakers' => fake()->randomElements(['smoking', 'drinking', 'not_religious', 'too_young', 'too_old'], rand(0, 3)),
                'must_haves' => fake()->randomElements(['education', 'career', 'family_oriented', 'religious', 'active_lifestyle'], rand(0, 3)),
                'distance_unit' => fake()->randomElement(['km', 'miles']),
                'show_me_globally' => rand(0, 10) > 7, // 30% show globally
                'notification_preferences' => [
                    'new_matches' => rand(0, 1),
                    'messages' => rand(0, 1),
                    'likes' => rand(0, 1),
                    'profile_views' => rand(0, 1),
                ],
            ]);

            // Create cultural profile
            UserCulturalProfile::create([
                'user_id' => $user->id,
                'native_languages' => fake()->randomElements($languages, rand(1, 2)),
                'spoken_languages' => fake()->randomElements($languages, rand(1, 4)),
                'preferred_communication_language' => fake()->randomElement($languages),
                'religion' => fake()->randomElement($religions),
                'religiousness_level' => fake()->randomElement($religiousnessLevels),
                'ethnicity' => fake()->randomElement($ethnicities),
                'uzbek_region' => fake()->randomElement($uzbekRegions),
                'lifestyle_type' => fake()->randomElement($lifestyleTypes),
                'gender_role_views' => fake()->randomElement($genderRoleViews),
                'traditional_clothing_comfort' => fake()->randomElement(['very_comfortable', 'comfortable', 'neutral', 'uncomfortable']),
                'uzbek_cuisine_knowledge' => fake()->randomElement($cuisineKnowledge),
                'cultural_events_participation' => fake()->randomElement($culturalParticipation),
                'halal_lifestyle' => rand(0, 1),
                'quran_reading' => fake()->randomElement(['daily', 'weekly', 'monthly', 'rarely', 'never']),
            ]);

            // Create family preferences
            UserFamilyPreference::create([
                'user_id' => $user->id,
                'marriage_intention' => fake()->randomElement(['seeking_marriage', 'open_to_marriage', 'not_ready_yet', 'undecided']),
                'children_preference' => fake()->randomElement($childrenPreferences),
                'current_children' => rand(0, 3),
                'number_of_children_wanted' => rand(0, 5),
                'family_values' => json_encode(['family_tradition', 'education', 'respect', 'love']),
                'living_situation' => fake()->randomElement(['alone', 'with_family', 'with_roommates', 'with_partner', 'other']),
                'family_involvement' => fake()->sentence(),
                'living_with_family' => rand(0, 1),
                'family_approval_important' => rand(0, 1),
                'marriage_timeline' => fake()->randomElement($marriageTimelines),
                'family_importance' => fake()->randomElement($familyImportance),
                'previous_marriages' => rand(0, 2),
                'homemaker_preference' => fake()->randomElement($homemakerPreferences),
            ]);

            // Create location preferences
            UserLocationPreference::create([
                'user_id' => $user->id,
                'immigration_status' => fake()->randomElement($immigrationStatuses),
                'years_in_current_country' => rand(0, 20),
                'plans_to_return_uzbekistan' => fake()->randomElement($returnPlans),
                'uzbekistan_visit_frequency' => fake()->randomElement($visitFrequencies),
                'willing_to_relocate' => fake()->randomElement(['no', 'within_city', 'within_state', 'within_country', 'internationally', 'for_right_person']),
                'relocation_countries' => fake()->randomElements(['USA', 'Canada', 'UK', 'Germany', 'Australia', 'UAE', 'Turkey'], rand(0, 3)),
                'preferred_locations' => fake()->randomElements(['New York', 'Los Angeles', 'London', 'Toronto', 'Sydney'], rand(0, 2)),
                'live_with_family' => (bool) rand(0, 1),
                'future_location_plans' => fake()->sentence(),
            ]);

            // Create career profile
            UserCareerProfile::create([
                'user_id' => $user->id,
                'education_level' => fake()->randomElement($educationLevels),
                'field_of_study' => fake()->randomElement(['Computer Science', 'Business', 'Medicine', 'Engineering', 'Arts', 'Education', 'Law', 'Other']),
                'university_name' => fake()->randomElement(['Tashkent State University', 'Samarkand State University', 'Harvard University', 'Stanford University', 'MIT', 'Oxford University', 'Cambridge University']),
                'work_status' => fake()->randomElement(['full_time', 'part_time', 'self_employed', 'freelance', 'student', 'unemployed', 'retired']),
                'occupation' => fake()->jobTitle(),
                'employer' => fake()->company(),
                'job_title' => fake()->jobTitle(),
                'career_goals' => json_encode(['professional_growth', 'financial_stability', 'work_life_balance']),
                'income_range' => fake()->randomElement($incomeRanges),
                'owns_property' => rand(0, 1),
                'financial_goals' => fake()->paragraph(1),
                'profession' => fake()->jobTitle(),
                'company' => fake()->company(),
                'income' => fake()->randomElement($incomeRanges),
            ]);

            // Create physical profile
            UserPhysicalProfile::create([
                'user_id' => $user->id,
                'height' => rand(150, 200), // cm
                'weight' => fake()->randomFloat(1, 45, 120), // kg
                'smoking_habit' => fake()->randomElement($smokingStatuses),
                'drinking_habit' => fake()->randomElement($drinkingStatuses),
                'exercise_frequency' => fake()->randomElement($fitnessLevels),
                'diet_preference' => fake()->randomElement(['everything', 'vegetarian', 'vegan', 'halal', 'kosher', 'pescatarian', 'keto']),
                'pet_preference' => fake()->randomElement(['love_pets', 'have_pets', 'allergic', 'dont_like', 'no_preference']),
                'hobbies' => json_encode(['reading', 'cooking', 'travel', 'sports', 'music', 'art']),
                'sleep_schedule' => fake()->randomElement(['early_bird', 'night_owl', 'flexible', 'regular']),
                'fitness_level' => fake()->randomElement($fitnessLevels),
                'dietary_restrictions' => fake()->randomElements(['halal', 'vegetarian', 'vegan', 'gluten_free', 'dairy_free', 'none'], rand(0, 2)),
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

            // Create user stories (Instagram-like stories)
            $numStories = rand(0, 3); // 0-3 stories per user
            for ($k = 0; $k < $numStories; $k++) {
                $storyType = fake()->randomElement(['image', 'video']);
                $expiresAt = Carbon::now()->addHours(rand(1, 24)); // Stories expire in 1-24 hours
                
                UserStory::create([
                    'user_id' => $user->id,
                    'media_url' => $storyType === 'image' 
                        ? 'https://picsum.photos/400/600' 
                        : 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                    'thumbnail_url' => 'https://picsum.photos/200/300',
                    'type' => $storyType,
                    'caption' => fake()->randomElement($storyCaptions),
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                ]);
            }

            // Create device tokens for push notifications (1-2 devices per user)
            $numDevices = rand(1, 2);
            $deviceTypes = ['PHONE', 'TABLET', 'DESKTOP', 'OTHER'];
            $osNames = ['iOS', 'Android', 'Windows', 'macOS', 'Linux'];
            $brands = ['Apple', 'Samsung', 'Google', 'Huawei', 'Xiaomi', 'OnePlus', 'Sony', 'LG'];
            
            for ($d = 0; $d < $numDevices; $d++) {
                DeviceToken::create([
                    'user_id' => $user->id,
                    'token' => 'device_token_' . Str::random(64),
                    'device_name' => fake()->randomElement(['iPhone 15 Pro', 'Samsung Galaxy S24', 'iPad Pro', 'MacBook Pro', 'Windows PC']),
                    'brand' => fake()->randomElement($brands),
                    'model_name' => fake()->randomElement(['iPhone 15 Pro', 'Galaxy S24', 'iPad Pro 12.9', 'MacBook Pro 16', 'Surface Laptop']),
                    'os_name' => fake()->randomElement($osNames),
                    'os_version' => fake()->randomElement(['17.0', '14.0', '13.0', '12.0', '11.0']),
                    'device_type' => fake()->randomElement($deviceTypes),
                    'is_device' => true,
                    'manufacturer' => fake()->randomElement($brands),
                ]);
            }

            // Create emergency contacts (0-3 contacts per user)
            $numEmergencyContacts = rand(0, 3);
            $relationships = ['parent', 'sibling', 'partner', 'friend', 'guardian', 'relative', 'colleague', 'other'];
            
            for ($e = 0; $e < $numEmergencyContacts; $e++) {
                UserEmergencyContact::create([
                    'user_id' => $user->id,
                    'name' => fake()->name(),
                    'relationship' => fake()->randomElement($relationships),
                    'phone' => fake()->phoneNumber(),
                    'email' => rand(0, 1) ? fake()->email() : null,
                    'is_primary' => $e === 0, // First contact is primary
                    'receives_panic_alerts' => rand(0, 1),
                    'receives_location_updates' => rand(0, 1),
                    'receives_date_check_ins' => rand(0, 1),
                    'priority_order' => $e + 1,
                    'is_verified' => rand(0, 1),
                    'verified_at' => rand(0, 1) ? now() : null,
                    'notification_preferences' => [
                        'preferred_method' => fake()->randomElement(['sms', 'call', 'email', 'whatsapp']),
                        'whatsapp' => rand(0, 1),
                        'quiet_hours_start' => '22:00',
                        'quiet_hours_end' => '08:00',
                    ],
                ]);
            }

            // Create some matchmakers (5% of users become matchmakers)
            if (rand(1, 20) === 1) { // 5% chance
                $specializations = ['traditional', 'modern', 'religious', 'professional', 'international', 'senior', 'lgbtq'];
                $matchmakerLanguages = ['English', 'Uzbek', 'Russian', 'Spanish', 'French', 'German', 'Turkish'];
                
                Matchmaker::create([
                    'user_id' => $user->id,
                    'business_name' => fake()->randomElement(['Perfect Match', 'Soul Connections', 'Love Bridge', 'Heart Makers', 'Divine Matches', 'True Love Agency']) . ' ' . fake()->randomElement(['Agency', 'Services', 'Consulting', 'Matchmaking']),
                    'bio' => fake()->paragraph(rand(3, 6)),
                    'phone' => fake()->phoneNumber(),
                    'website' => rand(0, 1) ? 'https://' . fake()->domainName() : null,
                    'specializations' => fake()->randomElements($specializations, rand(1, 3)),
                    'languages' => fake()->randomElements($matchmakerLanguages, rand(1, 3)),
                    'years_experience' => rand(1, 20),
                    'success_rate' => fake()->randomFloat(2, 60, 95),
                    'successful_matches' => rand(10, 200),
                    'total_clients' => rand(20, 300),
                    'verification_status' => fake()->randomElement(['pending', 'verified', 'rejected']),
                    'verified_at' => rand(0, 1) ? now() : null,
                    'is_active' => rand(0, 1),
                    'rating' => fake()->randomFloat(1, 3.0, 5.0),
                    'reviews_count' => rand(0, 50),
                ]);
            }

            // Create verification requests and badges (30% of users have verification data)
            if (rand(1, 10) <= 3) {
                $verificationTypes = ['identity', 'photo', 'employment', 'education', 'income', 'address', 'social_media', 'background_check'];
                $verificationStatuses = ['pending', 'approved', 'rejected', 'needs_review'];
                
                // Create verification request
                $verificationType = fake()->randomElement($verificationTypes);
                $verificationRequest = VerificationRequest::create([
                    'user_id' => $user->id,
                    'verification_type' => $verificationType,
                    'submitted_data' => $this->generateVerificationData($verificationType),
                    'user_notes' => rand(0, 1) ? fake()->sentence() : null,
                    'status' => fake()->randomElement($verificationStatuses),
                    'submitted_at' => Carbon::now()->subDays(rand(0, 30)),
                    'reviewed_at' => rand(0, 1) ? Carbon::now()->subDays(rand(0, 15)) : null,
                    'reviewed_by' => rand(0, 1) ? 1 : null, // Assuming admin user ID 1
                ]);

                // Create verified badge if approved
                if ($verificationRequest->status === 'approved') {
                    $badgeType = $this->getBadgeTypeFromVerificationType($verificationType);
                    UserVerifiedBadge::create([
                        'user_id' => $user->id,
                        'badge_type' => $badgeType,
                        'verified_at' => $verificationRequest->reviewed_at,
                        'verified_by' => $verificationRequest->reviewed_by,
                    ]);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        $this->command->info('Successfully created 500 users with complete enhanced profiles, cultural data, stories, device tokens, emergency contacts, matchmakers, and verification data!');

        // Create interactions
        $this->createLikesAndMatches();
        $this->createChatsAndMessages();
    }

    /**
     * Generate verification data based on verification type
     */
    private function generateVerificationData(string $verificationType): array
    {
        return match ($verificationType) {
            'identity' => [
                'full_name' => fake()->name(),
                'date_of_birth' => fake()->date('Y-m-d', '2000-01-01'),
                'id_number' => fake()->numerify('##########'),
                'document_type' => fake()->randomElement(['passport', 'drivers_license', 'national_id']),
            ],
            'employment' => [
                'company_name' => fake()->company(),
                'position' => fake()->jobTitle(),
                'employment_start_date' => fake()->date('Y-m-d', '2020-01-01'),
                'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contract']),
            ],
            'education' => [
                'institution_name' => fake()->randomElement(['Tashkent State University', 'Samarkand State University', 'Harvard University', 'Stanford University', 'MIT']),
                'degree' => fake()->randomElement(['Bachelor', 'Master', 'PhD', 'Associate', 'Certificate']),
                'graduation_year' => fake()->numberBetween(1990, 2024),
                'field_of_study' => fake()->randomElement(['Computer Science', 'Medicine', 'Business', 'Engineering', 'Arts']),
            ],
            'income' => [
                'annual_income' => fake()->numberBetween(20000, 200000),
                'income_currency' => fake()->randomElement(['USD', 'UZS', 'EUR']),
                'income_source' => fake()->randomElement(['salary', 'business', 'investments', 'freelance']),
            ],
            'address' => [
                'street_address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'postal_code' => fake()->postcode(),
                'country' => fake()->country(),
            ],
            'social_media' => [
                'instagram_url' => 'https://instagram.com/' . fake()->userName(),
                'facebook_url' => 'https://facebook.com/' . fake()->userName(),
                'linkedin_url' => 'https://linkedin.com/in/' . fake()->userName(),
            ],
            'background_check' => [
                'criminal_record' => 'none',
                'employment_history' => fake()->sentence(),
                'references' => fake()->sentence(),
            ],
            default => []
        };
    }

    /**
     * Get badge type from verification type
     */
    private function getBadgeTypeFromVerificationType(string $verificationType): string
    {
        return match ($verificationType) {
            'identity' => 'identity_verified',
            'photo' => 'photo_verified',
            'employment' => 'employment_verified',
            'education' => 'education_verified',
            'income' => 'income_verified',
            'address' => 'address_verified',
            'social_media' => 'social_verified',
            'background_check' => 'background_check',
            default => 'general_verified'
        };
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
        
        // Show statistics
        $totalLikes = Like::count();
        $totalMatches = MatchModel::count();
        $totalDislikes = Dislike::count();
        
        $this->command->info("Likes, matches, and dislikes created successfully!");
        $this->command->info("📊 Statistics:");
        $this->command->info("   • Total Likes: {$totalLikes}");
        $this->command->info("   • Total Matches: {$totalMatches}");
        $this->command->info("   • Total Dislikes: {$totalDislikes}");
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
            "Hey! I noticed we're both from the same city. Small world! 😊",
            "Hi! Your bio made me laugh. You seem like a fun person!",
            "Hello! I see we both love cooking. What's your signature dish?",
            "Hey! I'm really impressed by your career. How did you get into that field?",
            "Hi there! I love your style in the photos. Where do you usually shop?",
            "Hello! I see we both enjoy reading. Any book recommendations?",
            "Hey! Your smile in that photo is contagious! 😄",
            "Hi! I noticed we both love animals. Do you have any pets?",
            "Hello! I see you're into fitness. What's your favorite workout?",
            "Hey! Your travel photos are amazing. Where's next on your list?",
            "Hi there! I love your taste in music. Who are you listening to lately?",
            "Hello! I see we both enjoy art. Do you create anything yourself?",
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
            "Aww, thank you! That's so sweet of you to say 😊",
            "I know right? It's such a small world sometimes!",
            "That's so kind of you! I try to keep things light and fun",
            "Oh wow, really? I'd love to hear more about your experience!",
            "You're too kind! I'm still learning but I love it",
            "That's so interesting! I've always been curious about that",
            "Haha, you caught me! I do have a few hidden talents 😄",
            "That's exactly what I was thinking! Great minds think alike",
            "I'm so glad you think so! It's one of my favorite things to do",
            "You're absolutely right! I couldn't agree more",
            "That's such a great question! Let me think about that...",
            "I love that about you too! It's so refreshing to meet someone like-minded",
            "That's so thoughtful of you to ask! I really appreciate it",
            "You're making me blush! 😊 That's so sweet",
            "I'm so happy we matched! This is already so much fun",
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
            "What's your ideal Saturday night?",
            "If you could have dinner with anyone, who would it be?",
            "What's something you're really passionate about?",
            "Do you have any favorite local spots?",
            "What's the best advice you've ever received?",
            "If you could travel anywhere right now, where would you go?",
            "What's your go-to comfort food?",
            "Do you have any hobbies you're really into?",
            "What's something that always makes you smile?",
            "If you could learn any new skill, what would it be?",
        ];

        foreach ($createdChats as $chat) {
            $users = $chat->users->shuffle();
            $user1 = $users->first();
            $user2 = $users->last();

            // 80% chance this chat will have messages
            if (rand(1, 10) <= 8) {
                $messageCount = rand(5, 35); // Random conversation length (increased)
                $currentSender = $users->random();
                $lastMessageTime = Carbon::now()->subDays(rand(0, 15));

                // Special message types for more realistic conversations
                $specialMessages = [
                    "Haha, you're funny! 😂",
                    "That's so interesting! Tell me more",
                    "I love that! We have so much in common",
                    "You seem really cool! I'm enjoying our chat",
                    "This is such a great conversation!",
                    "I'm really glad we matched!",
                    "You're making me laugh so much! 😄",
                    "I can't believe we have so much in common!",
                    "This is exactly what I needed today!",
                    "You seem like such a genuine person!",
                ];

                $emojiMessages = [
                    "😊", "😄", "😂", "🥰", "😍", "🤗", "😘", "😉", "😎", "🤩",
                    "❤️", "💕", "💖", "💝", "✨", "🌟", "🎉", "🎊", "🔥", "💯"
                ];

                for ($j = 0; $j < $messageCount; $j++) {
                    $content = '';
                    $messageType = 'text';

                    if ($j === 0) {
                        // First message is always a conversation starter
                        $content = fake()->randomElement($conversationStarters);
                    } elseif ($j % 4 === 0 && $j > 1) {
                        // Every fourth message ask a follow-up question
                        $content = fake()->randomElement($followUpQuestions);
                    } elseif ($j % 7 === 0 && $j > 3) {
                        // Every seventh message is a special message
                        $content = fake()->randomElement($specialMessages);
                    } elseif (rand(1, 10) === 1) {
                        // 10% chance of emoji-only message
                        $content = fake()->randomElement($emojiMessages);
                    } else {
                        // Regular responses
                        $content = fake()->randomElement($responses);
                    }

                    $messageTime = $lastMessageTime->copy()->addMinutes(rand(5, 120));

                    $message = Message::create([
                        'chat_id' => $chat->id,
                        'sender_id' => $currentSender->id,
                        'content' => $content,
                        'message_type' => $messageType,
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

                    // Some messages have media (8% chance)
                    if (rand(1, 12) === 1) {
                        $mediaType = fake()->randomElement(['image', 'video']);
                        $message->update([
                            'message_type' => $mediaType,
                            'media_url' => $mediaType === 'image' 
                                ? 'https://picsum.photos/400/300' 
                                : 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
                            'thumbnail_url' => 'https://picsum.photos/100/100',
                            'media_data' => [
                                'width' => $mediaType === 'image' ? 400 : 1280,
                                'height' => $mediaType === 'image' ? 300 : 720,
                                'size' => rand(100000, 2000000),
                                'duration' => $mediaType === 'video' ? rand(10, 60) : null
                            ]
                        ]);
                    }

                    // Some messages are delivered (70% chance)
                    if (rand(1, 10) <= 7) {
                        $message->update([
                            'status' => 'delivered'
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
        $totalChats = $createdChats->count();
        $activeChats = Chat::where('is_active', true)->count();
        $unreadMessages = Message::where('status', 'sent')->count();
        
        $this->command->info("Successfully created {$totalChats} chats with {$totalMessages} messages!");
        $this->command->info("💬 Chat Statistics:");
        $this->command->info("   • Total Chats: {$totalChats}");
        $this->command->info("   • Active Chats: {$activeChats}");
        $this->command->info("   • Total Messages: {$totalMessages}");
        $this->command->info("   • Unread Messages: {$unreadMessages}");
    }
}

<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthService
{
    protected $mediaUploadService;
    protected $imageProcessingService;

    public function __construct(
        MediaUploadService $mediaUploadService,
        ImageProcessingService $imageProcessingService
    ) {
        $this->mediaUploadService = $mediaUploadService;
        $this->imageProcessingService = $imageProcessingService;
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     * @throws \Exception
     */
    public function register(array $data): array
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'registration_completed' => true,
                'is_private' => $data['is_private'] ?? false
            ]);

            // Find country if provided
            $country_id = null;
            if (!empty($data['country'])) {
                $country = \App\Models\Country::where('name', $data['country'])
                    ->orWhere('code', $data['country'])
                    ->first();

                if ($country) {
                    $country_id = $country->id;
                }
            }

            // Calculate age from date of birth
            $age = null;
            if (!empty($data['date_of_birth'])) {
                $birthDate = new \DateTime($data['date_of_birth']);
                $today = new \DateTime('today');
                $age = $birthDate->diff($today)->y;
            }

            // Create profile
            $user->profile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'country_id' => $country_id,
                'age' => $age,
                'profile_completed_at' => now()
            ]);

            // Create default preferences
            $user->preference()->create([
                'search_radius' => 10, // default value
                'min_age' => 18,
                'max_age' => 99
            ]);

            // Commit transaction
            DB::commit();

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('profile'),
                'token' => $token
            ];
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Login user
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $authCredentials = [];
        if (isset($credentials['email'])) {
            $authCredentials['email'] = $credentials['email'];
        } else {
            $authCredentials['phone'] = $credentials['phone'];
        }
        $authCredentials['password'] = $credentials['password'];

        if (!Auth::attempt($authCredentials)) {
            throw ValidationException::withMessages([
                'credentials' => ['The provided credentials are incorrect.']
            ]);
        }

        $user = Auth::user();

        // Check if user is disabled
        if ($user->disabled_at !== null) {
            throw ValidationException::withMessages([
                'account' => ['Account is disabled']
            ]);
        }

        // Generate new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['profile', 'preference']),
            'token' => $token
        ];
    }

    /**
     * Logout user
     *
     * @param User $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Complete registration for OTP users
     *
     * @param User $user
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function completeRegistration(User $user, array $data): User
    {
        try {
            DB::beginTransaction();

            // Update user
            $user->update([
                'email' => $data['email'] ?? $user->email,
                'registration_completed' => true,
                'is_private' => $data['is_private'] ?? $user->is_private,
                'phone_verified_at' => now()
            ]);

            // Find country if provided
            $country_id = null;
            if (!empty($data['country'])) {
                $country = \App\Models\Country::where('name', $data['country'])
                    ->orWhere('code', $data['country'])
                    ->first();

                if ($country) {
                    $country_id = $country->id;
                }
            }

            // Calculate age from date of birth
            $age = null;
            if (!empty($data['dateOfBirth'])) {
                $birthDate = new \DateTime($data['dateOfBirth']);
                $today = new \DateTime('today');
                $age = $birthDate->diff($today)->y;
            }

            // Create or update profile
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['firstName'],
                    'last_name' => $data['lastName'],
                    'date_of_birth' => $data['dateOfBirth'],
                    'gender' => $data['gender'],
                    'profession' => $data['profession'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'interests' => $data['interests'] ?? null,
                    'state' => $data['state'] ?? null,
                    'city' => $data['city'] ?? null,
                    'country_id' => $country_id,
                    'age' => $age,
                    'status' => $data['status'] ?? null,
                    'occupation' => $data['occupation'] ?? null,
                    'looking_for' => $data['lookingFor'] ?? 'all',
                    'profile_completed_at' => now()
                ]
            );

            // Create or update preferences
            $user->preference()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'search_radius' => 10, // default value
                    'min_age' => 18,
                    'max_age' => 99
                ]
            );

            // Handle photos if provided
            if (isset($data['photos']) && is_array($data['photos']) && !empty($data['photos'])) {
                $mainPhotoIndex = isset($data['mainPhotoIndex']) ? (int)$data['mainPhotoIndex'] : 0;
                $profilePhotoUrl = null; // Track the profile photo URL

                // Process each photo
                foreach ($data['photos'] as $index => $photoData) {
                    // Skip if photo data is empty or invalid
                    if (empty($photoData) || (!is_array($photoData) && !($photoData instanceof \Illuminate\Http\UploadedFile))) {
                        continue;
                    }

                    // Check if this is the main photo
                    $isProfilePhoto = ($index == $mainPhotoIndex);

                    // If this is set as profile photo, unset any existing profile photos
                    if ($isProfilePhoto) {
                        \App\Models\UserPhoto::where('user_id', $user->id)
                            ->where('is_profile_photo', true)
                            ->update(['is_profile_photo' => false]);
                    }

                    $originalUrl = null;
                    $thumbnailUrl = null;
                    $mediumUrl = null;

                    if ($photoData instanceof \Illuminate\Http\UploadedFile) {
                        // Handle uploaded file from web using MediaUploadService
                        try {
                            $uploadResult = $this->mediaUploadService->uploadMedia(
                                $photoData, 
                                'profile_photos', 
                                $user->id, 
                                ['is_profile_photo' => $isProfilePhoto]
                            );

                            $originalUrl = $uploadResult['original_url'];
                            $thumbnailUrl = $uploadResult['thumbnail_url'];
                            $mediumUrl = $uploadResult['medium_url'];

                        } catch (\Exception $e) {
                            \Log::error("Failed to upload photo: " . $e->getMessage());
                            continue;
                        }

                    } else if (is_array($photoData) && isset($photoData['name'])) {
                        // Handle photo data from mobile app
                        // For mobile uploads, we assume the file is already uploaded to S3
                        // and we just need to create the database record
                        $filename = $photoData['name'];
                        $s3Path = "media/profile_photos/{$user->id}/{$filename}";
                        
                        // Check if the file exists in S3
                        if (!Storage::disk('s3')->exists($s3Path)) {
                            \Log::warning("Photo file not found in S3: " . $s3Path);
                            continue;
                        }

                        $originalUrl = Storage::disk('s3')->url($s3Path);
                        
                        // For mobile uploads, we might need to generate thumbnails
                        // This could be done asynchronously or on-demand
                        $thumbnailUrl = $originalUrl; // Fallback to original for now
                        $mediumUrl = $originalUrl; // Fallback to original for now
                    } else {
                        // Skip invalid photo data
                        continue;
                    }

                    // Store the profile photo URL for updating the user record
                    if ($isProfilePhoto) {
                        $profilePhotoUrl = $originalUrl;
                    }

                    // Create photo record with all required fields
                    \App\Models\UserPhoto::create([
                        'user_id' => $user->id,
                        'original_url' => $originalUrl,
                        'thumbnail_url' => $thumbnailUrl,
                        'medium_url' => $mediumUrl,
                        'is_profile_photo' => $isProfilePhoto,
                        'order' => $index,
                        'is_private' => $data['is_private'] ?? false,
                        'is_verified' => false,
                        'status' => 'pending',
                        'uploaded_at' => now(),
                    ]);
                }

                // Update user's profile_photo_path if a profile photo was set
                if ($profilePhotoUrl) {
                    $user->update(['profile_photo_path' => $profilePhotoUrl]);
                }
            }

            DB::commit();

            return $user->fresh(['profile', 'preference', 'photos']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }
}

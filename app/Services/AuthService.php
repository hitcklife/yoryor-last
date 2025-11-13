<?php

namespace App\Services;

use App\Models\User;
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
     * @param  bool  $createSession  Create session for SPA mode (Next.js), false for token mode (React Native)
     *
     * @throws \Exception
     */
    public function register(array $data, bool $createSession = false): array
    {
        try {
            // Begin transaction
            DB::beginTransaction();

            // Create user - registration NOT completed yet, needs to go through onboarding
            $user = User::create([
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'registration_completed' => false,
                'is_private' => $data['is_private'] ?? false,
            ]);

            // Find country if provided
            $country_id = null;
            if (! empty($data['country'])) {
                $country = \App\Models\Country::where('name', $data['country'])
                    ->orWhere('code', $data['country'])
                    ->first();

                if ($country) {
                    $country_id = $country->id;
                }
            }

            // Calculate age from date of birth
            $age = null;
            if (! empty($data['date_of_birth'])) {
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
                'profile_completed_at' => now(),
            ]);

            // Create default preferences
            $user->preference()->create([
                'search_radius' => 10, // default value
                'min_age' => 18,
                'max_age' => 99,
            ]);

            // Commit transaction
            DB::commit();

            // For SPA mode (Next.js): Create session, no token
            if ($createSession) {
                Auth::login($user);

                return [
                    'user' => $user->load('profile'),
                    'token' => null,
                ];
            }

            // For mobile mode (React Native): Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('profile'),
                'token' => $token,
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
     * @param  bool  $createSession  Create session for SPA mode (Next.js), false for token mode (React Native)
     *
     * @throws ValidationException
     */
    public function login(array $credentials, bool $createSession = false): array
    {
        $authCredentials = [];
        if (isset($credentials['email'])) {
            $authCredentials['email'] = $credentials['email'];
        } else {
            $authCredentials['phone'] = $credentials['phone'];
        }
        $authCredentials['password'] = $credentials['password'];

        // Find user and verify password manually to avoid creating unwanted sessions
        $user = User::where(function ($query) use ($authCredentials) {
            if (isset($authCredentials['email'])) {
                $query->where('email', $authCredentials['email']);
            } else {
                $query->where('phone', $authCredentials['phone']);
            }
        })->first();

        if (! $user || ! Hash::check($authCredentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is disabled
        if ($user->disabled_at !== null) {
            throw ValidationException::withMessages([
                'account' => ['Account is disabled'],
            ]);
        }

        // Update last login timestamp
        $user->update(['last_login_at' => now()]);

        // For SPA mode (Next.js): Create session, no token
        if ($createSession) {
            Auth::login($user);

            return [
                'user' => $user->load(['profile', 'preference']),
                'token' => null,
            ];
        }

        // For mobile mode (React Native): Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load(['profile', 'preference']),
            'token' => $token,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Authenticate user with OTP
     */
    public function authenticateWithOtp(string $phone, string $otpCode): array
    {
        try {
            $otpService = new OtpService;
            $result = $otpService->verifyOtp($phone, $otpCode);

            return [
                'success' => true,
                'user' => $result['user'],
                'token' => $result['token'],
                'is_new_user' => $result['is_new_user'],
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Authentication failed. Please try again.',
            ];
        }
    }

    /**
     * Complete registration for OTP users
     *
     * @throws \Exception
     */
    public function completeRegistration(User $user, array $data): User
    {
        // Check if registration is already completed
        if ($user->registration_completed) {
            throw new \Exception('Registration already completed');
        }

        try {
            DB::beginTransaction();

            // Update user
            // Convert profile_private to boolean if it's a string "1" or "0"
            $isPrivate = $user->is_private; // default to current value
            if (isset($data['profile_private'])) {
                $isPrivate = filter_var($data['profile_private'], FILTER_VALIDATE_BOOLEAN);
            }

            $user->update([
                'email' => $data['email'] ?? $user->email,
                'registration_completed' => true,
                'is_private' => $isPrivate,
                'phone_verified_at' => now(),
            ]);

            // Find country if provided
            $country_id = null;
            if (! empty($data['country'])) {
                $country = \App\Models\Country::where('name', $data['country'])
                    ->orWhere('code', $data['country'])
                    ->first();

                if ($country) {
                    $country_id = $country->id;
                }
            }

            // Calculate age from date of birth
            $age = null;
            if (! empty($data['dateOfBirth'])) {
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
                    'looking_for_relationship' => $data['lookingFor'] ?? 'open',
                    'profile_completed_at' => now(),
                ]
            );

            // Create or update preferences
            $user->preference()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'search_radius' => 10, // default value
                    'min_age' => 18,
                    'max_age' => 99,
                ]
            );

            // Handle photos if provided
            if (isset($data['photos']) && is_array($data['photos']) && ! empty($data['photos'])) {
                $mainPhotoIndex = isset($data['mainPhotoIndex']) ? (int) $data['mainPhotoIndex'] : 0;

                // Process each photo
                foreach ($data['photos'] as $index => $photoData) {
                    // Skip if photo data is empty or invalid
                    if (empty($photoData) || (! is_array($photoData) && ! ($photoData instanceof \Illuminate\Http\UploadedFile))) {
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
                            \Log::error('Failed to upload photo: '.$e->getMessage());

                            continue;
                        }

                    } elseif (is_array($photoData) && isset($photoData['name'])) {
                        // Handle photo data from mobile app
                        // For mobile uploads, we assume the file is already uploaded to R2
                        // and we just need to create the database record
                        $filename = $photoData['name'];
                        $r2Path = "media/profile_photos/{$user->id}/{$filename}";

                        // Check if the file exists in R2
                        if (! Storage::disk('r2')->exists($r2Path)) {
                            \Log::warning('Photo file not found in R2: '.$r2Path);

                            continue;
                        }

                        $originalUrl = Storage::disk('r2')->url($r2Path);

                        // For mobile uploads, we might need to generate thumbnails
                        // This could be done asynchronously or on-demand
                        $thumbnailUrl = $originalUrl; // Fallback to original for now
                        $mediumUrl = $originalUrl; // Fallback to original for now
                    } else {
                        // Skip invalid photo data
                        continue;
                    }

                    // Profile photo is now handled by the UserPhoto model's is_profile_photo flag

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

                // The profile photo is now handled by the UserPhoto model's is_profile_photo flag
                // No need to update user's profile_photo_path as it's been removed
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

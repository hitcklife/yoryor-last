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
                'registration_completed' => true
            ]);

            // Create profile
            $user->profile()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender']
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
                'registration_completed' => true
            ]);

            // Create or update profile
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => $data['firstName'],
                    'last_name' => $data['lastName'],
                    'date_of_birth' => $data['dateOfBirth'],
                    'gender' => $data['gender'],
                    'status' => $data['status'] ?? null,
                    'occupation' => $data['occupation'] ?? null,
                    'profession' => $data['profession'] ?? null,
                    'bio' => $data['bio'] ?? null,
                    'interests' => $data['interests'] ?? null,
                    'country_code' => $data['countryCode'] ?? null,
                    'state' => $data['state'] ?? null,
                    'city' => $data['city'] ?? null
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
            if (isset($data['photos']) && is_array($data['photos'])) {
                $mainPhotoIndex = isset($data['mainPhotoIndex']) ? (int)$data['mainPhotoIndex'] : 0;

                // Process each photo
                foreach ($data['photos'] as $index => $photoData) {
                    // Check if this is the main photo
                    $isProfilePhoto = ($index == $mainPhotoIndex);

                    // If this is set as profile photo, unset any existing profile photos
                    if ($isProfilePhoto) {
                        \App\Models\UserPhoto::where('user_id', $user->id)
                            ->where('is_profile_photo', true)
                            ->update(['is_profile_photo' => false]);
                    }

                    // Generate a unique filename for the photo
                    if ($photoData instanceof \Illuminate\Http\UploadedFile) {
                        // Handle uploaded file
                        $filename = $photoData->getClientOriginalName();
                        // Alternatively, generate a unique name
                        // $filename = time() . '_' . $photoData->getClientOriginalName();
                    } else {
                        // Handle photo data as array (from mobile app)
                        $filename = $photoData['name'];
                    }
                    $directory = 'photos/' . $user->id;

                    // Ensure the directory exists
                    if (!Storage::disk('public')->exists($directory)) {
                        Storage::disk('public')->makeDirectory($directory);
                    }

                    // Handle file storage based on the type of data
                    if ($photoData instanceof \Illuminate\Http\UploadedFile) {
                        // For web uploads, store the actual file
                        $path = $photoData->storeAs($directory, $filename, 'public');
                        $photoUrl = '/storage/' . $path;
                    } else {
                        // For mobile app uploads, the files are already uploaded and we just need to store the references
                        // The mobile app sends the photo details (name and size) in the request
                        $path = $directory . '/' . $filename;
                        $photoUrl = '/storage/' . $path;

                        // If we need to create an empty file as a placeholder (optional)
                        // Storage::disk('public')->put($path, '');
                    }

                    // Create photo record
                    \App\Models\UserPhoto::create([
                        'user_id' => $user->id,
                        'photo_url' => $photoUrl,
                        'is_profile_photo' => $isProfilePhoto,
                        'order' => $index,
                        'is_private' => false,
                    ]);
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

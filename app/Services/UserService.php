<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Update user profile
     *
     * @param User $user
     * @param array $profileData
     * @return User
     * @throws \Exception
     */
    public function updateProfile(User $user, array $profileData): User
    {
        try {
            DB::beginTransaction();

            // Update user data if provided
            if (isset($profileData['email'])) {
                $user->email = $profileData['email'];
            }

            if (isset($profileData['phone'])) {
                $user->phone = $profileData['phone'];
            }

            if (isset($profileData['password'])) {
                $user->password = Hash::make($profileData['password']);
            }

            $user->save();

            // Update profile data
            $profileFields = array_intersect_key($profileData, array_flip([
                'first_name', 'last_name', 'gender', 'date_of_birth',
                'city', 'state', 'province', 'country_id',
                'latitude', 'longitude'
            ]));

            if (!empty($profileFields)) {
                $user->profile()->updateOrCreate(
                    ['user_id' => $user->id],
                    $profileFields
                );
            }

            DB::commit();

            return $user->fresh(['profile']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update user preferences
     *
     * @param User $user
     * @param array $preferenceData
     * @return User
     * @throws \Exception
     */
    public function updatePreferences(User $user, array $preferenceData): User
    {
        try {
            DB::beginTransaction();

            $user->preference()->updateOrCreate(
                ['user_id' => $user->id],
                $preferenceData
            );

            DB::commit();

            return $user->fresh(['preference']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Disable user account
     *
     * @param User $user
     * @return User
     */
    public function disableAccount(User $user): User
    {
        $user->disabled_at = now();
        $user->save();

        return $user;
    }

    /**
     * Enable user account
     *
     * @param User $user
     * @return User
     */
    public function enableAccount(User $user): User
    {
        $user->disabled_at = null;
        $user->save();

        return $user;
    }

    /**
     * Get user by ID with relationships
     *
     * @param int $userId
     * @param array $relations
     * @return User|null
     */
    public function getUserById(int $userId, array $relations = []): ?User
    {
        return User::with($relations)->find($userId);
    }

    /**
     * Get user by email
     *
     * @param string $email
     * @param array $relations
     * @return User|null
     */
    public function getUserByEmail(string $email, array $relations = []): ?User
    {
        return User::with($relations)->where('email', $email)->first();
    }

    /**
     * Get user by phone
     *
     * @param string $phone
     * @param array $relations
     * @return User|null
     */
    public function getUserByPhone(string $phone, array $relations = []): ?User
    {
        return User::with($relations)->where('phone', $phone)->first();
    }
}

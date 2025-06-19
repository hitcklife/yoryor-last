<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any profiles.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('profile.view.any');
    }

    /**
     * Determine whether the user can view the profile.
     *
     * @param User $user
     * @param Profile $profile
     * @return bool
     */
    public function view(User $user, Profile $profile): bool
    {
        // Users can always view their own profile
        if ($user->id === $profile->user_id) {
            return true;
        }

        return $user->hasPermission('profile.view');
    }

    /**
     * Determine whether the user can create profiles.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Users can only create their own profile
        if (!$user->profile) {
            return true;
        }

        return $user->hasPermission('profile.create');
    }

    /**
     * Determine whether the user can update the profile.
     *
     * @param User $user
     * @param Profile $profile
     * @return bool
     */
    public function update(User $user, Profile $profile): bool
    {
        // Users can always update their own profile
        if ($user->id === $profile->user_id) {
            return true;
        }

        return $user->hasPermission('profile.update');
    }

    /**
     * Determine whether the user can delete the profile.
     *
     * @param User $user
     * @param Profile $profile
     * @return bool
     */
    public function delete(User $user, Profile $profile): bool
    {
        // Users can delete their own profile
        if ($user->id === $profile->user_id) {
            return true;
        }

        return $user->hasPermission('profile.delete');
    }

    /**
     * Determine whether the user can restore the profile.
     *
     * @param User $user
     * @param Profile $profile
     * @return bool
     */
    public function restore(User $user, Profile $profile): bool
    {
        return $user->hasPermission('profile.restore');
    }

    /**
     * Determine whether the user can permanently delete the profile.
     *
     * @param User $user
     * @param Profile $profile
     * @return bool
     */
    public function forceDelete(User $user, Profile $profile): bool
    {
        return $user->hasPermission('profile.force.delete');
    }
}

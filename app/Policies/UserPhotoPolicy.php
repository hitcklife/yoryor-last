<?php

namespace App\Policies;

use App\Models\UserPhoto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPhotoPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any user photos.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view photos
        return true;
    }

    /**
     * Determine whether the user can view the user photo.
     *
     * @param User $user
     * @param UserPhoto $userPhoto
     * @return bool
     */
    public function view(User $user, UserPhoto $userPhoto): bool
    {
        // Users can view photos
        return true;
    }

    /**
     * Determine whether the user can create user photos.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Users can create their own photos
        return true;
    }

    /**
     * Determine whether the user can update the user photo.
     *
     * @param User $user
     * @param UserPhoto $userPhoto
     * @return bool
     */
    public function update(User $user, UserPhoto $userPhoto): bool
    {
        // Users can only update their own photos
        if ($user->id === $userPhoto->user_id) {
            return true;
        }

        return $user->hasPermission('user.photo.update');
    }

    /**
     * Determine whether the user can delete the user photo.
     *
     * @param User $user
     * @param UserPhoto $userPhoto
     * @return bool
     */
    public function delete(User $user, UserPhoto $userPhoto): bool
    {
        // Users can only delete their own photos
        if ($user->id === $userPhoto->user_id) {
            return true;
        }

        return $user->hasPermission('user.photo.delete');
    }

    /**
     * Determine whether the user can restore the user photo.
     *
     * @param User $user
     * @param UserPhoto $userPhoto
     * @return bool
     */
    public function restore(User $user, UserPhoto $userPhoto): bool
    {
        // Only admins can restore photos
        return $user->hasPermission('user.photo.restore');
    }

    /**
     * Determine whether the user can permanently delete the user photo.
     *
     * @param User $user
     * @param UserPhoto $userPhoto
     * @return bool
     */
    public function forceDelete(User $user, UserPhoto $userPhoto): bool
    {
        // Only admins can force delete photos
        return $user->hasPermission('user.photo.force.delete');
    }
}

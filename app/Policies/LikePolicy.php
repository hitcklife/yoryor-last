<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LikePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any likes.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view likes
        return true;
    }

    /**
     * Determine whether the user can view the like.
     *
     * @param User $user
     * @param Like $like
     * @return bool
     */
    public function view(User $user, Like $like): bool
    {
        // Users can view likes if they are the sender or receiver
        return $like->user_id === $user->id || $like->liked_user_id === $user->id || $user->hasPermission('like.view');
    }

    /**
     * Determine whether the user can create likes.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a like
        return true;
    }

    /**
     * Determine whether the user can update the like.
     *
     * @param User $user
     * @param Like $like
     * @return bool
     */
    public function update(User $user, Like $like): bool
    {
        // Users cannot update likes, they can only create or delete them
        return false;
    }

    /**
     * Determine whether the user can delete the like.
     *
     * @param User $user
     * @param Like $like
     * @return bool
     */
    public function delete(User $user, Like $like): bool
    {
        // Users can only delete their own likes
        return $like->user_id === $user->id || $user->hasPermission('like.delete');
    }

    /**
     * Determine whether the user can restore the like.
     *
     * @param User $user
     * @param Like $like
     * @return bool
     */
    public function restore(User $user, Like $like): bool
    {
        // Only admins can restore likes
        return $user->hasPermission('like.restore');
    }

    /**
     * Determine whether the user can permanently delete the like.
     *
     * @param User $user
     * @param Like $like
     * @return bool
     */
    public function forceDelete(User $user, Like $like): bool
    {
        // Only admins can force delete likes
        return $user->hasPermission('like.force.delete');
    }
}

<?php

namespace App\Policies;

use App\Models\Dislike;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DislikePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any dislikes.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view dislikes
        return true;
    }

    /**
     * Determine whether the user can view the dislike.
     *
     * @param User $user
     * @param Dislike $dislike
     * @return bool
     */
    public function view(User $user, Dislike $dislike): bool
    {
        // Users can view dislikes if they are the sender or receiver
        return $dislike->user_id === $user->id || $dislike->disliked_user_id === $user->id || $user->hasPermission('dislike.view');
    }

    /**
     * Determine whether the user can create dislikes.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a dislike
        return true;
    }

    /**
     * Determine whether the user can update the dislike.
     *
     * @param User $user
     * @param Dislike $dislike
     * @return bool
     */
    public function update(User $user, Dislike $dislike): bool
    {
        // Users cannot update dislikes, they can only create or delete them
        return false;
    }

    /**
     * Determine whether the user can delete the dislike.
     *
     * @param User $user
     * @param Dislike $dislike
     * @return bool
     */
    public function delete(User $user, Dislike $dislike): bool
    {
        // Users can only delete their own dislikes
        return $dislike->user_id === $user->id || $user->hasPermission('dislike.delete');
    }

    /**
     * Determine whether the user can restore the dislike.
     *
     * @param User $user
     * @param Dislike $dislike
     * @return bool
     */
    public function restore(User $user, Dislike $dislike): bool
    {
        // Only admins can restore dislikes
        return $user->hasPermission('dislike.restore');
    }

    /**
     * Determine whether the user can permanently delete the dislike.
     *
     * @param User $user
     * @param Dislike $dislike
     * @return bool
     */
    public function forceDelete(User $user, Dislike $dislike): bool
    {
        // Only admins can force delete dislikes
        return $user->hasPermission('dislike.force.delete');
    }
}

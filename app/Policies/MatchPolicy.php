<?php

namespace App\Policies;

use App\Models\MatchModel;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MatchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any matches.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('match.view.any');
    }

    /**
     * Determine whether the user can view the match.
     *
     * @param User $user
     * @param MatchModel $match
     * @return bool
     */
    public function view(User $user, MatchModel $match): bool
    {
        // Users can only view matches they are part of
        if ($user->id === $match->user_id || $user->id === $match->matched_user_id) {
            return true;
        }

        return $user->hasPermission('match.view');
    }

    /**
     * Determine whether the user can create matches.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // All authenticated users can create matches
        return true;
    }

    /**
     * Determine whether the user can update the match.
     *
     * @param User $user
     * @param MatchModel $match
     * @return bool
     */
    public function update(User $user, MatchModel $match): bool
    {
        // Users can only update matches they created
        if ($user->id === $match->user_id) {
            return true;
        }

        return $user->hasPermission('match.update');
    }

    /**
     * Determine whether the user can delete the match.
     *
     * @param User $user
     * @param MatchModel $match
     * @return bool
     */
    public function delete(User $user, MatchModel $match): bool
    {
        // Users can delete matches they are part of
        if ($user->id === $match->user_id || $user->id === $match->matched_user_id) {
            return true;
        }

        return $user->hasPermission('match.delete');
    }

    /**
     * Determine whether the user can restore the match.
     *
     * @param User $user
     * @param MatchModel $match
     * @return bool
     */
    public function restore(User $user, MatchModel $match): bool
    {
        return $user->hasPermission('match.restore');
    }

    /**
     * Determine whether the user can permanently delete the match.
     *
     * @param User $user
     * @param MatchModel $match
     * @return bool
     */
    public function forceDelete(User $user, MatchModel $match): bool
    {
        return $user->hasPermission('match.force.delete');
    }
}

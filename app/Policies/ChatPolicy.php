<?php

namespace App\Policies;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any chats.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view their own chats
        return true;
    }

    /**
     * Determine whether the user can view the chat.
     *
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function view(User $user, Chat $chat): bool
    {
        // Users can only view chats they are a part of
        return $chat->users()->where('user_id', $user->id)->exists() || $user->hasPermission('chat.view');
    }

    /**
     * Determine whether the user can create chats.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a chat
        return true;
    }

    /**
     * Determine whether the user can update the chat.
     *
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function update(User $user, Chat $chat): bool
    {
        // Users can only update chats they are a part of
        return $chat->users()->where('user_id', $user->id)->exists() || $user->hasPermission('chat.update');
    }

    /**
     * Determine whether the user can delete the chat.
     *
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function delete(User $user, Chat $chat): bool
    {
        // Users can only delete chats they are a part of
        return $chat->users()->where('user_id', $user->id)->exists() || $user->hasPermission('chat.delete');
    }

    /**
     * Determine whether the user can restore the chat.
     *
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function restore(User $user, Chat $chat): bool
    {
        // Only admins can restore chats
        return $user->hasPermission('chat.restore');
    }

    /**
     * Determine whether the user can permanently delete the chat.
     *
     * @param User $user
     * @param Chat $chat
     * @return bool
     */
    public function forceDelete(User $user, Chat $chat): bool
    {
        // Only admins can force delete chats
        return $user->hasPermission('chat.force.delete');
    }
}

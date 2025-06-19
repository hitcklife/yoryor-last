<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any messages.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view messages in chats they are a part of
        return true;
    }

    /**
     * Determine whether the user can view the message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function view(User $user, Message $message): bool
    {
        // Users can only view messages in chats they are a part of
        $chat = $message->chat;
        return $chat->user_id_1 === $user->id || $chat->user_id_2 === $user->id || $user->hasPermission('message.view');
    }

    /**
     * Determine whether the user can create messages.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create a message in their chats
        return true;
    }

    /**
     * Determine whether the user can update the message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function update(User $user, Message $message): bool
    {
        // Users can only update their own messages
        return $message->sender_id === $user->id || $user->hasPermission('message.update');
    }

    /**
     * Determine whether the user can delete the message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function delete(User $user, Message $message): bool
    {
        // Users can only delete their own messages
        return $message->sender_id === $user->id || $user->hasPermission('message.delete');
    }

    /**
     * Determine whether the user can restore the message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function restore(User $user, Message $message): bool
    {
        // Only admins can restore messages
        return $user->hasPermission('message.restore');
    }

    /**
     * Determine whether the user can permanently delete the message.
     *
     * @param User $user
     * @param Message $message
     * @return bool
     */
    public function forceDelete(User $user, Message $message): bool
    {
        // Only admins can force delete messages
        return $user->hasPermission('message.force.delete');
    }
}

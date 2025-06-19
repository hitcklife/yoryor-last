<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any media.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Users can view media in chats they are a part of
        return true;
    }

    /**
     * Determine whether the user can view the media.
     *
     * @param User $user
     * @param Media $media
     * @return bool
     */
    public function view(User $user, Media $media): bool
    {
        // Users can only view media in chats they are a part of
        $message = $media->message;
        $chat = $message->chat;
        return $chat->user_id_1 === $user->id || $chat->user_id_2 === $user->id || $user->hasPermission('media.view');
    }

    /**
     * Determine whether the user can create media.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Any authenticated user can create media in their chats
        return true;
    }

    /**
     * Determine whether the user can update the media.
     *
     * @param User $user
     * @param Media $media
     * @return bool
     */
    public function update(User $user, Media $media): bool
    {
        // Users can only update their own media
        $message = $media->message;
        return $message->sender_id === $user->id || $user->hasPermission('media.update');
    }

    /**
     * Determine whether the user can delete the media.
     *
     * @param User $user
     * @param Media $media
     * @return bool
     */
    public function delete(User $user, Media $media): bool
    {
        // Users can only delete their own media
        $message = $media->message;
        return $message->sender_id === $user->id || $user->hasPermission('media.delete');
    }

    /**
     * Determine whether the user can restore the media.
     *
     * @param User $user
     * @param Media $media
     * @return bool
     */
    public function restore(User $user, Media $media): bool
    {
        // Only admins can restore media
        return $user->hasPermission('media.restore');
    }

    /**
     * Determine whether the user can permanently delete the media.
     *
     * @param User $user
     * @param Media $media
     * @return bool
     */
    public function forceDelete(User $user, Media $media): bool
    {
        // Only admins can force delete media
        return $user->hasPermission('media.force.delete');
    }
}

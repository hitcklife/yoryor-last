<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Chat channel authorization
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    if (!$user) {
        $user = request()->user();
    }
    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }
    // Check if the user is part of this chat
    return $chat->users()->where('user_id', $user->id)->exists();
});

// User's chats channel authorization - for receiving updates about new messages across all chats
Broadcast::channel('private-user.{userId}', function ($user, $userId) {
    // Ensure the user is only accessing their own channel
    return (int) $user->id === (int) $userId;
});

// Legacy user channel authorization (for backward compatibility)
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Ensure the user is only accessing their own channel
    return (int) $user->id === (int) $userId;
});

// PRESENCE CHANNELS FOR ONLINE STATUS

// General presence channel - tracks all online users globally
Broadcast::channel('presence-online-users', function ($user) {
    if (!$user || !$user->registration_completed) {
        return false;
    }

    // Update user's last active timestamp when they join presence channel
    $user->updateLastActive();

    // Return user info to be shared with other users in the presence channel
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->profile_photo_path,
        'is_online' => true,
        'joined_at' => now()->toISOString(),
        'last_active_at' => $user->last_active_at?->toISOString()
    ];
});

// Chat-specific presence channel - tracks who is actively viewing a specific chat
Broadcast::channel('presence-chat.{chatId}', function ($user, $chatId) {
    if (!$user) {
        return false;
    }

    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    // Check if the user is part of this chat
    $isChatMember = $chat->users()->where('user_id', $user->id)->exists();
    if (!$isChatMember) {
        return false;
    }

    // Update user's last active timestamp
    $user->updateLastActive();

    // Track chat activity
    if (class_exists(\App\Traits\TracksActivity::class)) {
        $user->trackChatActivity('chat_presence_joined', $chat->id);
    }

    // Return user info for chat presence
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->profile_photo_path,
        'is_online' => true,
        'is_typing' => false,
        'joined_chat_at' => now()->toISOString(),
        'last_active_at' => $user->last_active_at?->toISOString()
    ];
});

// Private version of chat presence channel for events
Broadcast::channel('private-presence-chat.{chatId}', function ($user, $chatId) {
    if (!$user) {
        return false;
    }

    $chat = Chat::find($chatId);
    if (!$chat) {
        return false;
    }

    // Check if the user is part of this chat
    $isChatMember = $chat->users()->where('user_id', $user->id)->exists();
    if (!$isChatMember) {
        return false;
    }

    return true; // Allow subscription for event listening
});

// Match/Dating specific presence channel - tracks users actively browsing for matches
Broadcast::channel('presence-dating-active', function ($user) {
    if (!$user || !$user->registration_completed) {
        return false;
    }

    // Only allow active users with complete profiles
    if (!$user->profile || !$user->profile->first_name || !$user->profile->date_of_birth) {
        return false;
    }

    // Update user's last active timestamp
    $user->updateLastActive();

    // Track dating activity
    if (class_exists(\App\Traits\TracksActivity::class)) {
        $user->logActivity('dating_browsing');
    }

    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'age' => $user->age,
        'city' => $user->profile->city,
        'avatar' => $user->profile_photo_path,
        'is_online' => true,
        'actively_dating' => true,
        'joined_at' => now()->toISOString(),
        'last_active_at' => $user->last_active_at?->toISOString()
    ];
});

// User's friends/matches presence channel - tracks online status of user's matches
Broadcast::channel('presence-user-matches.{userId}', function ($user, $userId) {
    // Ensure the user is only accessing their own matches presence channel
    if ((int) $user->id !== (int) $userId) {
        return false;
    }

    if (!$user->registration_completed) {
        return false;
    }

    // Update user's last active timestamp
    $user->updateLastActive();

    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'email' => $user->email,
        'avatar' => $user->profile_photo_path,
        'is_online' => true,
        'watching_matches' => true,
        'joined_at' => now()->toISOString(),
        'last_active_at' => $user->last_active_at?->toISOString()
    ];
});

// Private version of user matches channel for events (Laravel Echo adds 'private-' prefix)
Broadcast::channel('private-presence-user-matches.{userId}', function ($user, $userId) {
    // Ensure the user is only accessing their own matches presence channel
    if ((int) $user->id !== (int) $userId) {
        return false;
    }

    if (!$user->registration_completed) {
        return false;
    }

    return true; // Allow subscription for event listening
});

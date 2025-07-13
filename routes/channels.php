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
        'avatar' => $user->getProfilePhotoUrl(),
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


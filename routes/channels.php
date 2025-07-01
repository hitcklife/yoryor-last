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

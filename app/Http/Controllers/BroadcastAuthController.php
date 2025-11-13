<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Pusher\Pusher;

class BroadcastAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $channelName = $request->input('channel_name');
        $socketId = $request->input('socket_id');
        
        // Remove 'private-' prefix for authorization
        $channel = str_replace('private-', '', $channelName);
        
        // Log the authentication attempt
        \Log::info('Broadcasting auth attempt', [
            'user_id' => auth()->id(),
            'channel' => $channelName,
            'socket_id' => $socketId
        ]);
        
        // Use Reverb configuration
        $pusher = new Pusher(
            env('REVERB_APP_KEY'),
            env('REVERB_APP_SECRET'),
            env('REVERB_APP_ID'),
            [
                'host' => env('REVERB_HOST', 'localhost'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => false,
                'cluster' => ''
            ]
        );
        
        // Authorize based on channel type
        $authorized = false;
        
        if (str_starts_with($channel, 'user.')) {
            // User channel - check if it's the authenticated user's channel
            $userId = str_replace('user.', '', $channel);
            $authorized = (int)$userId === auth()->id();
        } elseif (str_starts_with($channel, 'chat.')) {
            // Chat channel - check if user is a member
            $chatId = str_replace('chat.', '', $channel);
            $chat = \App\Models\Chat::find($chatId);
            if ($chat) {
                $authorized = $chat->users()->where('user_id', auth()->id())->exists();
            }
        }
        
        if (!$authorized) {
            \Log::warning('Broadcasting auth denied', [
                'user_id' => auth()->id(),
                'channel' => $channelName
            ]);
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        // Generate auth signature
        $auth = $pusher->socket_auth($channelName, $socketId);
        
        \Log::info('Broadcasting auth success', [
            'user_id' => auth()->id(),
            'channel' => $channelName
        ]);
        
        // Return as JSON for proper parsing
        return response()->json(json_decode($auth, true));
    }
}
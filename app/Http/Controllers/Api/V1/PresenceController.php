<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\PresenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PresenceController extends Controller
{
    public function __construct(
        private PresenceService $presenceService
    ) {}

    /**
     * Get user's online status
     */
    public function getOnlineStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $status = $user->getOnlineStatus();

        return response()->json([
            'status' => 'success',
            'data' => $status
        ]);
    }

    /**
     * Update user's online status
     */
    public function updateOnlineStatus(Request $request): JsonResponse
    {
        $request->validate([
            'is_online' => 'required|boolean'
        ]);

        $user = $request->user();
        $isOnline = $request->boolean('is_online');

        if ($isOnline) {
            $user->goOnline();
        } else {
            $user->goOffline();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Online status updated successfully',
            'data' => [
                'is_online' => $isOnline,
                'updated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get all online users
     */
    public function getOnlineUsers(Request $request): JsonResponse
    {
        $onlineUsers = $this->presenceService->getOnlineUsers();

        return response()->json([
            'status' => 'success',
            'data' => [
                'online_users' => $onlineUsers->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'avatar' => $user->profile_photo_path,
                        'last_active_at' => $user->last_active_at?->toISOString(),
                        'presence_data' => $user->getPresenceData(),
                    ];
                }),
                'total_online' => $onlineUsers->count(),
            ]
        ]);
    }

    /**
     * Get online users in a specific chat
     */
    public function getOnlineUsersInChat(Request $request, int $chatId): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is part of the chat
        $chat = $user->chats()->findOrFail($chatId);
        
        $onlineUsers = $this->presenceService->getOnlineUsersInChat($chatId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'chat_id' => $chatId,
                'online_users' => $onlineUsers->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'avatar' => $user->profile_photo_path,
                        'last_active_at' => $user->last_active_at?->toISOString(),
                        'presence_data' => $user->getPresenceData(),
                    ];
                }),
                'total_online' => $onlineUsers->count(),
            ]
        ]);
    }

    /**
     * Get user's online matches
     */
    public function getOnlineMatches(Request $request): JsonResponse
    {
        $user = $request->user();
        $onlineMatches = $user->getOnlineMatches();

        return response()->json([
            'status' => 'success',
            'data' => [
                'online_matches' => $onlineMatches->map(function ($match) {
                    return [
                        'id' => $match->id,
                        'name' => $match->full_name,
                        'age' => $match->age,
                        'avatar' => $match->profile_photo_path,
                        'last_active_at' => $match->last_active_at?->toISOString(),
                        'presence_data' => $match->getPresenceData(),
                    ];
                }),
                'total_online_matches' => $onlineMatches->count(),
            ]
        ]);
    }

    /**
     * Update typing status in a chat
     */
    public function updateTypingStatus(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required|integer|exists:chats,id',
            'is_typing' => 'required|boolean'
        ]);

        $user = $request->user();
        $chatId = $request->integer('chat_id');
        $isTyping = $request->boolean('is_typing');

        // Check if user is part of the chat
        $chat = $user->chats()->findOrFail($chatId);

        $user->updateTypingStatus($chatId, $isTyping);

        return response()->json([
            'status' => 'success',
            'message' => 'Typing status updated successfully',
            'data' => [
                'chat_id' => $chatId,
                'is_typing' => $isTyping,
                'updated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get typing users in a chat
     */
    public function getTypingUsers(Request $request, int $chatId): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is part of the chat
        $chat = $user->chats()->findOrFail($chatId);
        
        $typingUserIds = $this->presenceService->getTypingUsersInChat($chatId);
        $typingUsers = collect();

        if (!empty($typingUserIds)) {
            $typingUsers = \App\Models\User::whereIn('id', $typingUserIds)
                ->with(['profile', 'profilePhoto'])
                ->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'chat_id' => $chatId,
                'typing_users' => $typingUsers->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->full_name,
                        'avatar' => $user->profile_photo_path,
                    ];
                }),
                'total_typing' => $typingUsers->count(),
            ]
        ]);
    }

    /**
     * Get presence statistics
     */
    public function getPresenceStatistics(Request $request): JsonResponse
    {
        $stats = $this->presenceService->getOnlineStatistics();

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }

    /**
     * Get user's presence history
     */
    public function getPresenceHistory(Request $request): JsonResponse
    {
        $request->validate([
            'days' => 'integer|min:1|max:30'
        ]);

        $user = $request->user();
        $days = $request->integer('days', 7);

        $history = $this->presenceService->getUserPresenceHistory($user->id, $days);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'period_days' => $days,
                'history' => $history,
                'total_entries' => count($history),
            ]
        ]);
    }

    /**
     * Sync online status from database (admin only)
     */
    public function syncOnlineStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is admin
        if (!$user->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $this->presenceService->syncOnlineStatusFromDatabase();

        return response()->json([
            'status' => 'success',
            'message' => 'Online status synchronized successfully'
        ]);
    }

    /**
     * Cleanup expired presence data (admin only)
     */
    public function cleanupExpiredPresence(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is admin
        if (!$user->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $this->presenceService->cleanupExpiredPresence();

        return response()->json([
            'status' => 'success',
            'message' => 'Expired presence data cleaned up successfully'
        ]);
    }

    /**
     * Heartbeat - keep user online
     */
    public function heartbeat(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->goOnline();

        return response()->json([
            'status' => 'success',
            'message' => 'Heartbeat received',
            'data' => [
                'timestamp' => now()->toISOString(),
                'is_online' => true,
            ]
        ]);
    }
} 
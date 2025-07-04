<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Broadcasting",
 *     description="API Endpoints for broadcasting channel authentication"
 * )
 */
class BroadcastingController extends Controller
{
    /**
     * Authenticate user for broadcasting channels
     *
     * @OA\Post(
     *     path="/v1/broadcasting/auth",
     *     summary="Authenticate broadcasting channel",
     *     description="Authenticates user for private broadcasting channels",
     *     operationId="authenticateBroadcasting",
     *     tags={"Broadcasting"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"channel_name", "socket_id"},
     *             @OA\Property(property="channel_name", type="string", example="chat.1", description="Broadcasting channel name"),
     *             @OA\Property(property="socket_id", type="string", example="123.456", description="Socket ID from broadcasting client")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Channel authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="auth", type="string", example="pusher_auth_signature"),
     *             @OA\Property(property="channel_data", type="string", example="{}", description="Channel data for presence channels")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Forbidden")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Channel not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Channel not found")
     *         )
     *     )
     * )
     */
    public function authenticate(Request $request): JsonResponse
    {
        try {
            // Get the authenticated user via Sanctum
            $user = $request->user();

            if (!$user) {
                Log::warning('Broadcasting auth failed: No authenticated user');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Validate request data
            $validated = $request->validate([
                'channel_name' => 'required|string',
                'socket_id' => 'required|string'
            ]);

            $channelName = $validated['channel_name'];
            $socketId = $validated['socket_id'];

            Log::info('Broadcasting authentication attempt', [
                'user_id' => $user->id,
                'channel' => $channelName,
                'socket_id' => $socketId
            ]);

            // Handle different channel types
            if (str_starts_with($channelName, 'private-chat.')) {
                return $this->authenticateChatChannel($user, $channelName, $socketId);
            }

            if (str_starts_with($channelName, 'private-App.Models.User.')) {
                return $this->authenticateUserChannel($user, $channelName, $socketId);
            }

            if (str_starts_with($channelName, 'private-user.')) {
                return $this->authenticateUserChatsChannel($user, $channelName, $socketId);
            }

            if (str_starts_with($channelName, 'user.')) {
                return $this->authenticateUserChannel($user, $channelName, $socketId);
            }

            // Handle presence channels if needed
            if (str_starts_with($channelName, 'presence-')) {
                return $this->authenticatePresenceChannel($user, $channelName, $socketId);
            }

            Log::warning('Broadcasting auth failed: Unknown channel type', [
                'channel' => $channelName,
                'user_id' => $user->id
            ]);

            return response()->json(['error' => 'Channel not found'], 404);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Broadcasting auth validation failed', [
                'errors' => $e->errors()
            ]);
            return response()->json(['error' => 'Invalid request data'], 400);
        } catch (\Exception $e) {
            Log::error('Broadcasting authentication error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Authenticate chat channel access
     */
    private function authenticateChatChannel($user, string $channelName, string $socketId): JsonResponse
    {
        // Extract chat ID from channel name (private-chat.{chatId})
        $chatId = str_replace('private-chat.', '', $channelName);

        if (!is_numeric($chatId)) {
            Log::warning('Invalid chat ID in channel name', [
                'channel' => $channelName,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Invalid channel'], 400);
        }

        $chat = Chat::find($chatId);

        if (!$chat) {
            Log::warning('Chat not found for broadcasting auth', [
                'chat_id' => $chatId,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Chat not found'], 404);
        }

        // Check if the user is part of this chat
        $isAuthorized = $chat->users()->where('user_id', $user->id)->exists();

        if (!$isAuthorized) {
            Log::warning('User not authorized for chat channel', [
                'chat_id' => $chatId,
                'user_id' => $user->id
            ]);
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // Generate auth signature and return as raw response
        $authData = $this->generateAuthSignature($channelName, $socketId);

        Log::info('Chat channel authenticated successfully', [
            'chat_id' => $chatId,
            'user_id' => $user->id,
            'channel' => $channelName
        ]);

        // Return the raw auth data as JSON response
        return response()->json($authData);
    }

    /**
     * Authenticate user channel access
     */
    private function authenticateUserChannel($user, string $channelName, string $socketId): JsonResponse
    {
        // Extract user ID from channel name (private-App.Models.User.{userId})
        $userId = str_replace('private-App.Models.User.', '', $channelName);

        if (!is_numeric($userId) || (int) $userId !== $user->id) {
            Log::warning('User channel access denied', [
                'requested_user_id' => $userId,
                'authenticated_user_id' => $user->id,
                'channel' => $channelName
            ]);
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $authData = $this->generateAuthSignature($channelName, $socketId);

        Log::info('User channel authenticated successfully', [
            'user_id' => $user->id,
            'channel' => $channelName
        ]);

        return response()->json($authData);
    }

    /**
     * Authenticate presence channel access
     */
    private function authenticatePresenceChannel($user, string $channelName, string $socketId): JsonResponse
    {
        // Generate user data for presence channel
        $userData = [
            'id' => $user->id,
            'info' => [
                'email' => $user->email,
                'name' => $user->profile->first_name ?? 'User'
            ]
        ];

        $authData = $this->generatePresenceAuthSignature($channelName, $socketId, $userData);

        Log::info('Presence channel authenticated successfully', [
            'user_id' => $user->id,
            'channel' => $channelName
        ]);

        return response()->json($authData);
    }

    /**
     * Generate authentication signature for private channels
     */
    private function generateAuthSignature(string $channelName, string $socketId): array
    {
        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options') ?? []
        );

        // Get the raw auth string and decode it
        $authString = $pusher->socket_auth($channelName, $socketId);

        // The socket_auth method returns a JSON string, so we need to decode it
        return json_decode($authString, true);
    }

    /**
     * Authenticate user chats channel access
     * This channel is used to notify users about new messages across all their chats
     */
    private function authenticateUserChatsChannel($user, string $channelName, string $socketId): JsonResponse
    {
        // Extract user ID from channel name (private-user.{userId})
        $userId = str_replace('private-user.', '', $channelName);

        if (!is_numeric($userId) || (int) $userId !== $user->id) {
            Log::warning('User chats channel access denied', [
                'requested_user_id' => $userId,
                'authenticated_user_id' => $user->id,
                'channel' => $channelName
            ]);
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $authData = $this->generateAuthSignature($channelName, $socketId);

        Log::info('User chats channel authenticated successfully', [
            'user_id' => $user->id,
            'channel' => $channelName
        ]);

        return response()->json($authData);
    }

    /**
     * Generate authentication signature for presence channels
     */
    private function generatePresenceAuthSignature(string $channelName, string $socketId, array $userData): string
    {
        $pusher = new \Pusher\Pusher(
            config('broadcasting.connections.pusher.key'),
            config('broadcasting.connections.pusher.secret'),
            config('broadcasting.connections.pusher.app_id'),
            config('broadcasting.connections.pusher.options') ?? []
        );

        // Get the raw auth data and decode it
        $authData = $pusher->presence_auth($channelName, $socketId, $userData['id'], $userData['info']);

        // The presence_auth method returns an array with 'auth' and 'channel_data'
        return $authData;
    }
}

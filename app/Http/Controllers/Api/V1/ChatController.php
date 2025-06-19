<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="API Endpoints for managing chats and messages between users"
 * )
 */
class ChatController extends Controller
{
    /**
     * Get all chats for the authenticated user
     *
     * @OA\Get(
     *     path="/v1/chats",
     *     summary="Get user chats",
     *     description="Returns all chats for the authenticated user",
     *     operationId="getChats",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="chats",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="user_id_1", type="integer", example=1),
     *                         @OA\Property(property="user_id_2", type="integer", example=2),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(
     *                             property="other_user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="first_name", type="string", example="Jane"),
     *                             @OA\Property(property="last_name", type="string", example="Doe"),
     *                             @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
     *                         ),
     *                         @OA\Property(
     *                             property="last_message",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="chat_id", type="integer", example=1),
     *                             @OA\Property(property="sender_id", type="integer", example=2),
     *                             @OA\Property(property="content", type="string", example="Hello, how are you?"),
     *                             @OA\Property(property="read", type="boolean", example=false),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
     *                         ),
     *                         @OA\Property(property="unread_count", type="integer", example=3)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=10)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getChats(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 10);

            // Get all chats where the user is either user_id_1 or user_id_2
            $chats = Chat::where('user_id_1', $user->id)
                ->orWhere('user_id_2', $user->id)
                ->with(['lastMessage'])
                ->paginate($perPage);

            // Transform the chats to include the other user and unread count
            $chats->getCollection()->transform(function ($chat) use ($user) {
                // Determine the other user in the chat
                $otherUserId = $chat->user_id_1 == $user->id ? $chat->user_id_2 : $chat->user_id_1;
                $otherUser = User::with('profile', 'profilePhoto')->find($otherUserId);

                // Count unread messages
                $unreadCount = Message::where('chat_id', $chat->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('read', false)
                    ->count();

                $chat->other_user = $otherUser;
                $chat->unread_count = $unreadCount;

                return $chat;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chats' => $chats->items(),
                    'pagination' => [
                        'total' => $chats->total(),
                        'per_page' => $chats->perPage(),
                        'current_page' => $chats->currentPage(),
                        'last_page' => $chats->lastPage()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific chat
     *
     * @OA\Get(
     *     path="/v1/chats/{id}",
     *     summary="Get a specific chat",
     *     description="Returns a specific chat with its messages",
     *     operationId="getChat",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for messages",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of messages per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="chat",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id_1", type="integer", example=1),
     *                     @OA\Property(property="user_id_2", type="integer", example=2),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(
     *                         property="other_user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="first_name", type="string", example="Jane"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="profile_photo_url", type="string", example="https://example.com/photos/2.jpg")
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="messages",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="chat_id", type="integer", example=1),
     *                         @OA\Property(property="sender_id", type="integer", example=2),
     *                         @OA\Property(property="content", type="string", example="Hello, how are you?"),
     *                         @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", nullable=true),
     *                         @OA\Property(property="read", type="boolean", example=false),
     *                         @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                         @OA\Property(property="is_mine", type="boolean", example=false)
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="pagination",
     *                     type="object",
     *                     @OA\Property(property="total", type="integer", example=100),
     *                     @OA\Property(property="per_page", type="integer", example=20),
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="last_page", type="integer", example=5)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to view this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function getChat(Request $request, $id)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);

            $chat = Chat::findOrFail($id);

            // Check if the user is part of this chat
            if ($chat->user_id_1 != $user->id && $chat->user_id_2 != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to view this chat'
                ], 403);
            }

            // Determine the other user in the chat
            $otherUserId = $chat->user_id_1 == $user->id ? $chat->user_id_2 : $chat->user_id_1;
            $otherUser = User::with('profile', 'profilePhoto')->find($otherUserId);
            $chat->other_user = $otherUser;

            // Get messages for this chat, ordered by created_at in descending order (newest first)
            $messages = Message::where('chat_id', $chat->id)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Mark unread messages as read if they were sent by the other user
            Message::where('chat_id', $chat->id)
                ->where('sender_id', '!=', $user->id)
                ->where('read', false)
                ->update(['read' => true]);

            // Add is_mine flag to each message
            $messages->getCollection()->transform(function ($message) use ($user) {
                $message->is_mine = $message->sender_id == $user->id;
                return $message;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat' => $chat,
                    'messages' => $messages->items(),
                    'pagination' => [
                        'total' => $messages->total(),
                        'per_page' => $messages->perPage(),
                        'current_page' => $messages->currentPage(),
                        'last_page' => $messages->lastPage()
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a message in a chat
     *
     * @OA\Post(
     *     path="/v1/chats/{id}/messages",
     *     summary="Send a message",
     *     description="Sends a new message in a chat",
     *     operationId="sendMessage",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Hello, how are you?", description="Message content"),
     *             @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", description="URL to attached media (optional)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="chat_id", type="integer", example=1),
     *                     @OA\Property(property="sender_id", type="integer", example=1),
     *                     @OA\Property(property="content", type="string", example="Hello, how are you?"),
     *                     @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", nullable=true),
     *                     @OA\Property(property="read", type="boolean", example=false),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="is_mine", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Message content is required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to send messages in this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => ['required_without:media_url', 'string', 'nullable'],
            'media_url' => ['required_without:content', 'string', 'nullable']
        ]);

        try {
            $user = $request->user();
            $chat = Chat::findOrFail($id);

            // Check if the user is part of this chat
            if ($chat->user_id_1 != $user->id && $chat->user_id_2 != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to send messages in this chat'
                ], 403);
            }

            // Create the message
            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'content' => $validated['content'] ?? null,
                'media_url' => $validated['media_url'] ?? null,
                'read' => false
            ]);

            // Update the chat's updated_at timestamp
            $chat->touch();

            // Add is_mine flag to the message
            $message->is_mine = true;

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'message' => $message
                ]
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark messages as read
     *
     * @OA\Post(
     *     path="/v1/chats/{id}/read",
     *     summary="Mark messages as read",
     *     description="Marks all unread messages in a chat as read",
     *     operationId="markMessagesAsRead",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Messages marked as read successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Messages marked as read successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="count", type="integer", example=5, description="Number of messages marked as read")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to access this chat")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Server error"),
     *             @OA\Property(property="error", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function markMessagesAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            $chat = Chat::findOrFail($id);

            // Check if the user is part of this chat
            if ($chat->user_id_1 != $user->id && $chat->user_id_2 != $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to access this chat'
                ], 403);
            }

            // Mark all unread messages from the other user as read
            $count = Message::where('chat_id', $chat->id)
                ->where('sender_id', '!=', $user->id)
                ->where('read', false)
                ->update(['read' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Messages marked as read successfully',
                'data' => [
                    'count' => $count
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to mark messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageReadEvent;
use App\Events\NewMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            // Optimized query with better performance
            $chats = $user->chats()
                ->with([
                    'lastMessage.sender:id,email',
                    'users' => function($query) use ($user) {
                        $query->where('users.id', '!=', $user->id)
                              ->with(['profile:id,user_id,first_name,last_name,bio',
                                     'profilePhoto:id,user_id,url,is_profile_photo']);
                    }
                ])
                ->withCount(['messages as unread_count' => function($query) use ($user) {
                    $query->unreadByUser($user);
                }])
                ->orderBy('last_activity_at', 'desc')
                ->paginate($perPage);

            // Transform the chats to include the other user
            $chats->getCollection()->transform(function ($chat) use ($user) {
                // Get the other user from the eager loaded relationship
                $otherUser = $chat->users->first();
                $chat->other_user = $otherUser;

                // Remove the users collection to clean up the response
                unset($chat->users);

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
            $beforeMessageId = $request->input('before_message_id'); // For loading older messages

            // Find the chat through the user's relationship to ensure authorization
            $chat = $user->chats()
                ->with(['users' => function($query) use ($user) {
                    // Eager load other users in the chat with their profiles and photos
                    $query->where('users.id', '!=', $user->id)
                          ->with(['profile', 'profilePhoto']);
                }])
                ->findOrFail($id);

            // Get the other user from the eager loaded relationship
            $otherUser = $chat->users->first();
            $chat->other_user = $otherUser;

            // Remove the user's collection to clean up the response
            unset($chat->users);

            // Get messages with optimized query
            $messages = Message::inChat($chat->id)
                ->with([
                    'sender:id,email',
                    'replyTo:id,content,sender_id',
                    'replyTo.sender:id,email'
                ])
                ->recent()
                ->paginate($perPage);

            // Get unread message IDs efficiently
            $unreadMessageIds = MessageRead::getUnreadMessageIds($chat->id, $user->id);

            // Mark messages as read efficiently
            if (!empty($unreadMessageIds)) {
                MessageRead::markMessagesAsRead($unreadMessageIds, $user->id);

                // Update user's last_read_at in chat_users pivot
                $user->chats()->updateExistingPivot($chat->id, [
                    'last_read_at' => now()
                ]);
            }

            // Transform messages with read status
            $messages->getCollection()->transform(function ($message) use ($user) {
                $readStatus = $message->getReadStatusFor($user);
                $message->is_mine = $readStatus['is_mine'];
                $message->is_read = $readStatus['is_read'];
                $message->read_at = $readStatus['read_at'];

                return $message;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat' => $chat,
                    'messages' => $messages->toArray(),
                    'pagination' => [
                        'total' => $totalMessages,
                        'loaded' => $messages->count(),
                        'has_more' => $hasMoreMessages,
                        'oldest_message_id' => $messages->isEmpty() ? null : $messages->first()->id
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
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="content", type="string", example="Hello, how are you?", description="Message content"),
     *                 @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", description="URL to attached media (optional)"),
     *                 @OA\Property(property="media_file", type="file", description="Media file to upload (image, video, audio, or other file)"),
     *                 @OA\Property(property="message_type", type="string", enum={"text", "image", "video", "audio", "file", "location"}, description="Type of message (optional, will be auto-detected for uploads)"),
     *                 @OA\Property(property="media_data", type="object", description="Additional metadata for the media (optional)"),
     *                 @OA\Property(property="reply_to_message_id", type="integer", description="ID of the message being replied to (optional)")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Hello, how are you?", description="Message content"),
     *             @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", description="URL to attached media (optional)"),
     *             @OA\Property(property="message_type", type="string", enum={"text", "image", "video", "audio", "file", "location"}, description="Type of message"),
     *             @OA\Property(property="media_data", type="object", description="Additional metadata for the media (optional)"),
     *             @OA\Property(property="reply_to_message_id", type="integer", description="ID of the message being replied to (optional)")
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
        // Handle media_data if it's sent as JSON string
        if ($request->has('media_data') && is_string($request->input('media_data'))) {
            $decodedMediaData = json_decode($request->input('media_data'), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge(['media_data' => $decodedMediaData]);
            }
        }

        $validated = $request->validate([
            'content' => ['required_without_all:media_url,media_file', 'string', 'nullable'],
            'media_url' => ['required_without_all:content,media_file', 'string', 'nullable'],
            'media_file' => ['required_without_all:content,media_url', 'file', 'nullable', 'max:100000'], // 100MB max file size
            'message_type' => ['string', 'in:text,image,video,audio,voice,file,location'],
            'media_data' => ['array', 'nullable'],
            'reply_to_message_id' => ['integer', 'exists:messages,id', 'nullable']
        ]);

        try {
            $user = $request->user();

            // Find chat and verify user access through relationship
            $chat = $user->chats()->findOrFail($id);

            // Check if chat is active
            if (!$chat->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This chat is no longer active'
                ], 403);
            }

            // Handle file upload if present
            $mediaUrl = $validated['media_url'] ?? null;
            $messageType = $validated['message_type'] ?? 'text';

            if ($request->hasFile('media_file')) {
                try {
                    $file = $request->file('media_file');

                    // Check if file is valid
                    if (!$file->isValid()) {
                        throw new \Exception('Invalid file upload');
                    }

                    $mimeType = $file->getMimeType();
                    $extension = $file->getClientOriginalExtension();

                    // Determine message type based on mime type if not provided
                    if (!isset($validated['message_type'])) {
                        if (strpos($mimeType, 'image/') === 0) {
                            $messageType = 'image';
                        } elseif (strpos($mimeType, 'video/') === 0) {
                            $messageType = 'video';
                        } elseif (strpos($mimeType, 'audio/') === 0) {
                            // Check if it's specifically a voice message based on media_data
                            $mediaData = $validated['media_data'] ?? [];
                            if (isset($mediaData['duration']) && $mediaData['duration'] <= 300) { // Voice messages typically under 5 minutes
                                $messageType = 'voice';
                            } else {
                                $messageType = 'audio';
                            }
                        } else {
                            $messageType = 'file';
                        }
                    } else {
                        // If message_type is explicitly set to 'voice', ensure it's an audio file
                        if ($validated['message_type'] === 'voice' && strpos($mimeType, 'audio/') !== 0) {
                            throw new \Exception('Voice messages must be audio files');
                        }
                    }

                    // Generate a unique filename
                    $filename = uniqid('chat_' . $chat->id . '_', true) . '.' . $extension;

                    // Define the path based on message type
                    $folderName = $messageType === 'voice' ? 'voices' : $messageType . 's';
                    $path = 'chats/' . $chat->id . '/' . $folderName . '/' . $filename;

                    // Log file info before upload
                    \Log::debug('Attempting S3 upload', [
                        'chat_id' => $chat->id,
                        'filename' => $filename,
                        'path' => $path,
                        'mime_type' => $mimeType,
                        'size' => $file->getSize(),
                        'is_valid' => $file->isValid(),
                        'original_name' => $file->getClientOriginalName(),
                    ]);

                    $store = false;
                    try {
                        $stream = fopen($file->getRealPath(), 'r');

                        if ($stream === false) {
                            throw new \Exception('Could not open file for upload');
                        }

                        $store = Storage::disk('s3')->put($path, $stream);
                        \Log::debug('S3 upload result', [
                            'path' => $path,
                            'store' => $store
                        ]);

                        if (is_resource($stream)) {
                            fclose($stream);
                        }

                        // Get the full URL for the file
                        $mediaUrl = Storage::disk('s3')->url($path);

                        // Store additional media data
                        $mediaData = $validated['media_data'] ?? [];
                        $mediaData['original_filename'] = $file->getClientOriginalName();
                        $mediaData['size'] = $file->getSize();
                        $mediaData['mime_type'] = $mimeType;

                        // For voice messages, ensure duration is stored
                        if ($messageType === 'voice') {
                            // If duration wasn't provided in the request, you might want to extract it
                            // from the audio file using a library like getID3 or FFmpeg
                            if (!isset($mediaData['duration'])) {
                                $mediaData['duration'] = $this->extractAudioDuration($file->getRealPath());
                            }

                            // Add voice-specific metadata
                            $mediaData['is_voice_message'] = true;

                            // Set a default content for voice messages if none provided
                            if (empty($validated['content'])) {
                                $validated['content'] = 'Voice Message';
                            }
                        }

                        $validated['media_data'] = $mediaData;

                    } catch (\Exception $e) {
                        \Log::error('S3 upload exception', [
                            'path' => $path,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                } catch (\Exception $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to upload media file',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }

            // Create the message with proper fields
            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'content' => $validated['content'] ?? null,
                'media_url' => $validated['media_url'] ?? null,
                'message_type' => $this->determineMessageType($validated),
                'sent_at' => now()
            ]);

            // Update chat activity
            $chat->updateLastActivity();

            // Transform message for response
            $readStatus = $message->getReadStatusFor($user);
            $message->is_mine = $readStatus['is_mine'];
            $message->is_read = $readStatus['is_read'];

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
     * Extract audio duration from file (helper method)
     * You might want to use a library like getID3 or FFmpeg for more accurate results
     */
    private function extractAudioDuration($filePath)
    {
        // Basic duration extraction - you might want to use a more robust solution
        // For now, return null if we can't determine duration
        try {
            // This is a placeholder - implement actual duration extraction
            // You could use getID3 library or FFmpeg
            return null;
        } catch (\Exception $e) {
            return null;
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

            // Find chat and verify user access through relationship
            $chat = $user->chats()->findOrFail($id);

            // Get unread message IDs efficiently
            $unreadMessageIds = MessageRead::getUnreadMessageIds($chat->id, $user->id);

            // Mark messages as read
            $count = 0;
            if (!empty($unreadMessageIds)) {
                $count = MessageRead::markMessagesAsRead($unreadMessageIds, $user->id);

                // Update user's last_read_at in chat_users pivot
                $user->chats()->updateExistingPivot($chat->id, [
                    'last_read_at' => now()
                ]);
            }

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

    /**
     * Helper method to determine message type
     */
    private function determineMessageType(array $validated): string
    {
        if (!empty($validated['media_url'])) {
            $extension = strtolower(pathinfo($validated['media_url'], PATHINFO_EXTENSION));

            $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $videoExtensions = ['mp4', 'avi', 'mov', 'webm'];
            $audioExtensions = ['mp3', 'wav', 'aac', 'm4a'];

            if (in_array($extension, $imageExtensions)) {
                return 'image';
            } elseif (in_array($extension, $videoExtensions)) {
                return 'video';
            } elseif (in_array($extension, $audioExtensions)) {
                return 'audio';
            } else {
                return 'file';
            }
        }

        return 'text';
    }

    /**
     * Create or get existing chat with another user
     *
     * @OA\Post(
     *     path="/v1/chats/create",
     *     summary="Create or get chat",
     *     description="Creates a new chat with another user or returns existing chat",
     *     operationId="createOrGetChat",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=2, description="ID of the user to chat with")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Chat created or retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Chat ready"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="chat",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="private"),
     *                     @OA\Property(property="is_new", type="boolean", example=true),
     *                     @OA\Property(
     *                         property="other_user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="first_name", type="string", example="Jane"),
     *                         @OA\Property(property="last_name", type="string", example="Doe")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function createOrGetChat(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        try {
            $user = $request->user();
            $otherUserId = $validated['user_id'];

            // Can't chat with yourself
            if ($user->id === $otherUserId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot create chat with yourself'
                ], 400);
            }

            // Check if chat already exists
            $existingChat = $user->getChatWith(User::find($otherUserId));

            if ($existingChat) {
                $existingChat->load(['users' => function($query) use ($user) {
                    $query->where('users.id', '!=', $user->id)
                          ->with(['profile:id,user_id,first_name,last_name']);
                }]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Chat retrieved',
                    'data' => [
                        'chat' => [
                            'id' => $existingChat->id,
                            'type' => $existingChat->type,
                            'is_new' => false,
                            'other_user' => $existingChat->users->first()
                        ]
                    ]
                ]);
            }

            // Create new chat
            $chat = DB::transaction(function() use ($user, $otherUserId) {
                $chat = Chat::create([
                    'type' => 'private',
                    'is_active' => true,
                    'last_activity_at' => now()
                ]);

                // Add both users to the chat
                $chat->users()->attach([
                    $user->id => [
                        'joined_at' => now(),
                        'role' => 'member'
                    ],
                    $otherUserId => [
                        'joined_at' => now(),
                        'role' => 'member'
                    ]
                ]);

                return $chat;
            });

            // Load other user info
            $chat->load(['users' => function($query) use ($user) {
                $query->where('users.id', '!=', $user->id)
                      ->with(['profile:id,user_id,first_name,last_name']);
            }]);

            return response()->json([
                'status' => 'success',
                'message' => 'Chat created',
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'type' => $chat->type,
                        'is_new' => true,
                        'other_user' => $chat->users->first()
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get unread messages count for all chats
     *
     * @OA\Get(
     *     path="/v1/chats/unread-count",
     *     summary="Get total unread count",
     *     description="Returns the total number of unread messages across all chats",
     *     operationId="getUnreadCount",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="total_unread", type="integer", example=15),
     *                 @OA\Property(
     *                     property="chats",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="chat_id", type="integer", example=1),
     *                         @OA\Property(property="unread_count", type="integer", example=5)
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();

            // Get unread counts per chat efficiently
            $unreadCounts = DB::table('messages')
                ->join('chats', 'messages.chat_id', '=', 'chats.id')
                ->join('chat_users', function($join) use ($user) {
                    $join->on('chats.id', '=', 'chat_users.chat_id')
                         ->where('chat_users.user_id', '=', $user->id)
                         ->whereNull('chat_users.left_at');
                })
                ->leftJoin('message_reads', function($join) use ($user) {
                    $join->on('messages.id', '=', 'message_reads.message_id')
                         ->where('message_reads.user_id', '=', $user->id);
                })
                ->where('messages.sender_id', '!=', $user->id)
                ->whereNull('message_reads.id')
                ->whereNull('messages.deleted_at')
                ->groupBy('messages.chat_id')
                ->get(['messages.chat_id as chat_id', DB::raw('COUNT(*) as unread_count')])
                ->keyBy('chat_id');

            $totalUnread = $unreadCounts->sum('unread_count');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_unread' => $totalUnread,
                    'chats' => $unreadCounts->values()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get unread count',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a chat
     *
     * @OA\Delete(
     *     path="/v1/chats/{id}",
     *     summary="Delete a chat",
     *     description="Removes the user from the chat (soft delete for the user)",
     *     operationId="deleteChat",
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
     *         description="Chat deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Chat deleted successfully")
     *         )
     *     )
     * )
     */
    public function deleteChat(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Find chat and verify user access
            $chat = $user->chats()->findOrFail($id);

            // Update pivot to mark user as left
            $user->chats()->updateExistingPivot($chat->id, [
                'left_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Chat deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Chat not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

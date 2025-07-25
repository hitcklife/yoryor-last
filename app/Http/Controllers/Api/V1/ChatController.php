<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageDeletedEvent;
use App\Events\MessageEditedEvent;
use App\Events\MessageReadEvent;
use App\Events\NewMessageEvent;
use App\Events\UnreadCountUpdateEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="API Endpoints for managing chats and messages between users"
 * )
 */
class ChatController extends Controller
{
    protected $mediaUploadService;

    public function __construct(MediaUploadService $mediaUploadService)
    {
        $this->mediaUploadService = $mediaUploadService;
    }

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
                                     'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo']);
                    }
                ])
                ->withCount(['messages as unread_count' => function($query) use ($user) {
                    $query->unreadByUser($user);
                }])
                ->orderBy('last_activity_at', 'desc')
                ->paginate($perPage);

            // Transform the chats to include the other user and add read status to last message
            $chats->getCollection()->transform(function ($chat) use ($user) {
                // Get the other user from the eager loaded relationship
                $otherUser = $chat->users->first();
                $chat->other_user = $otherUser;

                // Add read status to last message if it exists
                if ($chat->lastMessage) {
                    $readStatus = $chat->lastMessage->getReadStatusFor($user);
                    $chat->lastMessage->is_mine = $readStatus['is_mine'];
                    $chat->lastMessage->is_read = $readStatus['is_read'];
                    $chat->lastMessage->read_at = $readStatus['read_at'];
                }

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
     * Get a specific chat with messages
     *
     * @OA\Get(
     *     path="/v1/chats/{id}",
     *     summary="Get a specific chat",
     *     description="Retrieves a specific chat with its messages and participants",
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
     *         description="Page number for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of messages per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=100)
     *     ),
     *     @OA\Parameter(
     *         name="include_call_data",
     *         in="query",
     *         description="Include detailed call data for call messages",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Chat retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="chat", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="participants", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="messages", type="object",
     *                         @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                         @OA\Property(property="pagination", type="object")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Chat not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Chat not found")
     *         )
     *     )
     * )
     */
    public function getChat(Request $request, int $id)
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'include_call_data' => 'sometimes|boolean'
        ]);

        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);
            $includeCallData = $request->boolean('include_call_data', true);

            // Find chat and verify user access with correct relationships
            $chat = $user->chats()->with(['users' => function($query) use ($user) {
                $query->where('users.id', '!=', $user->id)
                      ->with(['profile:id,user_id,first_name,last_name', 'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo']);
            }])->findOrFail($id);

            // Get the other user from the chat
            $otherUser = $chat->users->first();
            $chat->other_user = $otherUser;

            // Clean up the response by removing the users collection
            unset($chat->users);

            // Build messages query with optimized loading
            $messagesQuery = $chat->messages()
                ->with([
                    'sender:id,email',
                    'sender.profile:id,user_id,first_name,last_name',
                    'sender.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                    'replyTo:id,content,sender_id',
                    'replyTo.sender:id,email',
                    'replyTo.sender.profile:id,user_id,first_name,last_name',
                    'replyTo.sender.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo'
                ])
                ->orderBy('sent_at', 'desc');

            // Include call data if requested
            if ($includeCallData) {
                $messagesQuery->with(['call:id,type,status,started_at,ended_at,caller_id,receiver_id']);
            }

            // Get paginated messages
            $messages = $messagesQuery->paginate($perPage);

            // Transform messages with read status and enhanced call data
            $transformedMessages = $messages->getCollection()->map(function ($message) use ($user, $includeCallData) {
                $readStatus = $message->getReadStatusFor($user);
                $messageData = [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'message_type' => $message->message_type,
                    'media_url' => $message->media_url,
                    'thumbnail_url' => $message->thumbnail_url,
                    'media_data' => $message->media_data,
                    'reply_to_message_id' => $message->reply_to_message_id,
                    'is_edited' => $message->is_edited,
                    'edited_at' => $message->edited_at,
                    'sent_at' => $message->sent_at,
                    'is_read' => $readStatus['is_read'],
                    'read_at' => $readStatus['read_at'],
                    'is_mine' => $readStatus['is_mine'],
                    'sender' => $message->sender,
                    'reply_to' => $message->replyTo
                ];

                // Add enhanced call data if it's a call message
                if ($message->isCallMessage() && $includeCallData && $message->call) {
                    $messageData['call_details'] = [
                        'call_id' => $message->call->id,
                        'type' => $message->call->type,
                        'status' => $message->call->status,
                        'duration_seconds' => $message->call->getDurationInSeconds(),
                        'formatted_duration' => $message->call->getFormattedDuration(),
                        'started_at' => $message->call->started_at,
                        'ended_at' => $message->call->ended_at,
                        'is_active' => $message->call->isActive(),
                        'other_participant' => $message->call->getOtherParticipant($user)
                    ];
                }

                return $messageData;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat' => [
                        'id' => $chat->id,
                        'type' => $chat->type,
                        'name' => $chat->name,
                        'is_active' => $chat->is_active,
                        'created_at' => $chat->created_at,
                        'updated_at' => $chat->updated_at,
                        'last_activity_at' => $chat->last_activity_at,
                        'other_user' => $chat->other_user
                    ],
                    'messages' => [
                        'data' => $transformedMessages,
                        'pagination' => [
                            'current_page' => $messages->currentPage(),
                            'last_page' => $messages->lastPage(),
                            'per_page' => $messages->perPage(),
                            'total' => $messages->total(),
                            'from' => $messages->firstItem(),
                            'to' => $messages->lastItem()
                        ]
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
     * Examples for call messages:
     * - Missed call: {"message_type": "call", "media_data": {"call_type": "video", "call_status": "missed", "ended_reason": "no_answer"}}
     * - Completed call: {"message_type": "call", "media_data": {"call_type": "audio", "call_status": "completed", "duration": 120}}
     * - Outgoing call: {"message_type": "call", "media_data": {"call_type": "video", "call_status": "outgoing", "duration": 0}}
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
     *                 @OA\Property(property="message_type", type="string", enum={"text", "image", "video", "audio", "voice", "file", "location", "call"}, description="Type of message (optional, will be auto-detected for uploads)"),
     *                 @OA\Property(
     *                     property="media_data",
     *                     type="object",
     *                     description="Additional metadata for the media (optional). For call messages, use call data structure",
     *                     @OA\Property(property="call_type", type="string", enum={"video", "audio"}, description="Type of call (required for call messages)"),
     *                     @OA\Property(property="call_status", type="string", enum={"missed", "incoming", "outgoing", "completed"}, description="Status of the call (required for call messages)"),
     *                     @OA\Property(property="duration", type="integer", example=120, description="Call duration in seconds (optional, for completed calls)"),
     *                     @OA\Property(property="ended_reason", type="string", enum={"declined", "busy", "no_answer", "completed", "network_error", "cancelled"}, description="Reason why call ended (optional)")
     *                 ),
     *                 @OA\Property(property="reply_to_message_id", type="integer", description="ID of the message being replied to (optional)")
     *             )
     *         ),
     *         @OA\JsonContent(
     *             @OA\Property(property="content", type="string", example="Hello, how are you?", description="Message content (optional for call messages)"),
     *             @OA\Property(property="media_url", type="string", example="https://example.com/media/1.jpg", description="URL to attached media (optional)"),
     *             @OA\Property(property="message_type", type="string", enum={"text", "image", "video", "audio", "voice", "file", "location", "call"}, description="Type of message"),
     *             @OA\Property(
     *                 property="media_data",
     *                 type="object",
     *                 description="Additional metadata for the media (optional). For call messages, use call data structure",
     *                 @OA\Property(property="call_type", type="string", enum={"video", "audio"}, description="Type of call (required for call messages)"),
     *                 @OA\Property(property="call_status", type="string", enum={"missed", "incoming", "outgoing", "completed"}, description="Status of the call (required for call messages)"),
     *                 @OA\Property(property="duration", type="integer", example=120, description="Call duration in seconds (optional, for completed calls)"),
     *                 @OA\Property(property="ended_reason", type="string", enum={"declined", "busy", "no_answer", "completed", "network_error", "cancelled"}, description="Reason why call ended (optional)")
     *             ),
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
     *                     @OA\Property(property="message_type", type="string", example="text"),
     *                     @OA\Property(property="media_data", type="object", example={"call_type": "video", "call_status": "completed", "duration": 120}, nullable=true),
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
            'content' => ['required_without_all:media_url,media_file,message_type', 'string', 'nullable'],
            'media_url' => ['required_without_all:content,media_file', 'string', 'nullable'],
            'media_file' => ['required_without_all:content,media_url', 'file', 'nullable', 'max:100000'], // 100MB max file size
            'message_type' => ['string', 'in:text,image,video,audio,voice,file,location,call'],
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
            $mediaData = $validated['media_data'] ?? [];

            // Handle call message type
            if ($messageType === 'call') {
                // Validate call-specific data
                $callData = $validated['media_data'] ?? [];

                // Validate call data structure
                $callValidation = validator($callData, [
                    'call_type' => ['required', 'string', 'in:video,audio'],
                    'call_status' => ['required', 'string', 'in:missed,incoming,outgoing,completed'],
                    'duration' => ['nullable', 'integer', 'min:0'],
                    'ended_reason' => ['nullable', 'string', 'in:declined,busy,no_answer,completed,network_error,cancelled']
                ]);

                if ($callValidation->fails()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid call data',
                        'errors' => $callValidation->errors()
                    ], 400);
                }

                // Set default content for call messages based on call data
                if (empty($validated['content'])) {
                    $callType = ucfirst($callData['call_type']);
                    $callStatus = $callData['call_status'];

                    switch ($callStatus) {
                        case 'missed':
                            $validated['content'] = "Missed {$callType} Call";
                            break;
                        case 'incoming':
                            $validated['content'] = "Incoming {$callType} Call";
                            break;
                        case 'outgoing':
                            $validated['content'] = "Outgoing {$callType} Call";
                            break;
                        case 'completed':
                            $duration = $callData['duration'] ?? 0;
                            $durationText = $this->formatCallDuration($duration);
                            $validated['content'] = "{$callType} Call - {$durationText}";
                            break;
                        default:
                            $validated['content'] = "{$callType} Call";
                    }
                }
            }

            if ($request->hasFile('media_file')) {
                try {
                    $file = $request->file('media_file');

                    // Check if file is valid
                    if (!$file->isValid()) {
                        throw new \Exception('Invalid file upload');
                    }

                    // Determine message type based on mime type if not provided
                    if (!isset($validated['message_type'])) {
                        $mimeType = $file->getMimeType();
                        if (strpos($mimeType, 'image/') === 0) {
                            $messageType = 'image';
                        } elseif (strpos($mimeType, 'video/') === 0) {
                            $messageType = 'video';
                        } elseif (strpos($mimeType, 'audio/') === 0) {
                            // Check if it's specifically a voice message based on media_data
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
                        if ($validated['message_type'] === 'voice' && strpos($file->getMimeType(), 'audio/') !== 0) {
                            throw new \Exception('Voice messages must be audio files');
                        }
                    }

                    // Use MediaUploadService to upload file
                    $uploadOptions = [
                        'chat_id' => $chat->id,
                        'message_type' => $messageType
                    ];

                    // Add voice-specific options
                    if ($messageType === 'voice') {
                        $uploadOptions['is_voice_message'] = true;
                        $uploadOptions['duration'] = $mediaData['duration'] ?? null;

                        // Set a default content for voice messages if none provided
                        if (empty($validated['content'])) {
                            $validated['content'] = 'Voice Message';
                        }
                    }

                    // Uplowad using MediaUploadService
                    $uploadResult = $this->mediaUploadService->uploadMedia(
                        $file,
                        'chat',
                        $user->id,
                        $uploadOptions
                    );

                    $mediaUrl = $uploadResult['original_url'];
                    $mediaData = array_merge($mediaData, $uploadResult['metadata']);

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
                'media_url' => $mediaUrl,
                'message_type' => $messageType,
                'media_data' => $mediaData,
                'reply_to_message_id' => $validated['reply_to_message_id'] ?? null,
                'sent_at' => now()
            ]);

            // Update chat activity
            $chat->updateLastActivity();

            // Broadcast real-time event
            broadcast(new NewMessageEvent($message))->toOthers();

            // Broadcast unread count updates to other users in the chat
            $otherUsers = $chat->users()->where('user_id', '!=', $user->id)->get();
            foreach ($otherUsers as $otherUser) {
                $totalUnreadCount = $otherUser->getUnreadMessagesCount();
                $chatUnreadCount = Message::getUnreadCountForUserInChat($otherUser, $chat->id);

                broadcast(new UnreadCountUpdateEvent(
                    $otherUser,
                    $totalUnreadCount,
                    $chat->id,
                    $chatUnreadCount
                ))->toOthers();
            }

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
    public function markMessagesAsRead(Request $request, $id, $message = null)
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

                // Broadcast read event
                broadcast(new MessageReadEvent($chat, $user, $count))->toOthers();

                // Broadcast updated unread count
                $totalUnreadCount = $user->getUnreadMessagesCount();
                $chatUnreadCount = Message::getUnreadCountForUserInChat($user, $chat->id);

                broadcast(new UnreadCountUpdateEvent(
                    $user,
                    $totalUnreadCount,
                    $chat->id,
                    $chatUnreadCount
                ))->toOthers();
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
     * Helper method to format call duration
     */
    private function formatCallDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        $formatted = "{$hours}h";
        if ($remainingMinutes > 0) {
            $formatted .= " {$remainingMinutes}m";
        }
        if ($remainingSeconds > 0) {
            $formatted .= " {$remainingSeconds}s";
        }

        return $formatted;
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
     * Edit a message
     *
     * @OA\Put(
     *     path="/v1/chats/{chat_id}/messages/{message_id}",
     *     summary="Edit a message",
     *     description="Edits a text message (only text messages can be edited)",
     *     operationId="editMessage",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chat_id",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="message_id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Updated message content", description="New message content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message edited successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message edited successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="message",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="chat_id", type="integer", example=1),
     *                     @OA\Property(property="sender_id", type="integer", example=1),
     *                     @OA\Property(property="content", type="string", example="Updated message content"),
     *                     @OA\Property(property="message_type", type="string", example="text"),
     *                     @OA\Property(property="is_edited", type="boolean", example=true),
     *                     @OA\Property(property="edited_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
     *                     @OA\Property(property="sent_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
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
     *             @OA\Property(property="message", type="string", example="Only text messages can be edited")
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
     *             @OA\Property(property="message", type="string", example="You can only edit your own messages")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Message not found")
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
    public function editMessage(Request $request, $chatId, $messageId)
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:5000'] // Limit message length
        ]);

        try {
            $user = $request->user();

            // Find the message and verify it belongs to the user's chat
            $message = Message::where('id', $messageId)
                ->where('chat_id', $chatId)
                ->whereHas('chat.users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            // Check if user can edit this message (only their own messages)
            if ($message->sender_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only edit your own messages'
                ], 403);
            }

            // Only text messages can be edited
            if ($message->message_type !== 'text') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only text messages can be edited'
                ], 400);
            }

            // Store original content for event
            $originalContent = $message->content;

            // Update the message
            $message->update([
                'content' => $validated['content'],
                'is_edited' => true,
                'edited_at' => now()
            ]);

            // Load relationships for response
            $message->load('sender:id,email');

            // Transform message for response
            $readStatus = $message->getReadStatusFor($user);
            $message->is_mine = $readStatus['is_mine'];
            $message->is_read = $readStatus['is_read'];

            // Broadcast the edit event
            broadcast(new MessageEditedEvent($message, $originalContent))->toOthers();

            return response()->json([
                'status' => 'success',
                'message' => 'Message edited successfully',
                'data' => [
                    'message' => $message
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to edit message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a message
     *
     * @OA\Delete(
     *     path="/v1/chats/{chat_id}/messages/{message_id}",
     *     summary="Delete a message",
     *     description="Deletes a message (soft delete)",
     *     operationId="deleteMessage",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="chat_id",
     *         in="path",
     *         description="Chat ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="message_id",
     *         in="path",
     *         description="Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message deleted successfully")
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
     *             @OA\Property(property="message", type="string", example="You can only delete your own messages")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Message not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Message not found")
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
    public function deleteMessage(Request $request, $chatId, $messageId)
    {
        try {
            $user = $request->user();

            // Find the message and verify it belongs to the user's chat
            $message = Message::where('id', $messageId)
                ->where('chat_id', $chatId)
                ->whereHas('chat.users', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->firstOrFail();

            // Check if user can delete this message (only their own messages)
            if ($message->sender_id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You can only delete your own messages'
                ], 403);
            }

            // Store message info before deletion for event
            $messageId = $message->id;
            $chatId = $message->chat_id;

            // Soft delete the message
            $message->delete();

            // Broadcast the delete event
            broadcast(new MessageDeletedEvent($messageId, $chatId, $user->id))->toOthers();

            return response()->json([
                'status' => 'success',
                'message' => 'Message deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
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

    /**
     * Get call messages for a specific chat
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getCallMessages(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'call_status' => 'sometimes|string|in:completed,rejected,missed,ongoing',
            'call_type' => 'sometimes|string|in:video,voice'
        ]);

        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);

            // Find chat and verify user access
            $chat = $user->chats()->findOrFail($id);

            // Build call messages query
            $callMessagesQuery = $chat->messages()
                ->callMessages()
                ->with([
                    'sender:id,email',
                    'sender.profile:id,user_id,first_name,last_name',
                    'sender.profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo',
                    'call:id,type,status,started_at,ended_at,caller_id,receiver_id'
                ])
                ->orderBy('sent_at', 'desc');

            // Apply filters
            if ($request->has('call_status')) {
                $callMessagesQuery->whereJsonContains('media_data->call_status', $request->call_status);
            }

            if ($request->has('call_type')) {
                $callMessagesQuery->whereJsonContains('media_data->call_type', $request->call_type);
            }

            // Get paginated call messages
            $callMessages = $callMessagesQuery->paginate($perPage);

            // Transform messages with enhanced call data
            $transformedCallMessages = $callMessages->getCollection()->map(function ($message) use ($user) {
                $readStatus = $message->getReadStatusFor($user);

                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'content' => $message->content,
                    'message_type' => $message->message_type,
                    'media_data' => $message->media_data,
                    'sent_at' => $message->sent_at,
                    'is_read' => $readStatus['is_read'],
                    'read_at' => $readStatus['read_at'],
                    'is_mine' => $readStatus['is_mine'],
                    'sender' => $message->sender,
                    'call_details' => $message->call ? [
                        'call_id' => $message->call->id,
                        'type' => $message->call->type,
                        'status' => $message->call->status,
                        'duration_seconds' => $message->call->getDurationInSeconds(),
                        'formatted_duration' => $message->call->getFormattedDuration(),
                        'started_at' => $message->call->started_at,
                        'ended_at' => $message->call->ended_at,
                        'is_active' => $message->call->isActive(),
                        'other_participant' => $message->call->getOtherParticipant($user)
                    ] : null
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'call_messages' => $transformedCallMessages,
                    'pagination' => [
                        'current_page' => $callMessages->currentPage(),
                        'last_page' => $callMessages->lastPage(),
                        'per_page' => $callMessages->perPage(),
                        'total' => $callMessages->total(),
                        'from' => $callMessages->firstItem(),
                        'to' => $callMessages->lastItem()
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
                'message' => 'Failed to get call messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get call statistics for a specific chat
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function getCallStatistics(Request $request, int $id): JsonResponse
    {
        try {
            $user = $request->user();

            // Find chat and verify user access
            $chat = $user->chats()->findOrFail($id);

            // Get call messages for this chat
            $callMessages = $chat->messages()
                ->callMessages()
                ->with('call')
                ->get();

            $totalCalls = $callMessages->count();
            $completedCalls = $callMessages->filter(function ($message) {
                return $message->getCallData()['call_status'] === 'completed';
            })->count();

            $missedCalls = $callMessages->filter(function ($message) {
                return $message->getCallData()['call_status'] === 'missed';
            })->count();

            $totalDuration = $callMessages->sum(function ($message) {
                return $message->getCallDuration() ?? 0;
            });

            $videoCalls = $callMessages->filter(function ($message) {
                return $message->getCallData()['call_type'] === 'video';
            })->count();

            $voiceCalls = $callMessages->filter(function ($message) {
                return $message->getCallData()['call_type'] === 'voice';
            })->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_calls' => $totalCalls,
                    'completed_calls' => $completedCalls,
                    'missed_calls' => $missedCalls,
                    'video_calls' => $videoCalls,
                    'voice_calls' => $voiceCalls,
                    'success_rate' => $totalCalls > 0 ? round(($completedCalls / $totalCalls) * 100, 2) : 0,
                    'total_duration_seconds' => $totalDuration,
                    'average_duration_seconds' => $completedCalls > 0 ? round($totalDuration / $completedCalls, 2) : 0,
                    'formatted_total_duration' => $this->formatCallDuration($totalDuration),
                    'formatted_average_duration' => $completedCalls > 0 ? $this->formatCallDuration(round($totalDuration / $completedCalls)) : '0s'
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
                'message' => 'Failed to get call statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

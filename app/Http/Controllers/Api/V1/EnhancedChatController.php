<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageRead;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Enhanced Chat",
 *     description="Enhanced API Endpoints for managing chats with activity tracking and real-time features"
 * )
 */
class EnhancedChatController extends Controller
{
    /**
     * Send message with activity tracking and enhanced features
     * 
     * @OA\Post(
     *     path="/v1/chats/{id}/messages/enhanced",
     *     summary="Send enhanced message",
     *     description="Sends a message with activity tracking and real-time broadcasting",
     *     operationId="sendEnhancedMessage",
     *     tags={"Enhanced Chat"},
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
     *             @OA\Property(property="content", type="string", example="Hello!", description="Message content"),
     *             @OA\Property(property="media_url", type="string", example="https://example.com/image.jpg", description="Media URL"),
     *             @OA\Property(property="reply_to_message_id", type="integer", example=123, description="ID of message being replied to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Message sent successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to send message",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Error message"),
     *             @OA\Property(property="error", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function sendEnhancedMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'content' => ['required_without:media_url', 'string', 'nullable'],
            'media_url' => ['required_without:content', 'string', 'nullable'],
            'reply_to_message_id' => ['nullable', 'integer', 'exists:messages,id']
        ]);

        try {
            $user = $request->user();
            $chat = $user->chats()->findOrFail($id);

            // Create message with enhanced data
            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'content' => $validated['content'] ?? null,
                'media_url' => $validated['media_url'] ?? null,
                'reply_to_message_id' => $validated['reply_to_message_id'] ?? null,
                'message_type' => $this->determineMessageType($validated),
                'sent_at' => now()
            ]);

            // Update chat activity
            $chat->updateLastActivity();
            
            // Update user's last active time and activity type
            $user->updateLastActive();
            $user->update(['last_activity_type' => 'message_sent']);

            // Track activity with detailed metadata
            $user->trackMessageSent(
                $chat->id, 
                $message->id, 
                $message->message_type
            );

            // Load message with relationships for response
            $message->load([
                'sender:id,email',
                'replyTo:id,content,sender_id',
                'replyTo.sender:id,email'
            ]);

            // Add read status for response
            $readStatus = $message->getReadStatusFor($user);
            $message->is_mine = $readStatus['is_mine'];
            $message->is_read = $readStatus['is_read'];

            // Broadcast real-time event if available
            if (class_exists('\App\Events\NewMessageEvent')) {
                broadcast(new \App\Events\NewMessageEvent($message, $chat))->toOthers();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => [
                    'message' => $message,
                    'chat' => [
                        'id' => $chat->id,
                        'last_activity_at' => $chat->last_activity_at,
                        'message_count' => $chat->message_count + 1
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chat with enhanced online status and activity information
     */
    public function getEnhancedChat(Request $request, $id)
    {
        try {
            $user = $request->user();
            $perPage = $request->input('per_page', 20);

            $chat = $user->chats()
                ->with(['users' => function($query) use ($user) {
                    $query->where('users.id', '!=', $user->id)
                          ->with(['profile', 'profilePhoto:id,user_id,original_url,thumbnail_url,medium_url,is_profile_photo']);
                }])
                ->findOrFail($id);

            // Get other user with enhanced status
            $otherUser = $chat->users->first();
            $otherUser->is_online = $otherUser->isOnline();
            $otherUser->last_seen = $otherUser->last_active_at;
            $otherUser->engagement_score = $otherUser->getChatEngagementScore();
            $otherUser->is_active_in_chat = $otherUser->isActiveInLast(15);
            
            $chat->other_user = $otherUser;
            unset($chat->users);

            // Track chat opened activity
            $user->trackChatOpened($chat->id);

            // Enhanced message loading with read receipts
            $messages = Message::inChat($chat->id)
                ->with([
                    'sender:id,email',
                    'replyTo:id,content,sender_id',
                    'replyTo.sender:id,email',
                    'messageReads' => function($query) use ($user) {
                        $query->where('user_id', '!=', $user->id)
                              ->with('user:id,email');
                    }
                ])
                ->recent()
                ->paginate($perPage);

            // Mark messages as read efficiently
            $unreadMessageIds = MessageRead::getUnreadMessageIds($chat->id, $user->id);
            $readCount = 0;
            
            if (!empty($unreadMessageIds)) {
                $readCount = MessageRead::markMessagesAsRead($unreadMessageIds, $user->id);
                
                $user->chats()->updateExistingPivot($chat->id, [
                    'last_read_at' => now()
                ]);

                // Track read activity
                $user->trackMessagesRead($chat->id, $readCount);
            }

            // Transform messages with enhanced read status
            $messages->getCollection()->transform(function ($message) use ($user) {
                $readStatus = $message->getReadStatusFor($user);
                $message->is_mine = $readStatus['is_mine'];
                $message->is_read = $readStatus['is_read'];
                $message->read_at = $readStatus['read_at'];
                
                // Add read by other users info
                $message->read_by_others = $message->messageReads->map(function($read) {
                    return [
                        'user_id' => $read->user_id,
                        'user' => $read->user,
                        'read_at' => $read->read_at
                    ];
                });
                
                unset($message->messageReads);
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
                    ],
                    'activity_summary' => [
                        'messages_read' => $readCount,
                        'other_user_online' => $otherUser->is_online,
                        'other_user_active_in_chat' => $otherUser->is_active_in_chat
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get typing status for chat
     */
    public function getTypingStatus(Request $request, $id)
    {
        try {
            $user = $request->user();
            $chat = $user->chats()->findOrFail($id);
            
            // Get typing users using the optimized method
            $typingUsers = UserActivity::getTypingUsers($id, 30);
            
            // Filter out current user
            $typingUsers = array_filter($typingUsers, function($typingUser) use ($user) {
                return $typingUser['user_id'] !== $user->id;
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'typing_users' => array_values($typingUsers),
                    'count' => count($typingUsers)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get typing status'
            ], 500);
        }
    }

    /**
     * Update typing status
     */
    public function updateTypingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'is_typing' => ['required', 'boolean']
        ]);

        try {
            $user = $request->user();
            $chat = $user->chats()->findOrFail($id);

            if ($validated['is_typing']) {
                // Track typing activity (includes spam prevention)
                $user->trackTyping($chat->id);

                // Broadcast typing event if available
                if (class_exists('\App\Events\UserTypingEvent')) {
                    broadcast(new \App\Events\UserTypingEvent($user, $chat))->toOthers();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Typing status updated'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update typing status'
            ], 500);
        }
    }

    /**
     * Get chat activity analytics
     */
    public function getChatActivity(Request $request, $id)
    {
        try {
            $user = $request->user();
            $chat = $user->chats()->findOrFail($id);
            $days = $request->input('days', 7);

            // Get activity metrics for this chat
            $activities = UserActivity::forChat($id)
                ->recent($days)
                ->with('user:id,email')
                ->get()
                ->groupBy('activity_type');

            $metrics = [
                'messages_sent' => $activities->get('message_sent', collect())->count(),
                'messages_read' => $activities->get('messages_read', collect())->count(),
                'chat_opens' => $activities->get('chat_opened', collect())->count(),
                'typing_events' => $activities->get('typing', collect())->count(),
                'total_activities' => $activities->flatten()->count()
            ];

            // Get activity timeline
            $timeline = UserActivity::forChat($id)
                ->recent($days)
                ->selectRaw('DATE(created_at) as date, activity_type, COUNT(*) as count')
                ->groupBy('date', 'activity_type')
                ->orderBy('date')
                ->get()
                ->groupBy('date');

            // Get most active users in this chat
            $activeUsers = UserActivity::forChat($id)
                ->recent($days)
                ->selectRaw('user_id, COUNT(*) as activity_count')
                ->with('user:id,email')
                ->groupBy('user_id')
                ->orderByDesc('activity_count')
                ->limit(5)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'chat_id' => $id,
                    'period_days' => $days,
                    'metrics' => $metrics,
                    'timeline' => $timeline,
                    'most_active_users' => $activeUsers,
                    'engagement_score' => $this->calculateChatEngagement($metrics)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get chat activity'
            ], 500);
        }
    }

    /**
     * Get online status of users in chat
     */
    public function getOnlineStatus(Request $request, $id)
    {
        try {
            $user = $request->user();
            $chat = $user->chats()->findOrFail($id);

            $users = $chat->users()
                ->where('users.id', '!=', $user->id)
                ->get()
                ->map(function($chatUser) {
                    return [
                        'user_id' => $chatUser->id,
                        'email' => $chatUser->email,
                        'is_online' => $chatUser->isOnline(),
                        'last_active_at' => $chatUser->last_active_at,
                        'last_activity_type' => $chatUser->last_activity_type,
                        'is_active_in_chat' => $chatUser->isActiveInLast(15)
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'users' => $users,
                    'online_count' => $users->where('is_online', true)->count(),
                    'active_in_chat_count' => $users->where('is_active_in_chat', true)->count()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get online status'
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
     * Calculate chat engagement score
     */
    private function calculateChatEngagement(array $metrics): float
    {
        $weights = [
            'messages_sent' => 2.0,
            'messages_read' => 1.0,
            'chat_opens' => 0.5,
            'typing_events' => 0.3
        ];

        $score = 0;
        foreach ($metrics as $type => $count) {
            if (isset($weights[$type])) {
                $score += $count * $weights[$type];
            }
        }

        return round($score, 2);
    }
}
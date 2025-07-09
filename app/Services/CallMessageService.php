<?php

namespace App\Services;

use App\Models\Call;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Events\NewMessageEvent;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CallMessageService
{
    /**
     * Create or update a call message based on call status
     */
    public function createOrUpdateCallMessage(Call $call, string $action = 'initiated'): ?Message
    {
        try {
            DB::beginTransaction();

            // Find or create chat between caller and receiver
            $chat = $this->findOrCreateChat($call->caller_id, $call->receiver_id);
            
            // Find existing call message or create new one
            $message = $this->findOrCreateCallMessage($call, $chat, $action);

            // Update message content and data based on call status
            $this->updateMessageContent($message, $call, $action);

            DB::commit();
            
            // Broadcast message update if it's a status change
            if ($action !== 'initiated') {
                broadcast(new NewMessageEvent($message))->toOthers();
            }

            return $message;
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create/update call message: ' . $e->getMessage(), [
                'call_id' => $call->id,
                'action' => $action
            ]);
            return null;
        }
    }

    /**
     * Find or create chat between two users
     */
    private function findOrCreateChat(int $userId1, int $userId2): Chat
    {
        // Find existing chat between these two users
        $chat = Chat::where('type', 'private')
            ->whereHas('users', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('users', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->first();

        if (!$chat) {
            DB::beginTransaction();
            try {
                // Create new private chat
                $chat = Chat::create([
                    'type' => 'private',
                    'is_active' => true,
                    'last_activity_at' => now()
                ]);

                // Add both users to the chat
                $chat->users()->attach([
                    $userId1 => [
                        'joined_at' => now(),
                        'role' => 'member'
                    ],
                    $userId2 => [
                        'joined_at' => now(),
                        'role' => 'member'
                    ]
                ]);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        return $chat;
    }

    /**
     * Find existing call message or create new one
     */
    private function findOrCreateCallMessage(Call $call, Chat $chat, string $action): Message
    {
        // Look for existing call message
        $existingMessage = Message::where('call_id', $call->id)
            ->where('chat_id', $chat->id)
            ->first();

        if ($existingMessage) {
            return $existingMessage;
        }

        // Create new call message
        $senderId = $action === 'initiated' ? $call->caller_id : $call->receiver_id;
        
        return Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $senderId,
            'call_id' => $call->id,
            'content' => $this->generateCallContent($call, $action),
            'message_type' => 'call',
            'media_data' => $this->generateCallMediaData($call, $action),
            'sent_at' => now(),
            'status' => 'sent'
        ]);
    }

    /**
     * Update message content based on call status
     */
    private function updateMessageContent(Message $message, Call $call, string $action): void
    {
        $message->content = $this->generateCallContent($call, $action);
        $message->media_data = $this->generateCallMediaData($call, $action);
        $message->save();

        // Update chat last activity
        $message->chat->updateLastActivity();
    }

    /**
     * Generate call content based on status
     */
    private function generateCallContent(Call $call, string $action): string
    {
        $callType = ucfirst($call->type);
        
        switch ($call->status) {
            case 'initiated':
                return $action === 'initiated' ? "Outgoing {$callType} Call" : "Incoming {$callType} Call";
            
            case 'ongoing':
                return "{$callType} Call in progress";
            
            case 'completed':
                $duration = $this->calculateCallDuration($call);
                return "{$callType} Call - {$duration}";
            
            case 'missed':
                return "Missed {$callType} Call";
            
            case 'rejected':
                return "{$callType} Call declined";
            
            default:
                return "{$callType} Call";
        }
    }

    /**
     * Generate call media data
     */
    private function generateCallMediaData(Call $call, string $action): array
    {
        $duration = $this->calculateCallDurationInSeconds($call);
        
        return [
            'call_type' => $call->type,
            'call_status' => $this->mapCallStatusToMessageStatus($call->status, $action),
            'duration' => $duration,
            'ended_reason' => $this->getEndedReason($call),
            'call_id' => $call->id,
            'channel_name' => $call->channel_name,
            'started_at' => $call->started_at?->toISOString(),
            'ended_at' => $call->ended_at?->toISOString()
        ];
    }

    /**
     * Map call status to message status
     */
    private function mapCallStatusToMessageStatus(string $callStatus, string $action): string
    {
        switch ($callStatus) {
            case 'initiated':
                return $action === 'initiated' ? 'outgoing' : 'incoming';
            case 'ongoing':
                return 'ongoing';
            case 'completed':
                return 'completed';
            case 'missed':
                return 'missed';
            case 'rejected':
                return 'declined';
            default:
                return 'unknown';
        }
    }

    /**
     * Calculate call duration in human-readable format
     */
    private function calculateCallDuration(Call $call): string
    {
        if (!$call->started_at || !$call->ended_at) {
            return '0s';
        }

        $seconds = $call->ended_at->diffInSeconds($call->started_at);
        return $this->formatDuration($seconds);
    }

    /**
     * Calculate call duration in seconds
     */
    private function calculateCallDurationInSeconds(Call $call): int
    {
        if (!$call->started_at || !$call->ended_at) {
            return 0;
        }

        return $call->ended_at->diffInSeconds($call->started_at);
    }

    /**
     * Format duration in human-readable format
     */
    private function formatDuration(int $seconds): string
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
     * Get ended reason based on call status
     */
    private function getEndedReason(Call $call): ?string
    {
        switch ($call->status) {
            case 'completed':
                return 'completed';
            case 'rejected':
                return 'declined';
            case 'missed':
                return 'no_answer';
            default:
                return null;
        }
    }

    /**
     * Handle missed call scenario
     */
    public function handleMissedCall(Call $call): void
    {
        try {
            // Update call status to missed
            $call->update([
                'status' => 'missed',
                'ended_at' => now()
            ]);

            // Create/update call message
            $this->createOrUpdateCallMessage($call, 'missed');
            
        } catch (Exception $e) {
            Log::error('Failed to handle missed call: ' . $e->getMessage(), [
                'call_id' => $call->id
            ]);
        }
    }

    /**
     * Get call history for a user with optimized queries
     */
    public function getCallHistory(User $user, array $filters = []): array
    {
        $query = Message::where('message_type', 'call')
            ->whereHas('chat.users', function ($chatQuery) use ($user) {
                $chatQuery->where('user_id', $user->id);
            })
            ->with(['chat', 'sender', 'call'])
            ->orderBy('sent_at', 'desc');

        // Apply filters
        if (isset($filters['call_type'])) {
            $query->whereJsonContains('media_data->call_type', $filters['call_type']);
        }

        if (isset($filters['call_status'])) {
            $query->whereJsonContains('media_data->call_status', $filters['call_status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('sent_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('sent_at', '<=', $filters['date_to']);
        }

        return $query->paginate($filters['per_page'] ?? 20)->toArray();
    }

    /**
     * Get call analytics for a user
     */
    public function getCallAnalytics(User $user): array
    {
        $callMessages = Message::where('message_type', 'call')
            ->whereHas('chat.users', function ($chatQuery) use ($user) {
                $chatQuery->where('user_id', $user->id);
            })
            ->get();

        $totalCalls = $callMessages->count();
        $completedCalls = $callMessages->where('media_data.call_status', 'completed')->count();
        $missedCalls = $callMessages->where('media_data.call_status', 'missed')->count();
        $totalDuration = $callMessages->sum('media_data.duration');

        return [
            'total_calls' => $totalCalls,
            'completed_calls' => $completedCalls,
            'missed_calls' => $missedCalls,
            'success_rate' => $totalCalls > 0 ? round(($completedCalls / $totalCalls) * 100, 2) : 0,
            'total_duration' => $totalDuration,
            'average_duration' => $completedCalls > 0 ? round($totalDuration / $completedCalls, 2) : 0,
            'formatted_total_duration' => $this->formatDuration($totalDuration)
        ];
    }
} 
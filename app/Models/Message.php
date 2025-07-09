<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id', 'sender_id', 'reply_to_message_id', 'content',
        'message_type', 'media_data', 'media_url', 'thumbnail_url',
        'status', 'is_edited', 'edited_at', 'sent_at', 'call_id'
    ];

    protected $casts = [
        'media_data' => 'json',
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
        'sent_at' => 'datetime'
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function messageReads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    /**
     * Get the call associated with this message (if it's a call message).
     */
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    /**
     * Check if this message is a call message
     */
    public function isCallMessage(): bool
    {
        return $this->message_type === 'call';
    }

    /**
     * Get call data from media_data if it's a call message
     */
    public function getCallData(): ?array
    {
        if (!$this->isCallMessage()) {
            return null;
        }

        return $this->media_data;
    }

    /**
     * Get call duration from media_data
     */
    public function getCallDuration(): ?int
    {
        $callData = $this->getCallData();
        return $callData['duration'] ?? null;
    }

    /**
     * Get formatted call duration
     */
    public function getFormattedCallDuration(): ?string
    {
        $duration = $this->getCallDuration();
        
        if ($duration === null) {
            return null;
        }

        return $this->formatDuration($duration);
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
     * Scope to get call messages only
     */
    public function scopeCallMessages(Builder $query): Builder
    {
        return $query->where('message_type', 'call');
    }

    /**
     * Scope to get call messages for a specific call
     */
    public function scopeForCall(Builder $query, Call $call): Builder
    {
        return $query->where('call_id', $call->id);
    }

    /**
     * Scope to get messages unread by a specific user
     */
    public function scopeUnreadByUser(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('messageReads', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->where('sender_id', '!=', $user->id);
    }

    /**
     * Scope to get messages in a chat
     */
    public function scopeInChat(Builder $query, int $chatId): Builder
    {
        return $query->where('chat_id', $chatId);
    }

    /**
     * Scope to get recent messages
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('sent_at', 'desc');
    }

    /**
     * Mark this message as read by a user
     */
    public function markAsReadBy(User $user): bool
    {
        // Don't mark own messages as read
        if ($this->sender_id === $user->id) {
            return false;
        }

        return $this->messageReads()->firstOrCreate(['user_id' => $user->id]) ? true : false;
    }

    /**
     * Check if this message is read by a user
     */
    public function isReadBy(User $user): bool
    {
        // Own messages are always considered "read"
        if ($this->sender_id === $user->id) {
            return true;
        }

        return $this->messageReads()->where('user_id', $user->id)->exists();
    }

    /**
     * Get the read status for a specific user (for API responses)
     */
    public function getReadStatusFor(User $user): array
    {
        $isMine = $this->sender_id === $user->id;
        $isRead = $isMine ? false : $this->isReadBy($user);
        $readAt = null;
        
        if ($isRead && !$isMine) {
            $readRecord = $this->messageReads()->where('user_id', $user->id)->first();
            $readAt = $readRecord?->read_at;
        }

        return [
            'is_read' => $isRead,
            'read_at' => $readAt,
            'is_mine' => $isMine
        ];
    }

    /**
     * Mark multiple messages as read by user efficiently
     */
    public static function markMultipleAsRead(array $messageIds, User $user): int
    {
        // Filter out messages sent by the user (can't read your own messages)
        $messagesToMark = static::whereIn('id', $messageIds)
            ->where('sender_id', '!=', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($messagesToMark)) {
            return 0;
        }

        return MessageRead::markMessagesAsRead($messagesToMark, $user->id);
    }

    /**
     * Get unread count for a user in a specific chat
     */
    public static function getUnreadCountForUserInChat(User $user, int $chatId): int
    {
        return static::inChat($chatId)
            ->unreadByUser($user)
            ->count();
    }
}

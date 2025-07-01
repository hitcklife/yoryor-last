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
        'status', 'is_edited', 'edited_at', 'sent_at'
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
        $isRead = $this->isReadBy($user);
        $readAt = null;
        
        if ($isRead && $this->sender_id !== $user->id) {
            $readRecord = $this->messageReads()->where('user_id', $user->id)->first();
            $readAt = $readRecord?->read_at;
        }

        return [
            'is_read' => $isRead,
            'read_at' => $readAt,
            'is_mine' => $this->sender_id === $user->id
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

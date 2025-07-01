<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id', 
        'read_at'
    ];

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark multiple messages as read by a user
     */
    public static function markMessagesAsRead(array $messageIds, int $userId): int
    {
        $timestamp = now();
        $reads = [];
        
        foreach ($messageIds as $messageId) {
            $reads[] = [
                'message_id' => $messageId,
                'user_id' => $userId,
                'read_at' => $timestamp,
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }
        
        return static::insertOrIgnore($reads);
    }

    /**
     * Get unread message IDs for a user in a chat
     */
    public static function getUnreadMessageIds(int $chatId, int $userId): array
    {
        return \DB::table('messages')
            ->leftJoin('message_reads', function($join) use ($userId) {
                $join->on('messages.id', '=', 'message_reads.message_id')
                     ->where('message_reads.user_id', '=', $userId);
            })
            ->where('messages.chat_id', $chatId)
            ->where('messages.sender_id', '!=', $userId)
            ->whereNull('message_reads.id')
            ->whereNull('messages.deleted_at')
            ->pluck('messages.id')
            ->toArray();
    }
}

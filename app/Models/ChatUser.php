<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'is_muted',
        'last_read_at',
        'joined_at',
        'left_at',
        'role'
    ];

    protected $casts = [
        'is_muted' => 'boolean',
        'last_read_at' => 'datetime',
        'joined_at' => 'datetime',
        'left_at' => 'datetime'
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user is active in chat (hasn't left)
     */
    public function isActive(): bool
    {
        return is_null($this->left_at);
    }

    /**
     * Check if user is admin of the chat
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Update last read timestamp
     */
    public function updateLastRead(): void
    {
        $this->update(['last_read_at' => now()]);
    }

    /**
     * Get unread messages count for this user in this chat
     */
    public function getUnreadCount(): int
    {
        return $this->chat->messages()
            ->where('sender_id', '!=', $this->user_id)
            ->where('sent_at', '>', $this->last_read_at ?? $this->joined_at)
            ->count();
    }
}

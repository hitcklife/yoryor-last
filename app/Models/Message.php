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

    public function scopeUnreadByUser(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('messageReads', function($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    public function markAsReadBy(User $user): void
    {
        $this->messageReads()->firstOrCreate(['user_id' => $user->id]);
    }

    public function isReadBy(User $user): bool
    {
        return $this->messageReads()->where('user_id', $user->id)->exists();
    }
}

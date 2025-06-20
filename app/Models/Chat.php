<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type', 'name', 'description', 'last_activity_at', 'is_active'
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_users')
                    ->withPivot(['is_muted', 'last_read_at', 'joined_at', 'left_at', 'role'])
                    ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()->wherePivotNull('left_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('sent_at');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany('sent_at');
    }

    public function getOtherUser(User $currentUser): ?User
    {
        if ($this->type !== 'private') {
            return null;
        }

        return $this->users()->where('user_id', '!=', $currentUser->id)->first();
    }

    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public function getUnreadCountForUser(User $user): int
    {
        $chatUser = $this->users()->where('user_id', $user->id)->first();
        if (!$chatUser) {
            return 0;
        }

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('sent_at', '>', $chatUser->pivot->last_read_at ?? $chatUser->pivot->joined_at)
            ->count();
    }
}

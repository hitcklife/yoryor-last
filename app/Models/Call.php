<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Call extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'channel_name',
        'caller_id',
        'receiver_id',
        'type',
        'status',
        'started_at',
        'ended_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /**
     * Get the caller of the call.
     */
    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Get the receiver of the call.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get the messages associated with this call.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the primary call message (first message created for this call).
     */
    public function callMessage()
    {
        return $this->hasOne(Message::class)->where('message_type', 'call')->oldest();
    }

    /**
     * Get call duration in seconds
     */
    public function getDurationInSeconds(): int
    {
        if (!$this->started_at || !$this->ended_at) {
            return 0;
        }

        return $this->ended_at->diffInSeconds($this->started_at);
    }

    /**
     * Get call duration formatted
     */
    public function getFormattedDuration(): string
    {
        $seconds = $this->getDurationInSeconds();

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
     * Check if call is active (ongoing or initiated)
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['initiated', 'ongoing']);
    }

    /**
     * Check if call is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if call was missed
     */
    public function isMissed(): bool
    {
        return $this->status === 'missed';
    }

    /**
     * Check if call was rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the other participant in the call
     */
    public function getOtherParticipant(User $user): ?User
    {
        if ($this->caller_id === $user->id) {
            return $this->receiver;
        } elseif ($this->receiver_id === $user->id) {
            return $this->caller;
        }

        return null;
    }

    /**
     * Check if user is participant of this call
     */
    public function isParticipant(User $user): bool
    {
        return $this->caller_id === $user->id || $this->receiver_id === $user->id;
    }

    /**
     * Get call statistics
     */
    public function getCallStats(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'status' => $this->status,
            'duration_seconds' => $this->getDurationInSeconds(),
            'formatted_duration' => $this->getFormattedDuration(),
            'started_at' => $this->started_at,
            'ended_at' => $this->ended_at,
            'created_at' => $this->created_at,
            'caller' => [
                'id' => $this->caller->id,
                'name' => $this->caller->name,
                'profile_photo_url' => $this->caller->getProfilePhotoUrl(),
            ],
            'receiver' => [
                'id' => $this->receiver->id,
                'name' => $this->receiver->name,
                'profile_photo_url' => $this->receiver->getProfilePhotoUrl(),
            ],
        ];
    }

    /**
     * Scope for active calls
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['initiated', 'ongoing']);
    }

    /**
     * Scope for completed calls
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for missed calls
     */
    public function scopeMissed($query)
    {
        return $query->where('status', 'missed');
    }

    /**
     * Scope for user's calls
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('caller_id', $user->id)
            ->orWhere('receiver_id', $user->id);
    }

    /**
     * Scope for calls between specific users
     */
    public function scopeBetweenUsers($query, User $user1, User $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('caller_id', $user1->id)->where('receiver_id', $user2->id);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('caller_id', $user2->id)->where('receiver_id', $user1->id);
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'activity_type', 
        'metadata', 
        'ip_address', 
        'user_agent'
    ];

    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime'
    ];

    // Disable updated_at since we only need created_at for activities
    public $timestamps = false;
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->created_at = now();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a user activity efficiently
     */
    public static function logActivity(int $userId, string $activityType, array $metadata = []): void
    {
        // Skip logging in testing environment to improve performance
        if (app()->environment('testing')) {
            return;
        }

        static::create([
            'user_id' => $userId,
            'activity_type' => $activityType,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent()
        ]);
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific activity type
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for activities in specific timeframe
     */
    public function scopeInTimeframe(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope for chat-related activities
     */
    public function scopeChatRelated(Builder $query): Builder
    {
        return $query->whereIn('activity_type', [
            'message_sent', 'chat_opened', 'typing', 'messages_read'
        ]);
    }

    /**
     * Get activities for a specific chat
     */
    public function scopeForChat(Builder $query, int $chatId): Builder
    {
        return $query->whereJsonContains('metadata->chat_id', $chatId);
    }

    /**
     * Get typing activities for a chat within time window
     */
    public static function getTypingUsers(int $chatId, int $seconds = 30): array
    {
        return static::where('activity_type', 'typing')
            ->where('created_at', '>', now()->subSeconds($seconds))
            ->whereJsonContains('metadata->chat_id', $chatId)
            ->with('user:id,email')
            ->get()
            ->map(function($activity) {
                return [
                    'user_id' => $activity->user_id,
                    'user' => $activity->user,
                    'started_typing_at' => $activity->created_at
                ];
            })
            ->toArray();
    }

    /**
     * Get user engagement metrics
     */
    public static function getUserEngagementMetrics(int $userId, int $days = 30): array
    {
        $activities = static::where('user_id', $userId)
            ->recent($days)
            ->get()
            ->groupBy('activity_type');

        return [
            'messages_sent' => $activities->get('message_sent', collect())->count(),
            'chats_opened' => $activities->get('chat_opened', collect())->count(),
            'matches_made' => $activities->get('match_made', collect())->count(),
            'profile_views' => $activities->get('profile_view', collect())->count(),
            'swipes_right' => $activities->get('swipe_right', collect())->count(),
            'swipes_left' => $activities->get('swipe_left', collect())->count(),
            'engagement_score' => static::calculateEngagementScore($activities),
            'most_active_day' => static::getMostActiveDay($activities),
            'activity_trend' => static::getActivityTrend($userId, $days)
        ];
    }

    /**
     * Calculate engagement score based on activities
     */
    private static function calculateEngagementScore($activities): float
    {
        $weights = [
            'message_sent' => 2.0,
            'match_made' => 5.0,
            'chat_opened' => 1.0,
            'profile_view' => 0.5,
            'swipe_right' => 1.5,
            'photo_upload' => 3.0
        ];

        $score = 0;
        foreach ($activities as $type => $typeActivities) {
            $weight = $weights[$type] ?? 0.5;
            $score += $typeActivities->count() * $weight;
        }

        return round($score, 2);
    }

    /**
     * Get most active day of the week
     */
    private static function getMostActiveDay($activities): string
    {
        $days = [
            0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 
            3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'
        ];

        $dayCount = $activities->flatten()
            ->groupBy(function($activity) {
                return $activity->created_at->dayOfWeek;
            })
            ->map->count()
            ->sortDesc();

        $mostActiveDay = $dayCount->keys()->first();
        return $days[$mostActiveDay] ?? 'N/A';
    }

    /**
     * Get activity trend (increasing/decreasing)
     */
    private static function getActivityTrend(int $userId, int $days): array
    {
        $midPoint = $days / 2;
        
        $firstHalf = static::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->where('created_at', '<', now()->subDays($midPoint))
            ->count();
            
        $secondHalf = static::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($midPoint))
            ->count();

        $trend = $secondHalf > $firstHalf ? 'increasing' : 
                ($secondHalf < $firstHalf ? 'decreasing' : 'stable');

        return [
            'trend' => $trend,
            'first_half_count' => $firstHalf,
            'second_half_count' => $secondHalf,
            'change_percentage' => $firstHalf > 0 ? 
                round((($secondHalf - $firstHalf) / $firstHalf) * 100, 2) : 0
        ];
    }

    /**
     * Cleanup old activities (for scheduled job)
     */
    public static function cleanupOldActivities(int $keepDays = 90): int
    {
        return static::where('created_at', '<', now()->subDays($keepDays))->delete();
    }
}
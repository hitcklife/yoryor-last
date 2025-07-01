<?php

namespace App\Traits;

use App\Models\UserActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait TracksActivity
{
    /**
     * Log an activity for this user
     */
    public function logActivity(string $activityType, array $metadata = []): void
    {
        UserActivity::logActivity($this->id, $activityType, $metadata);
    }

    /**
     * Get user's activities relationship
     */
    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    /**
     * Get recent activities for this user
     */
    public function getRecentActivity(int $days = 7)
    {
        return $this->activities()
            ->recent($days)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get chat engagement score for this user
     */
    public function getChatEngagementScore(int $days = 30): float
    {
        $messagesSent = $this->activities()
            ->ofType('message_sent')
            ->recent($days)
            ->count();

        $chatsOpened = $this->activities()
            ->ofType('chat_opened')
            ->recent($days)
            ->count();

        $messagesRead = $this->activities()
            ->ofType('messages_read')
            ->recent($days)
            ->count();

        // Weight different activities
        return ($messagesSent * 2.0) + ($chatsOpened * 1.5) + ($messagesRead * 0.5);
    }

    /**
     * Get overall engagement score including all activities
     */
    public function getOverallEngagementScore(int $days = 30): float
    {
        return UserActivity::getUserEngagementMetrics($this->id, $days)['engagement_score'];
    }

    /**
     * Get user engagement metrics
     */
    public function getEngagementMetrics(int $days = 30): array
    {
        return UserActivity::getUserEngagementMetrics($this->id, $days);
    }

    /**
     * Check if user is currently active (has activity in last X minutes)
     */
    public function isActiveInLast(int $minutes = 15): bool
    {
        return $this->activities()
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->exists();
    }

    /**
     * Get user's most used activity types
     */
    public function getMostUsedActivities(int $limit = 5, int $days = 30): array
    {
        return $this->activities()
            ->recent($days)
            ->selectRaw('activity_type, COUNT(*) as count')
            ->groupBy('activity_type')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('count', 'activity_type')
            ->toArray();
    }

    /**
     * Get user's activity timeline for a specific date range
     */
    public function getActivityTimeline(string $startDate, string $endDate): array
    {
        return $this->activities()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, activity_type, COUNT(*) as count')
            ->groupBy('date', 'activity_type')
            ->orderBy('date')
            ->get()
            ->groupBy('date')
            ->toArray();
    }

    /**
     * Track chat-related activities
     */
    public function trackChatActivity(string $activityType, int $chatId, array $additionalData = []): void
    {
        $metadata = array_merge([
            'chat_id' => $chatId
        ], $additionalData);

        $this->logActivity($activityType, $metadata);
    }

    /**
     * Track message sent activity
     */
    public function trackMessageSent(int $chatId, int $messageId, string $messageType = 'text'): void
    {
        $this->trackChatActivity('message_sent', $chatId, [
            'message_id' => $messageId,
            'message_type' => $messageType
        ]);
    }

    /**
     * Track messages read activity
     */
    public function trackMessagesRead(int $chatId, int $messageCount): void
    {
        $this->trackChatActivity('messages_read', $chatId, [
            'message_count' => $messageCount
        ]);
    }

    /**
     * Track chat opened activity
     */
    public function trackChatOpened(int $chatId): void
    {
        $this->trackChatActivity('chat_opened', $chatId);
    }

    /**
     * Track typing activity
     */
    public function trackTyping(int $chatId): void
    {
        // Only log if no typing activity in last 30 seconds to avoid spam
        $recentTyping = $this->activities()
            ->ofType('typing')
            ->where('created_at', '>', now()->subSeconds(30))
            ->whereJsonContains('metadata->chat_id', $chatId)
            ->exists();

        if (!$recentTyping) {
            $this->trackChatActivity('typing', $chatId);
        }
    }

    /**
     * Track match-related activities
     */
    public function trackMatchActivity(string $activityType, int $targetUserId, array $additionalData = []): void
    {
        $metadata = array_merge([
            'target_user_id' => $targetUserId
        ], $additionalData);

        $this->logActivity($activityType, $metadata);
    }

    /**
     * Track swipe activities
     */
    public function trackSwipe(string $direction, int $targetUserId): void
    {
        $this->trackMatchActivity("swipe_{$direction}", $targetUserId);
    }

    /**
     * Track profile view
     */
    public function trackProfileView(int $viewedUserId): void
    {
        $this->trackMatchActivity('profile_view', $viewedUserId);
    }

    /**
     * Track match made
     */
    public function trackMatchMade(int $matchedUserId): void
    {
        $this->trackMatchActivity('match_made', $matchedUserId);
    }

    /**
     * Track login activity
     */
    public function trackLogin(): void
    {
        $this->logActivity('login');
    }

    /**
     * Track logout activity
     */
    public function trackLogout(): void
    {
        $this->logActivity('logout');
    }

    /**
     * Track profile update
     */
    public function trackProfileUpdate(array $updatedFields = []): void
    {
        $this->logActivity('profile_updated', [
            'updated_fields' => $updatedFields
        ]);
    }

    /**
     * Track photo upload
     */
    public function trackPhotoUpload(int $photoId): void
    {
        $this->logActivity('photo_upload', [
            'photo_id' => $photoId
        ]);
    }
}
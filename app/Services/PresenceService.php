<?php

namespace App\Services;

use App\Models\User;
use App\Events\UserOnlineStatusChanged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PresenceService
{
    /**
     * Cache TTL for online status (in seconds)
     */
    private const ONLINE_STATUS_TTL = 300; // 5 minutes

    /**
     * Cache TTL for presence data (in seconds)
     */
    private const PRESENCE_DATA_TTL = 60; // 1 minute

    /**
     * Mark user as online and update their presence
     */
    public function markUserOnline(User $user): void
    {
        $wasOnline = $this->isUserOnline($user->id);
        
        // Update user's last active timestamp
        $user->updateLastActive();
        
        // Cache the online status
        Cache::put("user_online_{$user->id}", true, self::ONLINE_STATUS_TTL);
        
        // Store presence data
        $this->storePresenceData($user);
        
        // Broadcast status change if user wasn't online before
        if (!$wasOnline) {
            event(new UserOnlineStatusChanged($user, true));
        }
    }

    /**
     * Mark user as offline
     */
    public function markUserOffline(User $user): void
    {
        $wasOnline = $this->isUserOnline($user->id);
        
        // Remove from online cache
        Cache::forget("user_online_{$user->id}");
        
        // Remove presence data
        $this->removePresenceData($user);
        
        // Broadcast status change if user was online before
        if ($wasOnline) {
            event(new UserOnlineStatusChanged($user, false));
        }
    }

    /**
     * Check if a user is currently online
     */
    public function isUserOnline(int $userId): bool
    {
        return Cache::has("user_online_{$userId}");
    }

    /**
     * Get all online users
     */
    public function getOnlineUsers(): Collection
    {
        $onlineUserIds = $this->getOnlineUserIds();
        
        return User::whereIn('id', $onlineUserIds)
            ->with(['profile', 'profilePhoto'])
            ->get();
    }

    /**
     * Get online user IDs
     */
    public function getOnlineUserIds(): array
    {
        $keys = Cache::getRedis()->keys('*user_online_*');
        
        return collect($keys)->map(function ($key) {
            return (int) str_replace('user_online_', '', $key);
        })->toArray();
    }

    /**
     * Get online users in a specific chat
     */
    public function getOnlineUsersInChat(int $chatId): Collection
    {
        $cacheKey = "chat_online_users_{$chatId}";
        
        return Cache::remember($cacheKey, 60, function () use ($chatId) {
            $chatUserIds = \App\Models\Chat::find($chatId)
                ?->users()
                ->pluck('users.id')
                ->toArray() ?? [];
            
            $onlineUserIds = $this->getOnlineUserIds();
            $onlineInChat = array_intersect($chatUserIds, $onlineUserIds);
            
            return User::whereIn('id', $onlineInChat)
                ->with(['profile', 'profilePhoto'])
                ->get();
        });
    }

    /**
     * Get user's online matches
     */
    public function getOnlineMatches(User $user): Collection
    {
        $cacheKey = "user_online_matches_{$user->id}";
        
        return Cache::remember($cacheKey, 120, function () use ($user) {
            $matchIds = $user->mutualMatches()
                ->pluck('matched_user_id')
                ->toArray();
            
            $onlineUserIds = $this->getOnlineUserIds();
            $onlineMatches = array_intersect($matchIds, $onlineUserIds);
            
            return User::whereIn('id', $onlineMatches)
                ->with(['profile', 'profilePhoto'])
                ->get();
        });
    }

    /**
     * Store presence data for a user
     */
    private function storePresenceData(User $user): void
    {
        $presenceData = [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'avatar' => $user->profile_photo_path,
            'is_online' => true,
            'last_active_at' => $user->last_active_at?->toISOString(),
            'online_since' => now()->toISOString(),
        ];
        
        Cache::put("presence_data_{$user->id}", $presenceData, self::PRESENCE_DATA_TTL);
    }

    /**
     * Remove presence data for a user
     */
    private function removePresenceData(User $user): void
    {
        Cache::forget("presence_data_{$user->id}");
    }

    /**
     * Get presence data for a user
     */
    public function getPresenceData(int $userId): ?array
    {
        return Cache::get("presence_data_{$userId}");
    }

    /**
     * Get presence data for multiple users
     */
    public function getPresenceDataForUsers(array $userIds): array
    {
        $presenceData = [];
        
        foreach ($userIds as $userId) {
            $data = $this->getPresenceData($userId);
            if ($data) {
                $presenceData[$userId] = $data;
            }
        }
        
        return $presenceData;
    }

    /**
     * Update user's typing status in a chat
     */
    public function updateTypingStatus(User $user, int $chatId, bool $isTyping): void
    {
        $key = "user_typing_{$user->id}_chat_{$chatId}";
        
        if ($isTyping) {
            Cache::put($key, true, 30); // 30 seconds
        } else {
            Cache::forget($key);
        }
        
        // Broadcast typing status to chat presence channel
        broadcast(new \App\Events\UserTypingStatusChanged($user, $chatId, $isTyping));
    }

    /**
     * Get typing users in a chat
     */
    public function getTypingUsersInChat(int $chatId): array
    {
        $keys = Cache::getRedis()->keys("*user_typing_*_chat_{$chatId}");
        
        return collect($keys)->map(function ($key) {
            preg_match('/user_typing_(\d+)_chat_\d+/', $key, $matches);
            return isset($matches[1]) ? (int) $matches[1] : null;
        })->filter()->values()->toArray();
    }

    /**
     * Get online statistics
     */
    public function getOnlineStatistics(): array
    {
        $totalOnline = count($this->getOnlineUserIds());
        $onlineInLast24Hours = User::where('last_active_at', '>=', now()->subDay())->count();
        $onlineInLastWeek = User::where('last_active_at', '>=', now()->subWeek())->count();
        
        return [
            'currently_online' => $totalOnline,
            'online_last_24_hours' => $onlineInLast24Hours,
            'online_last_week' => $onlineInLastWeek,
            'peak_online_today' => $this->getPeakOnlineToday(),
            'average_online_time' => $this->getAverageOnlineTime(),
        ];
    }

    /**
     * Get peak online count for today
     */
    private function getPeakOnlineToday(): int
    {
        return Cache::get('peak_online_today', 0);
    }

    /**
     * Get average online time for active users
     */
    private function getAverageOnlineTime(): float
    {
        // This would need to be implemented based on your activity tracking
        // For now, return a placeholder
        return 0.0;
    }

    /**
     * Clean up expired presence data
     */
    public function cleanupExpiredPresence(): void
    {
        $keys = Cache::getRedis()->keys('*user_online_*');
        
        foreach ($keys as $key) {
            if (!Cache::has($key)) {
                $userId = str_replace('user_online_', '', $key);
                Cache::forget("presence_data_{$userId}");
            }
        }
    }

    /**
     * Force update online status for all users based on last_active_at
     */
    public function syncOnlineStatusFromDatabase(): void
    {
        $onlineUsers = User::where('last_active_at', '>=', now()->subMinutes(5))
            ->get();
        
        foreach ($onlineUsers as $user) {
            $this->markUserOnline($user);
        }
    }

    /**
     * Get user's presence history
     */
    public function getUserPresenceHistory(int $userId, int $days = 7): array
    {
        // This would integrate with your UserActivity model
        return \App\Models\UserActivity::where('user_id', $userId)
            ->where('activity_type', 'online_status')
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($activity) {
                return [
                    'timestamp' => $activity->created_at->toISOString(),
                    'status' => $activity->metadata['status'] ?? 'online',
                    'duration' => $activity->metadata['duration'] ?? null,
                ];
            })
            ->toArray();
    }
} 
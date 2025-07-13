<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class PresenceService
{
    private const PRESENCE_DATA_TTL = 300; // 5 minutes

    /**
     * Mark user as online
     */
    public function markUserOnline(User $user): void
    {
        $key = "user_online_{$user->id}";
        Cache::put($key, true, 300); // 5 minutes TTL

        $this->storePresenceData($user);

        // Update user's last_active_at
        $user->update(['last_active_at' => now()]);

        // Broadcast online status
        broadcast(new \App\Events\UserOnlineStatusChanged($user, true));
    }

    /**
     * Mark user as offline
     */
    public function markUserOffline(User $user): void
    {
        $key = "user_online_{$user->id}";
        Cache::forget($key);

        $this->removePresenceData($user);

        // Update user's last_active_at
        $user->update(['last_active_at' => now()]);

        // Broadcast offline status
        broadcast(new \App\Events\UserOnlineStatusChanged($user, false));
    }

    /**
     * Check if user is online
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
        if ($this->isRedisAvailable()) {
            return $this->getOnlineUserIdsFromRedis();
        } else {
            return $this->getOnlineUserIdsFromDatabase();
        }
    }

    /**
     * Get online user IDs from Redis
     */
    private function getOnlineUserIdsFromRedis(): array
    {
        try {
            $keys = Cache::getRedis()->keys('*user_online_*');
            return collect($keys)->map(function ($key) {
                return (int) str_replace('user_online_', '', $key);
            })->toArray();
        } catch (\Exception $e) {
            // Fallback to database method if Redis fails
            return $this->getOnlineUserIdsFromDatabase();
        }
    }

    /**
     * Get online user IDs from database cache
     */
    private function getOnlineUserIdsFromDatabase(): array
    {
        // For database cache, we need to track online users differently
        // This is a simplified approach - in production you might want to use a separate table
        $onlineUsers = User::where('last_active_at', '>=', now()->subMinutes(5))
            ->pluck('id')
            ->toArray();

        return $onlineUsers;
    }

    /**
     * Check if Redis is available
     */
    private function isRedisAvailable(): bool
    {
        try {
            return config('cache.default') === 'redis' && Cache::getRedis() !== null;
        } catch (\Exception $e) {
            return false;
        }
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
            'avatar' => $user->getProfilePhotoUrl(),
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
        if ($this->isRedisAvailable()) {
            return $this->getTypingUsersFromRedis($chatId);
        } else {
            return $this->getTypingUsersFromDatabase($chatId);
        }
    }

    /**
     * Get typing users from Redis
     */
    private function getTypingUsersFromRedis(int $chatId): array
    {
        try {
            $keys = Cache::getRedis()->keys("*user_typing_*_chat_{$chatId}");
            return collect($keys)->map(function ($key) {
                preg_match('/user_typing_(\d+)_chat_\d+/', $key, $matches);
                return isset($matches[1]) ? (int) $matches[1] : null;
            })->filter()->values()->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get typing users from database cache
     */
    private function getTypingUsersFromDatabase(int $chatId): array
    {
        // For database cache, we can't easily search by pattern
        // This is a limitation of database cache - you might want to use a separate table
        // For now, return empty array as database cache doesn't support pattern matching
        return [];
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
        if ($this->isRedisAvailable()) {
            $this->cleanupExpiredPresenceFromRedis();
        } else {
            $this->cleanupExpiredPresenceFromDatabase();
        }
    }

    /**
     * Clean up expired presence data from Redis
     */
    private function cleanupExpiredPresenceFromRedis(): void
    {
        try {
            $keys = Cache::getRedis()->keys('*user_online_*');

            foreach ($keys as $key) {
                if (!Cache::has($key)) {
                    $userId = str_replace('user_online_', '', $key);
                    Cache::forget("presence_data_{$userId}");
                }
            }
        } catch (\Exception $e) {
            // Handle Redis errors gracefully
        }
    }

    /**
     * Clean up expired presence data from database
     */
    private function cleanupExpiredPresenceFromDatabase(): void
    {
        // For database cache, we rely on TTL expiration
        // No manual cleanup needed as database cache handles expiration automatically
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
     * Get user presence history
     */
    public function getUserPresenceHistory(int $userId, int $days = 7): array
    {
        $cacheKey = "user_presence_history_{$userId}_{$days}";

        return Cache::remember($cacheKey, 3600, function () use ($userId, $days) {
            // This would need to be implemented based on your activity tracking
            // For now, return empty array
            return [];
        });
    }
}

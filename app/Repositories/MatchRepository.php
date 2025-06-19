<?php

namespace App\Repositories;

use App\Models\MatchModel;
use Illuminate\Database\Eloquent\Collection;

class MatchRepository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model(): string
    {
        return MatchModel::class;
    }

    /**
     * Find matches by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function findByUserId(int $userId): Collection
    {
        return $this->findWhere(['user_id' => $userId]);
    }

    /**
     * Find matches by matched user ID
     *
     * @param int $matchedUserId
     * @return Collection
     */
    public function findByMatchedUserId(int $matchedUserId): Collection
    {
        return $this->findWhere(['matched_user_id' => $matchedUserId]);
    }

    /**
     * Find mutual matches (where both users have matched each other)
     *
     * @param int $userId
     * @return Collection
     */
    public function findMutualMatches(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->whereExists(function ($query) use ($userId) {
                $query->select(\DB::raw(1))
                    ->from('matches')
                    ->whereRaw('matches.user_id = matched_user_id')
                    ->whereRaw('matches.matched_user_id = ?', [$userId]);
            })
            ->get();
    }

    /**
     * Check if two users are matched
     *
     * @param int $userId
     * @param int $otherUserId
     * @return bool
     */
    public function areMatched(int $userId, int $otherUserId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('matched_user_id', $otherUserId)
            ->exists();
    }

    /**
     * Check if two users have a mutual match
     *
     * @param int $userId
     * @param int $otherUserId
     * @return bool
     */
    public function haveMutualMatch(int $userId, int $otherUserId): bool
    {
        $userMatched = $this->areMatched($userId, $otherUserId);
        $otherMatched = $this->areMatched($otherUserId, $userId);

        return $userMatched && $otherMatched;
    }

    /**
     * Create a match between two users
     *
     * @param int $userId
     * @param int $matchedUserId
     * @return MatchModel
     */
    public function createMatch(int $userId, int $matchedUserId): MatchModel
    {
        return $this->create([
            'user_id' => $userId,
            'matched_user_id' => $matchedUserId,
            'matched_at' => now()
        ]);
    }

    /**
     * Delete a match between two users
     *
     * @param int $userId
     * @param int $matchedUserId
     * @return bool
     */
    public function deleteMatch(int $userId, int $matchedUserId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('matched_user_id', $matchedUserId)
            ->delete();
    }

    /**
     * Get matches with user information
     *
     * @param int $userId
     * @return Collection
     */
    public function getMatchesWithUserInfo(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('matchedUser.profile')
            ->get();
    }

    /**
     * Get mutual matches with user information
     *
     * @param int $userId
     * @return Collection
     */
    public function getMutualMatchesWithUserInfo(int $userId): Collection
    {
        return $this->findMutualMatches($userId)
            ->load('matchedUser.profile');
    }
}

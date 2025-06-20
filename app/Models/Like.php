<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'liked_user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function likedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'liked_user_id');
    }

    public function createMatchIfMutual(): ?MatchModel
    {
        $mutualLike = Like::where('user_id', $this->liked_user_id)
                         ->where('liked_user_id', $this->user_id)
                         ->exists();

        if ($mutualLike) {
            // Create match for both users
            MatchModel::firstOrCreate([
                'user_id' => $this->user_id,
                'matched_user_id' => $this->liked_user_id
            ]);

            return MatchModel::firstOrCreate([
                'user_id' => $this->liked_user_id,
                'matched_user_id' => $this->user_id
            ]);
        }

        return null;
    }
}

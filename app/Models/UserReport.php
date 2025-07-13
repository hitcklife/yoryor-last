<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_id',
        'reason',
        'description',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who is reporting
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the user being reported
     */
    public function reported(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_id');
    }

    /**
     * Get available report reasons
     */
    public static function getReportReasons(): array
    {
        return [
            'inappropriate_content' => 'Inappropriate Content',
            'harassment' => 'Harassment or Bullying',
            'spam' => 'Spam or Fake Profile',
            'inappropriate_photos' => 'Inappropriate Photos',
            'scam' => 'Scam or Fraud',
            'underage' => 'Underage User',
            'violence' => 'Violence or Threats',
            'hate_speech' => 'Hate Speech',
            'other' => 'Other',
        ];
    }

    /**
     * Check if a user has already reported another user
     */
    public static function hasReported(int $reporterId, int $reportedId): bool
    {
        return static::where('reporter_id', $reporterId)
            ->where('reported_id', $reportedId)
            ->exists();
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for resolved reports
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}

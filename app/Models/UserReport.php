<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'reported_user_id',
        'category',
        'subcategory',
        'description',
        'evidence',
        'incident_details',
        'severity',
        'status',
        'admin_notes',
        'actions_taken',
        'reviewed_by',
        'reviewed_at',
        'resolved_at',
        'is_anonymous',
        'priority_score',
    ];

    protected $casts = [
        'evidence' => 'array',
        'incident_details' => 'array',
        'actions_taken' => 'array',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_anonymous' => 'boolean',
        'priority_score' => 'integer',
    ];

    /**
     * Get the reporter
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /**
     * Get the reported user
     */
    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * Get the admin who reviewed
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get report evidence
     */
    public function reportEvidence(): HasMany
    {
        return $this->hasMany(ReportEvidence::class, 'report_id');
    }

    /**
     * Get category display name
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        return match ($this->category) {
            'inappropriate_behavior' => 'Inappropriate Behavior',
            'harassment' => 'Harassment',
            'fake_profile' => 'Fake Profile',
            'spam' => 'Spam',
            'inappropriate_photos' => 'Inappropriate Photos',
            'scam_attempt' => 'Scam Attempt',
            'hate_speech' => 'Hate Speech',
            'violence_threat' => 'Violence/Threat',
            'underage' => 'Underage User',
            'stolen_photos' => 'Stolen Photos',
            'catfishing' => 'Catfishing',
            'inappropriate_messages' => 'Inappropriate Messages',
            'offline_behavior' => 'Offline Behavior',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->category))
        };
    }

    /**
     * Get subcategory display name
     */
    public function getSubcategoryDisplayNameAttribute(): string
    {
        if (! $this->subcategory) {
            return '';
        }

        return match ($this->subcategory) {
            'sexual_harassment' => 'Sexual Harassment',
            'verbal_abuse' => 'Verbal Abuse',
            'persistent_messaging' => 'Persistent Messaging',
            'fake_photos' => 'Fake Photos',
            'false_information' => 'False Information',
            'impersonation' => 'Impersonation',
            'nudity' => 'Nudity',
            'sexually_explicit' => 'Sexually Explicit Content',
            'violent_content' => 'Violent Content',
            'financial_scam' => 'Financial Scam',
            'romance_scam' => 'Romance Scam',
            'identity_theft' => 'Identity Theft',
            'technical_issue' => 'Technical Issue',
            'policy_violation' => 'Policy Violation',
            'other_reason' => 'Other Reason',
            default => ucfirst(str_replace('_', ' ', $this->subcategory))
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'under_review' => 'Under Review',
            'resolved' => 'Resolved',
            'dismissed' => 'Dismissed',
            'escalated' => 'Escalated',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'under_review' => 'blue',
            'resolved' => 'green',
            'dismissed' => 'gray',
            'escalated' => 'red',
            default => 'gray'
        };
    }

    /**
     * Calculate priority score
     */
    public function calculatePriorityScore(): int
    {
        $score = 0;

        // Base severity score
        $score += match ($this->severity) {
            'low' => 1,
            'medium' => 3,
            'high' => 6,
            'critical' => 10,
            default => 1
        };

        // Category weight
        $score += match ($this->category) {
            'violence_threat', 'harassment', 'hate_speech' => 8,
            'inappropriate_behavior', 'scam_attempt' => 6,
            'fake_profile', 'catfishing' => 4,
            'inappropriate_photos', 'spam' => 3,
            default => 2
        };

        // Evidence weight
        if ($this->evidence && count($this->evidence) > 0) {
            $score += 2;
        }

        // Historical reports against same user
        $reportCount = static::where('reported_user_id', $this->reported_user_id)
            ->where('status', '!=', 'dismissed')
            ->count();

        if ($reportCount > 1) {
            $score += min($reportCount, 5); // Cap at 5 points
        }

        // Age of report (older reports get higher priority)
        $daysSinceReport = $this->created_at->diffInDays(now());
        if ($daysSinceReport > 3) {
            $score += min(floor($daysSinceReport / 3), 3);
        }

        return $score;
    }

    /**
     * Update priority score
     */
    public function updatePriorityScore(): bool
    {
        return $this->update(['priority_score' => $this->calculatePriorityScore()]);
    }

    /**
     * Check if report is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if report is under review
     */
    public function isUnderReview(): bool
    {
        return $this->status === 'under_review';
    }

    /**
     * Check if report is resolved
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'dismissed']);
    }

    /**
     * Get days since submission
     */
    public function getDaysSinceSubmissionAttribute(): int
    {
        return $this->created_at->diffInDays(now());
    }

    /**
     * Scope for pending reports
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for under review reports
     */
    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    /**
     * Scope for high priority reports
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority_score', '>=', 8)
            ->orWhere('severity', 'critical');
    }

    /**
     * Scope for specific category
     */
    public function scopeOfCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific severity
     */
    public function scopeOfSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as under review
     */
    public function markUnderReview(User $reviewer): bool
    {
        return $this->update([
            'status' => 'under_review',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Mark as resolved
     */
    public function markResolved(User $reviewer, array $actionsTaken, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'resolved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'resolved_at' => now(),
            'actions_taken' => $actionsTaken,
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Mark as dismissed
     */
    public function markDismissed(User $reviewer, string $reason): bool
    {
        return $this->update([
            'status' => 'dismissed',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'resolved_at' => now(),
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Escalate report
     */
    public function escalate(User $reviewer, string $reason): bool
    {
        return $this->update([
            'status' => 'escalated',
            'severity' => 'critical',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $reason,
            'priority_score' => max($this->priority_score, 10),
        ]);
    }

    /**
     * Get similar reports
     */
    public function getSimilarReports(int $limit = 5)
    {
        return static::where('id', '!=', $this->id)
            ->where('reported_user_id', $this->reported_user_id)
            ->where('category', $this->category)
            ->latest()
            ->limit($limit)
            ->get();
    }
}

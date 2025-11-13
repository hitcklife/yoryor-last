<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSafetyScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trust_score',
        'report_count',
        'verified_report_count',
        'false_report_count',
        'community_flags',
        'positive_feedback',
        'last_incident_date',
        'risk_category',
        'score_breakdown',
        'last_calculated_at',
    ];

    protected $casts = [
        'trust_score' => 'integer',
        'report_count' => 'integer',
        'verified_report_count' => 'integer',
        'false_report_count' => 'integer',
        'community_flags' => 'integer',
        'positive_feedback' => 'integer',
        'last_incident_date' => 'date',
        'score_breakdown' => 'array',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get trust level based on score
     */
    public function getTrustLevelAttribute(): string
    {
        return match (true) {
            $this->trust_score >= 90 => 'excellent',
            $this->trust_score >= 80 => 'very_good',
            $this->trust_score >= 70 => 'good',
            $this->trust_score >= 60 => 'fair',
            $this->trust_score >= 40 => 'poor',
            default => 'very_poor'
        };
    }

    /**
     * Get trust level display name
     */
    public function getTrustLevelDisplayNameAttribute(): string
    {
        return match ($this->trust_level) {
            'excellent' => 'Excellent',
            'very_good' => 'Very Good',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Poor',
            'very_poor' => 'Very Poor',
            default => 'Unknown'
        };
    }

    /**
     * Get risk category display name
     */
    public function getRiskCategoryDisplayNameAttribute(): string
    {
        return match ($this->risk_category) {
            'low' => 'Low Risk',
            'medium' => 'Medium Risk',
            'high' => 'High Risk',
            'critical' => 'Critical Risk',
            default => 'Unknown Risk'
        };
    }

    /**
     * Get risk category color
     */
    public function getRiskCategoryColorAttribute(): string
    {
        return match ($this->risk_category) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get days since last incident
     */
    public function getDaysSinceLastIncidentAttribute(): ?int
    {
        return $this->last_incident_date 
            ? $this->last_incident_date->diffInDays(now())
            : null;
    }

    /**
     * Calculate comprehensive safety score
     */
    public function calculateSafetyScore(): array
    {
        $baseScore = 100;
        $breakdown = [];

        // Deduct for verified reports
        $reportPenalty = min($this->verified_report_count * 15, 60); // Max 60 points
        $baseScore -= $reportPenalty;
        $breakdown['verified_reports'] = -$reportPenalty;

        // Deduct for community flags
        $flagPenalty = min($this->community_flags * 5, 20); // Max 20 points
        $baseScore -= $flagPenalty;
        $breakdown['community_flags'] = -$flagPenalty;

        // Add points for positive feedback
        $positiveBonus = min($this->positive_feedback * 2, 15); // Max 15 points
        $baseScore += $positiveBonus;
        $breakdown['positive_feedback'] = $positiveBonus;

        // Penalty for false reports they made
        $falseReportPenalty = min($this->false_report_count * 3, 10); // Max 10 points
        $baseScore -= $falseReportPenalty;
        $breakdown['false_reports_made'] = -$falseReportPenalty;

        // Time-based recovery (if no incidents for a while)
        if ($this->last_incident_date && $this->days_since_last_incident > 90) {
            $recoveryBonus = min(floor($this->days_since_last_incident / 30), 10);
            $baseScore += $recoveryBonus;
            $breakdown['time_recovery'] = $recoveryBonus;
        }

        // Ensure score stays within bounds
        $finalScore = max(0, min(100, $baseScore));

        // Determine risk category
        $riskCategory = match (true) {
            $finalScore >= 80 => 'low',
            $finalScore >= 60 => 'medium',
            $finalScore >= 40 => 'high',
            default => 'critical'
        };

        return [
            'trust_score' => $finalScore,
            'risk_category' => $riskCategory,
            'score_breakdown' => $breakdown,
        ];
    }

    /**
     * Update safety score
     */
    public function updateScore(): bool
    {
        $scoreData = $this->calculateSafetyScore();
        
        return $this->update([
            'trust_score' => $scoreData['trust_score'],
            'risk_category' => $scoreData['risk_category'],
            'score_breakdown' => $scoreData['score_breakdown'],
            'last_calculated_at' => now(),
        ]);
    }

    /**
     * Record new report
     */
    public function recordReport(bool $isVerified = false): bool
    {
        $updates = [
            'report_count' => $this->report_count + 1,
            'last_incident_date' => now()->toDateString(),
        ];

        if ($isVerified) {
            $updates['verified_report_count'] = $this->verified_report_count + 1;
        }

        $this->update($updates);
        return $this->updateScore();
    }

    /**
     * Record community flag
     */
    public function recordCommunityFlag(): bool
    {
        $this->update([
            'community_flags' => $this->community_flags + 1,
            'last_incident_date' => now()->toDateString(),
        ]);

        return $this->updateScore();
    }

    /**
     * Record positive feedback
     */
    public function recordPositiveFeedback(): bool
    {
        $this->update([
            'positive_feedback' => $this->positive_feedback + 1,
        ]);

        return $this->updateScore();
    }

    /**
     * Record false report made by user
     */
    public function recordFalseReport(): bool
    {
        $this->update([
            'false_report_count' => $this->false_report_count + 1,
            'last_incident_date' => now()->toDateString(),
        ]);

        return $this->updateScore();
    }

    /**
     * Check if user is high risk
     */
    public function isHighRisk(): bool
    {
        return in_array($this->risk_category, ['high', 'critical']);
    }

    /**
     * Check if user needs review
     */
    public function needsReview(): bool
    {
        return $this->trust_score < 60 || 
               $this->verified_report_count >= 3 ||
               $this->community_flags >= 5;
    }

    /**
     * Scope for high risk users
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_category', ['high', 'critical']);
    }

    /**
     * Scope for low trust score
     */
    public function scopeLowTrust($query, int $threshold = 60)
    {
        return $query->where('trust_score', '<', $threshold);
    }

    /**
     * Scope for users with recent incidents
     */
    public function scopeRecentIncidents($query, int $days = 30)
    {
        return $query->where('last_incident_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for users needing review
     */
    public function scopeNeedsReview($query)
    {
        return $query->where('trust_score', '<', 60)
                     ->orWhere('verified_report_count', '>=', 3)
                     ->orWhere('community_flags', '>=', 5);
    }

    /**
     * Get recommended actions based on score
     */
    public function getRecommendedActions(): array
    {
        $actions = [];

        if ($this->trust_score < 40) {
            $actions[] = [
                'action' => 'account_restriction',
                'priority' => 'high',
                'description' => 'Consider restricting account features'
            ];
        }

        if ($this->verified_report_count >= 5) {
            $actions[] = [
                'action' => 'manual_review',
                'priority' => 'high',
                'description' => 'Requires immediate manual review'
            ];
        }

        if ($this->community_flags >= 10) {
            $actions[] = [
                'action' => 'community_warning',
                'priority' => 'medium',
                'description' => 'Issue community guidelines warning'
            ];
        }

        if ($this->trust_score >= 80 && $this->positive_feedback >= 10) {
            $actions[] = [
                'action' => 'trusted_user',
                'priority' => 'low',
                'description' => 'Consider for trusted user program'
            ];
        }

        return $actions;
    }
}
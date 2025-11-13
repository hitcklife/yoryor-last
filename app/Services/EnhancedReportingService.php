<?php

namespace App\Services;

use App\Models\AutomatedSafetyFlag;
use App\Models\ReportEvidence;
use App\Models\User;
use App\Models\UserReport;
use App\Models\UserSafetyScore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnhancedReportingService
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Submit an enhanced report
     */
    public function submitReport(
        User $reporter,
        User $reportedUser,
        string $category,
        string $description,
        ?string $subcategory = null,
        array $evidence = [],
        array $incidentDetails = [],
        bool $isAnonymous = false
    ): array {
        try {
            // Check for spam reporting
            if ($this->isSpamReporting($reporter, $reportedUser)) {
                return [
                    'success' => false,
                    'error' => 'You have already reported this user recently',
                ];
            }

            $report = DB::transaction(function () use (
                $reporter,
                $reportedUser,
                $category,
                $description,
                $subcategory,
                $evidence,
                $incidentDetails,
                $isAnonymous
            ) {
                // Determine severity based on category
                $severity = $this->determineSeverity($category, $subcategory);

                // Create the report
                $report = UserReport::create([
                    'reporter_id' => $reporter->id,
                    'reported_user_id' => $reportedUser->id,
                    'category' => $category,
                    'subcategory' => $subcategory,
                    'description' => $description,
                    'incident_details' => $incidentDetails,
                    'severity' => $severity,
                    'is_anonymous' => $isAnonymous,
                ]);

                // Store evidence files
                $this->storeReportEvidence($report, $evidence);

                // Calculate and update priority score
                $report->updatePriorityScore();

                // Update reported user's safety score
                $this->updateUserSafetyScore($reportedUser, $report);

                // Auto-escalate critical reports
                if ($severity === 'critical') {
                    $this->autoEscalateReport($report);
                }

                return $report;
            });

            // Send notifications
            $this->sendReportNotifications($report);

            return [
                'success' => true,
                'report' => $report,
                'message' => 'Report submitted successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to submit enhanced report', [
                'error' => $e->getMessage(),
                'reporter_id' => $reporter->id,
                'reported_user_id' => $reportedUser->id,
                'category' => $category,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to submit report',
            ];
        }
    }

    /**
     * Review a report
     */
    public function reviewReport(
        UserReport $report,
        User $reviewer,
        string $action,
        array $actionDetails = []
    ): bool {
        try {
            DB::transaction(function () use ($report, $reviewer, $action, $actionDetails) {
                switch ($action) {
                    case 'resolve':
                        $report->markResolved(
                            $reviewer,
                            $actionDetails['actions_taken'] ?? [],
                            $actionDetails['notes'] ?? null
                        );

                        // Apply actions to reported user
                        $this->applyReportActions($report, $actionDetails['actions_taken'] ?? []);
                        break;

                    case 'dismiss':
                        $report->markDismissed($reviewer, $actionDetails['reason'] ?? 'No violation found');

                        // Record false report if dismissed
                        $this->recordFalseReport($report);
                        break;

                    case 'escalate':
                        $report->escalate($reviewer, $actionDetails['reason'] ?? 'Requires senior review');
                        break;

                    case 'under_review':
                        $report->markUnderReview($reviewer);
                        break;
                }

                // Update user safety scores
                $this->updateUserSafetyScore($report->reportedUser, $report);
            });

            // Send notifications
            $this->sendReviewNotifications($report, $action);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to review report', [
                'error' => $e->getMessage(),
                'report_id' => $report->id,
                'action' => $action,
            ]);

            return false;
        }
    }

    /**
     * Get reporting dashboard data
     */
    public function getDashboardData(): array
    {
        $today = now()->startOfDay();
        $lastWeek = now()->subWeek();
        $lastMonth = now()->subMonth();

        return [
            'pending_reports' => UserReport::pending()->count(),
            'high_priority_reports' => UserReport::highPriority()->pending()->count(),
            'reports_today' => UserReport::where('created_at', '>=', $today)->count(),
            'reports_this_week' => UserReport::where('created_at', '>=', $lastWeek)->count(),
            'reports_this_month' => UserReport::where('created_at', '>=', $lastMonth)->count(),
            'category_breakdown' => $this->getCategoryBreakdown(),
            'severity_breakdown' => $this->getSeverityBreakdown(),
            'high_risk_users' => $this->getHighRiskUsers(),
            'trending_issues' => $this->getTrendingIssues(),
        ];
    }

    /**
     * Get user safety overview
     */
    public function getUserSafetyOverview(User $user): array
    {
        $safetyScore = $this->getOrCreateSafetyScore($user);
        $recentReports = UserReport::where('reported_user_id', $user->id)
            ->recent()
            ->with(['reporter', 'reviewedBy'])
            ->get();

        $automatedFlags = AutomatedSafetyFlag::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        return [
            'safety_score' => $safetyScore,
            'recent_reports' => $recentReports,
            'automated_flags' => $automatedFlags,
            'account_status' => $user->account_status,
            'restrictions' => $user->safety_restrictions,
            'recommendations' => $safetyScore->getRecommendedActions(),
        ];
    }

    /**
     * Determine severity based on category and subcategory
     */
    private function determineSeverity(string $category, ?string $subcategory): string
    {
        // Critical severity categories
        $criticalCategories = [
            'violence_threat',
            'harassment',
            'hate_speech',
            'underage',
        ];

        if (in_array($category, $criticalCategories)) {
            return 'critical';
        }

        // High severity
        $highCategories = [
            'scam_attempt',
            'inappropriate_behavior',
            'catfishing',
        ];

        if (in_array($category, $highCategories)) {
            return 'high';
        }

        // Medium severity based on subcategory
        $highSubcategories = [
            'sexual_harassment',
            'financial_scam',
            'identity_theft',
        ];

        if ($subcategory && in_array($subcategory, $highSubcategories)) {
            return 'high';
        }

        // Default to medium
        return 'medium';
    }

    /**
     * Store report evidence
     */
    private function storeReportEvidence(UserReport $report, array $evidence): void
    {
        foreach ($evidence as $evidenceData) {
            if (isset($evidenceData['file']) && $evidenceData['file']->isValid()) {
                $file = $evidenceData['file'];
                $path = $file->store("reports/{$report->id}", 'private');

                ReportEvidence::create([
                    'report_id' => $report->id,
                    'evidence_type' => $evidenceData['type'] ?? 'document',
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $evidenceData['description'] ?? null,
                    'metadata' => $evidenceData['metadata'] ?? [],
                ]);
            }
        }
    }

    /**
     * Update user safety score
     */
    private function updateUserSafetyScore(User $user, UserReport $report): void
    {
        $safetyScore = $this->getOrCreateSafetyScore($user);

        if ($report->status === 'resolved') {
            $safetyScore->recordReport(true);
        } elseif ($report->status === 'pending') {
            $safetyScore->recordReport(false);
        }

        // Update user account status based on safety score
        $this->updateUserAccountStatus($user, $safetyScore);
    }

    /**
     * Get or create safety score
     */
    private function getOrCreateSafetyScore(User $user): UserSafetyScore
    {
        return UserSafetyScore::firstOrCreate(
            ['user_id' => $user->id],
            [
                'trust_score' => 100,
                'risk_category' => 'low',
                'last_calculated_at' => now(),
            ]
        );
    }

    /**
     * Update user account status
     */
    private function updateUserAccountStatus(User $user, UserSafetyScore $safetyScore): void
    {
        $newStatus = match (true) {
            $safetyScore->trust_score < 20 => 'banned',
            $safetyScore->trust_score < 40 => 'suspended',
            $safetyScore->trust_score < 60 => 'restricted',
            $safetyScore->verified_report_count >= 3 => 'warning',
            default => 'active'
        };

        if ($user->account_status !== $newStatus) {
            $user->update([
                'account_status' => $newStatus,
                'safety_score' => $safetyScore->trust_score,
                'is_flagged' => $safetyScore->isHighRisk(),
                'last_safety_check' => now(),
            ]);

            // Send notification about status change
            $this->notificationService->notifyUserOfAccountStatusChange($user, $newStatus);
        }
    }

    /**
     * Check if this is spam reporting
     */
    private function isSpamReporting(User $reporter, User $reportedUser): bool
    {
        $recentReports = UserReport::where('reporter_id', $reporter->id)
            ->where('reported_user_id', $reportedUser->id)
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        return $recentReports > 0;
    }

    /**
     * Auto-escalate critical reports
     */
    private function autoEscalateReport(UserReport $report): void
    {
        $report->update([
            'status' => 'escalated',
            'priority_score' => 10,
        ]);
    }

    /**
     * Apply actions to reported user
     */
    private function applyReportActions(UserReport $report, array $actions): void
    {
        $user = $report->reportedUser;

        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'warning':
                    $this->issueWarning($user, $action['details'] ?? '');
                    break;

                case 'restrict_messaging':
                    $this->restrictMessaging($user, $action['duration'] ?? 24);
                    break;

                case 'restrict_photos':
                    $this->restrictPhotos($user, $action['duration'] ?? 24);
                    break;

                case 'suspend_account':
                    $this->suspendAccount($user, $action['duration'] ?? 24);
                    break;

                case 'ban_account':
                    $this->banAccount($user, $action['reason'] ?? '');
                    break;
            }
        }
    }

    /**
     * Send report notifications
     */
    private function sendReportNotifications(UserReport $report): void
    {
        // Notify admins of new report
        $this->notificationService->notifyAdminsOfNewReport($report);

        // Notify reporter of submission
        if (! $report->is_anonymous) {
            $this->notificationService->notifyReporterOfSubmission($report->reporter, $report);
        }
    }

    /**
     * Send review notifications
     */
    private function sendReviewNotifications(UserReport $report, string $action): void
    {
        if (! $report->is_anonymous) {
            $this->notificationService->notifyReporterOfReview($report->reporter, $report, $action);
        }

        if (in_array($action, ['resolve', 'dismiss']) && $report->actions_taken) {
            $this->notificationService->notifyUserOfReportAction($report->reportedUser, $report);
        }
    }

    /**
     * Record false report
     */
    private function recordFalseReport(UserReport $report): void
    {
        $reporterSafetyScore = $this->getOrCreateSafetyScore($report->reporter);
        $reporterSafetyScore->recordFalseReport();
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown(): array
    {
        return UserReport::recent()
            ->groupBy('category')
            ->selectRaw('category, COUNT(*) as count')
            ->pluck('count', 'category')
            ->toArray();
    }

    /**
     * Get severity breakdown
     */
    private function getSeverityBreakdown(): array
    {
        return UserReport::recent()
            ->groupBy('severity')
            ->selectRaw('severity, COUNT(*) as count')
            ->pluck('count', 'severity')
            ->toArray();
    }

    /**
     * Get high risk users
     */
    private function getHighRiskUsers(int $limit = 10): array
    {
        return UserSafetyScore::highRisk()
            ->with('user')
            ->orderBy('trust_score')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get trending issues
     */
    private function getTrendingIssues(): array
    {
        $lastWeek = now()->subWeek();
        $twoWeeksAgo = now()->subWeeks(2);

        $currentWeek = UserReport::where('created_at', '>=', $lastWeek)
            ->groupBy('category')
            ->selectRaw('category, COUNT(*) as count')
            ->pluck('count', 'category');

        $previousWeek = UserReport::whereBetween('created_at', [$twoWeeksAgo, $lastWeek])
            ->groupBy('category')
            ->selectRaw('category, COUNT(*) as count')
            ->pluck('count', 'category');

        $trends = [];
        foreach ($currentWeek as $category => $currentCount) {
            $previousCount = $previousWeek[$category] ?? 0;
            $change = $previousCount > 0 ? (($currentCount - $previousCount) / $previousCount) * 100 : 100;

            $trends[] = [
                'category' => $category,
                'current_count' => $currentCount,
                'previous_count' => $previousCount,
                'change_percentage' => round($change, 1),
                'trending' => $change > 20 ? 'up' : ($change < -20 ? 'down' : 'stable'),
            ];
        }

        // Sort by change percentage
        usort($trends, fn ($a, $b) => $b['change_percentage'] <=> $a['change_percentage']);

        return array_slice($trends, 0, 5);
    }

    /**
     * Issue warning to user
     */
    private function issueWarning(User $user, string $details): void
    {
        // Implementation for issuing warnings
        // This could involve updating user status, sending notifications, etc.
    }

    /**
     * Restrict messaging for user
     */
    private function restrictMessaging(User $user, int $hours): void
    {
        $restrictions = $user->safety_restrictions ?? [];
        $restrictions['messaging_restricted_until'] = now()->addHours($hours)->toISOString();

        $user->update(['safety_restrictions' => $restrictions]);
    }

    /**
     * Restrict photos for user
     */
    private function restrictPhotos(User $user, int $hours): void
    {
        $restrictions = $user->safety_restrictions ?? [];
        $restrictions['photo_upload_restricted_until'] = now()->addHours($hours)->toISOString();

        $user->update(['safety_restrictions' => $restrictions]);
    }

    /**
     * Suspend account
     */
    private function suspendAccount(User $user, int $hours): void
    {
        $user->update([
            'account_status' => 'suspended',
            'safety_restrictions' => [
                'suspended_until' => now()->addHours($hours)->toISOString(),
                'reason' => 'Account suspended due to community guidelines violation',
            ],
        ]);
    }

    /**
     * Ban account
     */
    private function banAccount(User $user, string $reason): void
    {
        $user->update([
            'account_status' => 'banned',
            'safety_restrictions' => [
                'banned_at' => now()->toISOString(),
                'reason' => $reason,
            ],
        ]);
    }
}

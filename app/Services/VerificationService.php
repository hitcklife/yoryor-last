<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVerifiedBadge;
use App\Models\VerificationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VerificationService
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Submit verification request
     */
    public function submitVerificationRequest(
        User $user,
        string $verificationType,
        array $documents,
        array $submittedData,
        ?string $userNotes = null
    ): array {
        try {
            // Check if user already has pending request for this type
            $existingRequest = VerificationRequest::where('user_id', $user->id)
                ->where('verification_type', $verificationType)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return [
                    'success' => false,
                    'error' => 'You already have a pending verification request for this type',
                ];
            }

            $verificationRequest = DB::transaction(function () use (
                $user,
                $verificationType,
                $documents,
                $submittedData,
                $userNotes
            ) {
                // Store uploaded documents
                $storedDocuments = $this->storeDocuments($documents, $user->id);

                // Create verification request
                $request = VerificationRequest::create([
                    'user_id' => $user->id,
                    'verification_type' => $verificationType,
                    'status' => 'pending',
                    'documents' => $storedDocuments,
                    'submitted_data' => $submittedData,
                    'user_notes' => $userNotes,
                    'submitted_at' => now(),
                ]);

                // Create or update badge with pending status
                UserVerifiedBadge::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'badge_type' => $this->mapVerificationTypeToBadge($verificationType),
                    ],
                    [
                        'status' => 'pending',
                        'verification_data' => $submittedData,
                    ]
                );

                return $request;
            });

            // Notify admins of new verification request
            $this->notificationService->notifyAdminsOfVerificationRequest($verificationRequest);

            return [
                'success' => true,
                'request' => $verificationRequest,
                'message' => 'Verification request submitted successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to submit verification request', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'verification_type' => $verificationType,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to submit verification request',
            ];
        }
    }

    /**
     * Approve verification request
     */
    public function approveVerificationRequest(
        VerificationRequest $request,
        User $reviewer,
        ?string $feedback = null
    ): bool {
        try {
            DB::transaction(function () use ($request, $reviewer, $feedback) {
                // Approve the request
                $request->approve($reviewer, $feedback);

                // Update or create the badge
                $badgeType = $this->mapVerificationTypeToBadge($request->verification_type);
                $expirationDate = $this->getBadgeExpirationDate($badgeType);

                $badge = UserVerifiedBadge::updateOrCreate(
                    [
                        'user_id' => $request->user_id,
                        'badge_type' => $badgeType,
                    ],
                    [
                        'status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $reviewer->id,
                        'expires_at' => $expirationDate,
                        'admin_notes' => $feedback,
                    ]
                );

                // Update user verification score
                $this->updateUserVerificationScore($request->user);

                // Auto-verify phone/email if applicable
                $this->autoVerifyBasicFields($request->user, $badgeType);
            });

            // Send notification to user
            $this->notificationService->notifyUserOfVerificationApproval($request->user, $request);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to approve verification request', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);

            return false;
        }
    }

    /**
     * Reject verification request
     */
    public function rejectVerificationRequest(
        VerificationRequest $request,
        User $reviewer,
        string $reason
    ): bool {
        try {
            DB::transaction(function () use ($request, $reviewer, $reason) {
                // Reject the request
                $request->reject($reviewer, $reason);

                // Update badge status
                $badgeType = $this->mapVerificationTypeToBadge($request->verification_type);
                UserVerifiedBadge::updateOrCreate(
                    [
                        'user_id' => $request->user_id,
                        'badge_type' => $badgeType,
                    ],
                    [
                        'status' => 'rejected',
                        'verified_by' => $reviewer->id,
                        'admin_notes' => $reason,
                    ]
                );
            });

            // Send notification to user
            $this->notificationService->notifyUserOfVerificationRejection($request->user, $request, $reason);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to reject verification request', [
                'error' => $e->getMessage(),
                'request_id' => $request->id,
            ]);

            return false;
        }
    }

    /**
     * Get user's verification status
     */
    public function getUserVerificationStatus(User $user): array
    {
        $badges = $user->verifiedBadges()->get();
        $pendingRequests = $user->verificationRequests()->pending()->get();

        $verificationScore = $this->calculateVerificationScore($user);
        $completionPercentage = $this->calculateCompletionPercentage($user);

        return [
            'verification_score' => $verificationScore,
            'completion_percentage' => $completionPercentage,
            'is_verified' => $user->is_verified,
            'is_premium_verified' => $user->is_premium_verified,
            'badges' => $badges->groupBy('status'),
            'pending_requests' => $pendingRequests,
            'available_verifications' => $this->getAvailableVerifications($user),
            'next_recommendations' => $this->getVerificationRecommendations($user),
        ];
    }

    /**
     * Update user verification score
     */
    public function updateUserVerificationScore(User $user): int
    {
        $score = $this->calculateVerificationScore($user);
        
        $user->update([
            'verification_score' => $score,
            'is_verified' => $score >= 30, // Basic verification threshold
            'is_premium_verified' => $score >= 70, // Premium verification threshold
            'last_verification_check' => now(),
        ]);

        return $score;
    }

    /**
     * Calculate verification score
     */
    private function calculateVerificationScore(User $user): int
    {
        return $user->verifiedBadges()
            ->active()
            ->get()
            ->sum('score_value');
    }

    /**
     * Calculate completion percentage
     */
    private function calculateCompletionPercentage(User $user): float
    {
        $totalPossibleScore = 160; // Sum of all possible badge scores
        $currentScore = $this->calculateVerificationScore($user);
        
        return round(($currentScore / $totalPossibleScore) * 100, 1);
    }

    /**
     * Get available verifications for user
     */
    private function getAvailableVerifications(User $user): array
    {
        $existingBadges = $user->verifiedBadges()->pluck('badge_type')->toArray();
        $pendingTypes = $user->verificationRequests()
            ->pending()
            ->pluck('verification_type')
            ->map(fn($type) => $this->mapVerificationTypeToBadge($type))
            ->toArray();

        $allTypes = [
            'phone_verified',
            'email_verified',
            'identity_verified',
            'photo_verified',
            'employment_verified',
            'education_verified',
            'income_verified',
            'address_verified',
            'social_verified',
            'background_check',
        ];

        $availableTypes = array_diff($allTypes, $existingBadges, $pendingTypes);

        return array_map(function ($type) {
            $requirements = UserVerifiedBadge::getRequirementsForType($type);
            return [
                'badge_type' => $type,
                'display_name' => (new UserVerifiedBadge(['badge_type' => $type]))->badge_display_name,
                'score_value' => (new UserVerifiedBadge(['badge_type' => $type]))->score_value,
                'requirements' => $requirements,
            ];
        }, $availableTypes);
    }

    /**
     * Get verification recommendations
     */
    private function getVerificationRecommendations(User $user): array
    {
        $currentScore = $this->calculateVerificationScore($user);
        $recommendations = [];

        if ($currentScore < 30) {
            $recommendations[] = [
                'priority' => 'high',
                'title' => 'Get Basic Verification',
                'description' => 'Complete phone and email verification to unlock more features',
                'badges' => ['phone_verified', 'email_verified'],
            ];
        }

        if ($currentScore >= 30 && $currentScore < 50) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Verify Your Identity',
                'description' => 'Add identity verification to increase trust and matches',
                'badges' => ['identity_verified', 'photo_verified'],
            ];
        }

        if ($currentScore >= 50 && $currentScore < 70) {
            $recommendations[] = [
                'priority' => 'medium',
                'title' => 'Professional Verification',
                'description' => 'Verify your employment or education for premium features',
                'badges' => ['employment_verified', 'education_verified'],
            ];
        }

        if ($currentScore >= 70) {
            $recommendations[] = [
                'priority' => 'low',
                'title' => 'Complete Verification',
                'description' => 'Add background check for maximum trust and premium features',
                'badges' => ['background_check'],
            ];
        }

        return $recommendations;
    }

    /**
     * Store uploaded documents
     */
    private function storeDocuments(array $documents, int $userId): array
    {
        $storedDocuments = [];

        foreach ($documents as $key => $file) {
            if ($file && $file->isValid()) {
                $path = $file->store("verification/{$userId}", 'private');
                $storedDocuments[$key] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ];
            }
        }

        return $storedDocuments;
    }

    /**
     * Map verification type to badge type
     */
    private function mapVerificationTypeToBadge(string $verificationType): string
    {
        return match ($verificationType) {
            'identity' => 'identity_verified',
            'photo' => 'photo_verified',
            'employment' => 'employment_verified',
            'education' => 'education_verified',
            'income' => 'income_verified',
            'address' => 'address_verified',
            'social_media' => 'social_verified',
            'background_check' => 'background_check',
            default => $verificationType . '_verified'
        };
    }

    /**
     * Get badge expiration date
     */
    private function getBadgeExpirationDate(string $badgeType): ?Carbon
    {
        return match ($badgeType) {
            'identity_verified', 'background_check' => now()->addYear(),
            'employment_verified', 'income_verified' => now()->addMonths(6),
            'photo_verified' => now()->addMonths(3),
            default => null // Never expires
        };
    }

    /**
     * Auto-verify basic fields
     */
    private function autoVerifyBasicFields(User $user, string $badgeType): void
    {
        // Auto-verify phone if identity is verified
        if ($badgeType === 'identity_verified' && $user->phone_verified_at) {
            UserVerifiedBadge::updateOrCreate(
                ['user_id' => $user->id, 'badge_type' => 'phone_verified'],
                ['status' => 'verified', 'verified_at' => now()]
            );
        }

        // Auto-verify email if identity is verified
        if ($badgeType === 'identity_verified' && $user->email_verified_at) {
            UserVerifiedBadge::updateOrCreate(
                ['user_id' => $user->id, 'badge_type' => 'email_verified'],
                ['status' => 'verified', 'verified_at' => now()]
            );
        }
    }

    /**
     * Process expired badges
     */
    public function processExpiredBadges(): void
    {
        $expiredBadges = UserVerifiedBadge::where('status', 'verified')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBadges as $badge) {
            $badge->update(['status' => 'expired']);
            
            // Update user verification score
            $this->updateUserVerificationScore($badge->user);
            
            // Notify user of expiration
            $this->notificationService->notifyUserOfBadgeExpiration($badge->user, $badge);
        }
    }
}
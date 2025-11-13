<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyMember;
use App\Models\FamilyApproval;
use App\Models\FamilyApprovalSetting;
use App\Models\FamilyActivityLog;
use App\Notifications\FamilyApprovalRequestNotification;
use App\Notifications\FamilyApprovalDecisionNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FamilyApprovalService
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Invite a family member
     */
    public function inviteFamilyMember(User $user, array $data): array
    {
        try {
            // Check if already has max family members (e.g., 5)
            if ($user->familyMembers()->count() >= 5) {
                return [
                    'success' => false,
                    'error' => 'Maximum family members limit reached',
                ];
            }

            // Create family member account if email provided
            $familyUser = null;
            if (isset($data['email'])) {
                $familyUser = User::where('email', $data['email'])->first();
                
                if (!$familyUser) {
                    // Create a new user account for family member
                    $familyUser = User::create([
                        'email' => $data['email'],
                        'password' => bcrypt(Str::random(16)), // Random password, they'll reset
                        'is_family_member' => true,
                        'registration_completed' => false,
                    ]);
                }
            }

            if (!$familyUser) {
                return [
                    'success' => false,
                    'error' => 'Failed to create family member account',
                ];
            }

            // Check if already linked
            if ($user->familyMembers()->where('family_user_id', $familyUser->id)->exists()) {
                return [
                    'success' => false,
                    'error' => 'This person is already linked as a family member',
                ];
            }

            // Create family member link
            $familyMember = FamilyMember::create([
                'user_id' => $user->id,
                'family_user_id' => $familyUser->id,
                'relationship' => $data['relationship'],
                'relationship_detail' => $data['relationship_detail'] ?? null,
                'can_approve_matches' => $data['can_approve_matches'] ?? true,
                'can_view_chats' => $data['can_view_chats'] ?? false,
                'can_block_users' => $data['can_block_users'] ?? true,
                'status' => 'pending',
                'invited_at' => now(),
            ]);

            // Send invitation notification
            $this->notificationService->sendFamilyInvitation($familyUser, $user, $familyMember);

            // Log activity
            $this->logActivity($familyMember->id, $user->id, 'invited_family_member', null, [
                'email' => $data['email'],
                'relationship' => $data['relationship'],
            ]);

            return [
                'success' => true,
                'family_member' => $familyMember,
                'message' => 'Family member invited successfully',
            ];

        } catch (\Exception $e) {
            Log::error('Failed to invite family member', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'data' => $data,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to invite family member',
            ];
        }
    }

    /**
     * Accept family member invitation
     */
    public function acceptInvitation(User $familyUser, int $userId): bool
    {
        try {
            $familyMember = FamilyMember::where('user_id', $userId)
                ->where('family_user_id', $familyUser->id)
                ->where('status', 'pending')
                ->first();

            if (!$familyMember) {
                return false;
            }

            $familyMember->update([
                'status' => 'active',
                'accepted_at' => now(),
            ]);

            // Update family user flags
            $familyUser->update([
                'is_family_member' => true,
            ]);

            // Notify main user
            $this->notificationService->sendFamilyAcceptanceNotification(
                User::find($userId),
                $familyUser,
                $familyMember
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to accept family invitation', [
                'error' => $e->getMessage(),
                'family_user_id' => $familyUser->id,
                'user_id' => $userId,
            ]);

            return false;
        }
    }

    /**
     * Request family approval for a match
     */
    public function requestApproval(User $user, User $matchUser): array
    {
        try {
            // Check if family approval is enabled
            if (!$user->family_approval_enabled) {
                return [
                    'success' => true,
                    'required' => false,
                ];
            }

            // Get active family members
            $familyMembers = $user->familyMembers()
                ->where('status', 'active')
                ->where('can_approve_matches', true)
                ->get();

            if ($familyMembers->isEmpty()) {
                return [
                    'success' => true,
                    'required' => false,
                ];
            }

            $settings = $this->getFamilySettings($user);
            $expiresAt = now()->addHours($settings->approval_timeout_hours);

            // Create approval requests
            $approvals = [];
            foreach ($familyMembers as $familyMember) {
                $approval = FamilyApproval::create([
                    'user_id' => $user->id,
                    'match_user_id' => $matchUser->id,
                    'family_member_id' => $familyMember->id,
                    'status' => 'pending',
                    'expires_at' => $expiresAt,
                ]);

                $approvals[] = $approval;

                // Send notification to family member
                $this->notificationService->sendFamilyApprovalRequest(
                    $familyMember->familyUser,
                    $user,
                    $matchUser,
                    $approval
                );
            }

            // Update match status
            if ($user->matches()->where('matched_user_id', $matchUser->id)->exists()) {
                $user->matches()
                    ->where('matched_user_id', $matchUser->id)
                    ->update(['family_approval_status' => 'pending']);
            }

            return [
                'success' => true,
                'required' => true,
                'approvals' => $approvals,
                'expires_at' => $expiresAt,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to request family approval', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'match_user_id' => $matchUser->id,
            ]);

            return [
                'success' => false,
                'error' => 'Failed to request family approval',
            ];
        }
    }

    /**
     * Process family member decision
     */
    public function processDecision(
        FamilyMember $familyMember,
        FamilyApproval $approval,
        string $decision,
        ?string $reason = null,
        ?string $notes = null
    ): bool {
        try {
            DB::transaction(function () use ($familyMember, $approval, $decision, $reason, $notes) {
                // Update approval
                $approval->update([
                    'status' => $decision,
                    'reason' => $reason,
                    'notes' => $notes,
                    'decided_at' => now(),
                ]);

                // Log activity
                $this->logActivity(
                    $familyMember->id,
                    $approval->user_id,
                    $decision === 'approved' ? 'approved_match' : 'rejected_match',
                    $approval->match_user_id,
                    [
                        'reason' => $reason,
                        'notes' => $notes,
                    ]
                );

                // Check if all required approvals are met
                $this->checkApprovalCompletion($approval->user_id, $approval->match_user_id);

                // Notify user of decision
                $this->notificationService->sendFamilyDecisionNotification(
                    User::find($approval->user_id),
                    $familyMember->familyUser,
                    User::find($approval->match_user_id),
                    $decision,
                    $reason
                );
            });

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to process family decision', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'decision' => $decision,
            ]);

            return false;
        }
    }

    /**
     * Check if approval requirements are met
     */
    private function checkApprovalCompletion(int $userId, int $matchUserId): void
    {
        $user = User::find($userId);
        $settings = $this->getFamilySettings($user);

        $approvals = FamilyApproval::where('user_id', $userId)
            ->where('match_user_id', $matchUserId)
            ->get();

        $approvedCount = $approvals->where('status', 'approved')->count();
        $rejectedCount = $approvals->where('status', 'rejected')->count();
        $pendingCount = $approvals->where('status', 'pending')->count();

        // If any rejection, mark as rejected
        if ($rejectedCount > 0) {
            $this->updateMatchApprovalStatus($userId, $matchUserId, 'rejected');
            return;
        }

        // If enough approvals, mark as approved
        if ($approvedCount >= $settings->min_approvals_required) {
            $this->updateMatchApprovalStatus($userId, $matchUserId, 'approved');
            return;
        }

        // If no pending and not enough approvals, mark as rejected
        if ($pendingCount === 0 && $approvedCount < $settings->min_approvals_required) {
            $this->updateMatchApprovalStatus($userId, $matchUserId, 'rejected');
        }
    }

    /**
     * Update match approval status
     */
    private function updateMatchApprovalStatus(int $userId, int $matchUserId, string $status): void
    {
        $match = DB::table('matches')
            ->where('user_id', $userId)
            ->where('matched_user_id', $matchUserId)
            ->first();

        if ($match) {
            DB::table('matches')
                ->where('id', $match->id)
                ->update([
                    'family_approval_status' => $status,
                    'family_approved_at' => $status === 'approved' ? now() : null,
                ]);
        }
    }

    /**
     * Get family approval settings
     */
    public function getFamilySettings(User $user): FamilyApprovalSetting
    {
        return FamilyApprovalSetting::firstOrCreate(
            ['user_id' => $user->id],
            [
                'require_approval_before_chat' => false,
                'require_approval_before_meeting' => true,
                'approval_timeout_hours' => 72,
                'min_approvals_required' => 1,
                'notify_family_on_match' => true,
                'notify_family_on_first_message' => true,
                'show_family_approved_badge' => true,
            ]
        );
    }

    /**
     * Check if user has family approval for match
     */
    public function hasApproval(User $user, User $matchUser): bool
    {
        if (!$user->family_approval_enabled) {
            return true;
        }

        $match = $user->matches()
            ->where('matched_user_id', $matchUser->id)
            ->first();

        if (!$match) {
            return false;
        }

        return $match->family_approval_status === 'approved' ||
               $match->family_approval_status === 'not_required';
    }

    /**
     * Get pending approvals for family member
     */
    public function getPendingApprovals(User $familyUser): array
    {
        $familyMembers = FamilyMember::where('family_user_id', $familyUser->id)
            ->where('status', 'active')
            ->pluck('id');

        $approvals = FamilyApproval::whereIn('family_member_id', $familyMembers)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })
            ->with(['user.profile', 'matchUser.profile', 'matchUser.photos'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $approvals->map(function ($approval) {
            return [
                'id' => $approval->id,
                'user' => [
                    'id' => $approval->user->id,
                    'name' => $approval->user->profile->full_name,
                    'photo' => $approval->user->profilePhotoUrl,
                ],
                'match_user' => [
                    'id' => $approval->matchUser->id,
                    'name' => $approval->matchUser->profile->full_name,
                    'age' => $approval->matchUser->profile->age,
                    'bio' => $approval->matchUser->profile->bio,
                    'location' => $approval->matchUser->profile->location,
                    'photos' => $approval->matchUser->photos->map(function ($photo) {
                        return [
                            'id' => $photo->id,
                            'url' => $photo->medium_url,
                        ];
                    }),
                ],
                'created_at' => $approval->created_at,
                'expires_at' => $approval->expires_at,
            ];
        })->toArray();
    }

    /**
     * Log family member activity
     */
    private function logActivity(
        int $familyMemberId,
        int $userId,
        string $action,
        ?int $targetUserId = null,
        ?array $metadata = null
    ): void {
        FamilyActivityLog::create([
            'family_member_id' => $familyMemberId,
            'user_id' => $userId,
            'action' => $action,
            'target_user_id' => $targetUserId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Handle expired approvals
     */
    public function handleExpiredApprovals(): void
    {
        $expired = FamilyApproval::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $approval) {
            $approval->update(['status' => 'expired']);
            
            // Auto-approve if settings allow
            $user = User::find($approval->user_id);
            $settings = $this->getFamilySettings($user);
            
            if ($settings->approval_timeout_hours > 0) {
                $this->checkApprovalCompletion($approval->user_id, $approval->match_user_id);
            }
        }
    }

    /**
     * Get family member dashboard stats
     */
    public function getFamilyDashboardStats(User $familyUser): array
    {
        $familyMembers = FamilyMember::where('family_user_id', $familyUser->id)
            ->where('status', 'active')
            ->get();

        $stats = [
            'total_users_monitoring' => $familyMembers->count(),
            'pending_approvals' => 0,
            'approved_today' => 0,
            'rejected_today' => 0,
            'users' => [],
        ];

        foreach ($familyMembers as $member) {
            $pendingCount = FamilyApproval::where('family_member_id', $member->id)
                ->where('status', 'pending')
                ->count();

            $approvedToday = FamilyApproval::where('family_member_id', $member->id)
                ->where('status', 'approved')
                ->whereDate('decided_at', today())
                ->count();

            $rejectedToday = FamilyApproval::where('family_member_id', $member->id)
                ->where('status', 'rejected')
                ->whereDate('decided_at', today())
                ->count();

            $stats['pending_approvals'] += $pendingCount;
            $stats['approved_today'] += $approvedToday;
            $stats['rejected_today'] += $rejectedToday;

            $stats['users'][] = [
                'user' => [
                    'id' => $member->user->id,
                    'name' => $member->user->profile->full_name,
                    'photo' => $member->user->profilePhotoUrl,
                ],
                'relationship' => $member->relationship_detail ?: $member->relationship,
                'pending_approvals' => $pendingCount,
                'permissions' => [
                    'can_approve_matches' => $member->can_approve_matches,
                    'can_view_chats' => $member->can_view_chats,
                    'can_block_users' => $member->can_block_users,
                ],
            ];
        }

        return $stats;
    }
}
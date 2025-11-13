<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifiedBadgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'verified_badges',
            'id' => (string) $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'badge_type' => $this->badge_type,
                'status' => $this->status,
                'badge_display_name' => $this->badge_display_name,
                'badge_icon' => $this->badge_icon,
                'badge_color' => $this->badge_color,
                'verified_at' => $this->verified_at,
                'expires_at' => $this->expires_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                // Privacy-controlled fields - only show to owner or admin
                'verification_data' => $this->when(
                    $this->canViewSensitiveData($request),
                    $this->verification_data
                ),
                'admin_notes' => $this->when(
                    $this->canViewAdminData($request),
                    $this->admin_notes
                ),
                'verified_by' => $this->when(
                    $this->canViewAdminData($request),
                    $this->verified_by
                ),
                // Computed attributes
                'score_value' => $this->score_value,
                'is_active' => $this->isActive(),
                'is_expired' => $this->isExpired(),
                'days_until_expiration' => $this->days_until_expiration,
                'expires_soon' => $this->expiresSoon(),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->user_id,
                    ],
                ],
                'verifier' => $this->when($this->verified_by && $this->canViewAdminData($request), function () {
                    return [
                        'data' => [
                            'type' => 'users',
                            'id' => (string) $this->verified_by,
                        ],
                    ];
                }),
            ],
            'included' => $this->when(
                $this->relationLoaded('user') || $this->relationLoaded('verifiedBy'),
                function () use ($request) {
                    $included = [];

                    // Include minimal user data if loaded
                    if ($this->relationLoaded('user') && $this->user) {
                        $included[] = [
                            'type' => 'users',
                            'id' => (string) $this->user->id,
                            'attributes' => [
                                'full_name' => $this->user->relationLoaded('profile') && $this->user->profile
                                    ? trim($this->user->profile->first_name . ' ' . $this->user->profile->last_name) ?: null
                                    : null,
                            ],
                        ];
                    }

                    // Include verifier data if loaded (admin only)
                    if ($this->relationLoaded('verifiedBy') && $this->verifiedBy && $this->canViewAdminData($request)) {
                        $included[] = [
                            'type' => 'users',
                            'id' => (string) $this->verifiedBy->id,
                            'attributes' => [
                                'full_name' => $this->verifiedBy->relationLoaded('profile') && $this->verifiedBy->profile
                                    ? trim($this->verifiedBy->profile->first_name . ' ' . $this->verifiedBy->profile->last_name) ?: null
                                    : null,
                            ],
                        ];
                    }

                    return array_filter($included);
                }
            ),
        ];
    }

    /**
     * Check if the current user can view sensitive verification data
     */
    protected function canViewSensitiveData(Request $request): bool
    {
        $currentUser = $request->user();

        if (!$currentUser) {
            return false;
        }

        // Owner can view their own data
        if ($currentUser->id === $this->user_id) {
            return true;
        }

        // Admins can view all verification data
        if ($currentUser->hasRole('admin') || $currentUser->hasRole('super_admin')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the current user can view admin-only data
     */
    protected function canViewAdminData(Request $request): bool
    {
        $currentUser = $request->user();

        if (!$currentUser) {
            return false;
        }

        // Only admins can view admin data
        return $currentUser->hasRole('admin') || $currentUser->hasRole('super_admin');
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'verification_requests',
            'id' => (string) $this->id,
            'attributes' => [
                'user_id' => $this->user_id,
                'verification_type' => $this->verification_type,
                'status' => $this->status,
                'type_display_name' => $this->type_display_name,
                'status_display_name' => $this->status_display_name,
                'status_color' => $this->status_color,
                'submitted_at' => $this->submitted_at,
                'reviewed_at' => $this->reviewed_at,
                'reviewed_by' => $this->reviewed_by,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                // Privacy-controlled fields - only show to owner or admin
                'user_notes' => $this->when(
                    $this->canViewSensitiveData($request),
                    $this->user_notes
                ),
                'admin_feedback' => $this->when(
                    $this->canViewSensitiveData($request),
                    $this->admin_feedback
                ),
                'documents' => $this->when(
                    $this->canViewSensitiveData($request),
                    $this->documents
                ),
                'submitted_data' => $this->when(
                    $this->canViewSensitiveData($request),
                    $this->submitted_data
                ),
                // Computed attributes
                'is_pending' => $this->isPending(),
                'is_approved' => $this->isApproved(),
                'is_rejected' => $this->isRejected(),
                'days_since_submission' => $this->days_since_submission,
                'required_documents' => $this->getRequiredDocuments(),
                'has_all_required_documents' => $this->hasAllRequiredDocuments(),
            ],
            'relationships' => [
                'user' => [
                    'data' => [
                        'type' => 'users',
                        'id' => (string) $this->user_id,
                    ],
                ],
                'reviewer' => $this->when($this->reviewed_by, function () {
                    return [
                        'data' => [
                            'type' => 'users',
                            'id' => (string) $this->reviewed_by,
                        ],
                    ];
                }),
            ],
            'included' => $this->when(
                $this->relationLoaded('user') || $this->relationLoaded('reviewedBy'),
                function () use ($request) {
                    $included = [];

                    // Include user data if loaded and authorized
                    if ($this->relationLoaded('user') && $this->user && $this->canViewSensitiveData($request)) {
                        $included[] = [
                            'type' => 'users',
                            'id' => (string) $this->user->id,
                            'attributes' => [
                                'email' => $this->user->email,
                                'full_name' => $this->user->relationLoaded('profile') && $this->user->profile
                                    ? trim($this->user->profile->first_name . ' ' . $this->user->profile->last_name) ?: null
                                    : null,
                            ],
                        ];
                    }

                    // Include reviewer data if loaded (admin only)
                    if ($this->relationLoaded('reviewedBy') && $this->reviewedBy) {
                        $included[] = [
                            'type' => 'users',
                            'id' => (string) $this->reviewedBy->id,
                            'attributes' => [
                                'full_name' => $this->reviewedBy->relationLoaded('profile') && $this->reviewedBy->profile
                                    ? trim($this->reviewedBy->profile->first_name . ' ' . $this->reviewedBy->profile->last_name) ?: null
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'verification_type',
        'status',
        'documents',
        'submitted_data',
        'user_notes',
        'admin_feedback',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'documents' => 'array',
        'submitted_data' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who reviewed
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get verification type display name
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->verification_type) {
            'identity' => 'Identity Verification',
            'photo' => 'Photo Verification',
            'employment' => 'Employment Verification',
            'education' => 'Education Verification',
            'income' => 'Income Verification',
            'address' => 'Address Verification',
            'social_media' => 'Social Media Verification',
            'background_check' => 'Background Check',
            default => ucfirst(str_replace('_', ' ', $this->verification_type))
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'needs_review' => 'Needs Additional Review',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'approved' => 'green',
            'rejected' => 'red',
            'needs_review' => 'orange',
            default => 'gray'
        };
    }

    /**
     * Check if request is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if request is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get days since submission
     */
    public function getDaysSinceSubmissionAttribute(): int
    {
        return $this->submitted_at->diffInDays(now());
    }

    /**
     * Get document URLs
     */
    public function getDocumentUrls(): array
    {
        if (!$this->documents) {
            return [];
        }

        $urls = [];
        foreach ($this->documents as $document) {
            if (isset($document['path'])) {
                $urls[] = [
                    'name' => $document['name'] ?? 'Document',
                    'url' => asset('storage/' . $document['path']),
                    'type' => $document['type'] ?? 'unknown',
                ];
            }
        }

        return $urls;
    }

    /**
     * Scope for pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for specific verification type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('verification_type', $type);
    }

    /**
     * Scope for recent requests
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('submitted_at', '>=', now()->subDays($days));
    }

    /**
     * Approve the verification request
     */
    public function approve(User $reviewer, ?string $feedback = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'admin_feedback' => $feedback,
        ]);
    }

    /**
     * Reject the verification request
     */
    public function reject(User $reviewer, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'admin_feedback' => $reason,
        ]);
    }

    /**
     * Mark as needs review
     */
    public function markNeedsReview(User $reviewer, string $reason): bool
    {
        return $this->update([
            'status' => 'needs_review',
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'admin_feedback' => $reason,
        ]);
    }

    /**
     * Get required documents for verification type
     */
    public function getRequiredDocuments(): array
    {
        return match ($this->verification_type) {
            'identity' => [
                'government_id' => 'Government-issued ID (passport, driver\'s license, etc.)',
                'selfie' => 'Selfie holding your ID'
            ],
            'photo' => [
                'profile_photos' => 'Clear photos of yourself for profile verification'
            ],
            'employment' => [
                'employment_letter' => 'Employment verification letter',
                'payslip' => 'Recent payslip or salary statement'
            ],
            'education' => [
                'diploma' => 'Diploma or degree certificate',
                'transcript' => 'Official transcript (optional)'
            ],
            'income' => [
                'tax_return' => 'Recent tax return',
                'bank_statement' => 'Bank statement showing income'
            ],
            'address' => [
                'utility_bill' => 'Recent utility bill',
                'lease_agreement' => 'Lease agreement or mortgage statement'
            ],
            'social_media' => [
                'social_profiles' => 'Links to your social media profiles'
            ],
            'background_check' => [
                'consent_form' => 'Signed consent form for background check'
            ],
            default => []
        };
    }

    /**
     * Check if all required documents are submitted
     */
    public function hasAllRequiredDocuments(): bool
    {
        $required = array_keys($this->getRequiredDocuments());
        $submitted = array_keys($this->documents ?? []);

        return empty(array_diff($required, $submitted));
    }
}
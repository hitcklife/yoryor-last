<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserVerifiedBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'badge_type',
        'status',
        'verification_data',
        'admin_notes',
        'verified_at',
        'expires_at',
        'verified_by',
    ];

    protected $casts = [
        'verification_data' => 'array',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who verified
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get badge display name
     */
    public function getBadgeDisplayNameAttribute(): string
    {
        return match ($this->badge_type) {
            'phone_verified' => 'Phone Verified',
            'email_verified' => 'Email Verified',
            'identity_verified' => 'Identity Verified',
            'photo_verified' => 'Photo Verified',
            'employment_verified' => 'Employment Verified',
            'education_verified' => 'Education Verified',
            'income_verified' => 'Income Verified',
            'address_verified' => 'Address Verified',
            'social_verified' => 'Social Media Verified',
            'background_check' => 'Background Checked',
            'premium_member' => 'Premium Member',
            'influencer' => 'Influencer',
            'matchmaker_verified' => 'Verified Matchmaker',
            default => ucfirst(str_replace('_', ' ', $this->badge_type))
        };
    }

    /**
     * Get badge icon
     */
    public function getBadgeIconAttribute(): string
    {
        return match ($this->badge_type) {
            'phone_verified' => '📱',
            'email_verified' => '📧',
            'identity_verified' => '🆔',
            'photo_verified' => '📸',
            'employment_verified' => '💼',
            'education_verified' => '🎓',
            'income_verified' => '💰',
            'address_verified' => '🏠',
            'social_verified' => '📱',
            'background_check' => '🔍',
            'premium_member' => '⭐',
            'influencer' => '👑',
            'matchmaker_verified' => '💕',
            default => '✅'
        };
    }

    /**
     * Get badge color for UI
     */
    public function getBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'verified' => match ($this->badge_type) {
                'premium_member' => 'gold',
                'influencer' => 'purple',
                'matchmaker_verified' => 'pink',
                'background_check' => 'green',
                default => 'blue'
            },
            'pending' => 'yellow',
            'rejected' => 'red',
            'expired' => 'gray',
            default => 'gray'
        };
    }

    /**
     * Get verification score value
     */
    public function getScoreValueAttribute(): int
    {
        if ($this->status !== 'verified') {
            return 0;
        }

        return match ($this->badge_type) {
            'phone_verified' => 5,
            'email_verified' => 5,
            'identity_verified' => 20,
            'photo_verified' => 10,
            'employment_verified' => 15,
            'education_verified' => 15,
            'income_verified' => 15,
            'address_verified' => 10,
            'social_verified' => 5,
            'background_check' => 25,
            'premium_member' => 10,
            'influencer' => 10,
            'matchmaker_verified' => 20,
            default => 5
        };
    }

    /**
     * Check if badge is verified and active
     */
    public function isActive(): bool
    {
        return $this->status === 'verified' && 
               ($this->expires_at === null || $this->expires_at->isFuture());
    }

    /**
     * Check if badge is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Get days until expiration
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->expires_at, false));
    }

    /**
     * Check if badge expires soon (within 30 days)
     */
    public function expiresSoon(): bool
    {
        return $this->expires_at && 
               $this->expires_at->lte(now()->addDays(30)) && 
               $this->expires_at->isFuture();
    }

    /**
     * Scope for verified badges
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope for active badges (verified and not expired)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'verified')
                     ->where(function ($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Scope for pending badges
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for expired badges
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope for specific badge type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('badge_type', $type);
    }

    /**
     * Mark badge as verified
     */
    public function markVerified(User $verifiedBy, ?Carbon $expiresAt = null, ?string $notes = null): bool
    {
        return $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy->id,
            'expires_at' => $expiresAt,
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Mark badge as rejected
     */
    public function markRejected(User $rejectedBy, string $reason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'verified_by' => $rejectedBy->id,
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Renew badge expiration
     */
    public function renew(?Carbon $newExpirationDate = null): bool
    {
        $expirationDate = $newExpirationDate ?? match ($this->badge_type) {
            'identity_verified', 'background_check' => now()->addYear(),
            'employment_verified', 'income_verified' => now()->addMonths(6),
            'photo_verified' => now()->addMonths(3),
            default => null // Never expires
        };

        return $this->update([
            'expires_at' => $expirationDate,
            'status' => 'verified',
        ]);
    }

    /**
     * Get verification requirements for badge type
     */
    public static function getRequirementsForType(string $badgeType): array
    {
        return match ($badgeType) {
            'identity_verified' => [
                'required_documents' => ['government_id', 'selfie'],
                'processing_time' => '2-5 business days',
                'fee' => 0,
            ],
            'photo_verified' => [
                'required_documents' => ['profile_photos'],
                'processing_time' => '24 hours',
                'fee' => 0,
            ],
            'employment_verified' => [
                'required_documents' => ['employment_letter', 'payslip'],
                'processing_time' => '3-7 business days',
                'fee' => 0,
            ],
            'education_verified' => [
                'required_documents' => ['diploma', 'transcript'],
                'processing_time' => '3-7 business days',
                'fee' => 0,
            ],
            'income_verified' => [
                'required_documents' => ['tax_return', 'bank_statement'],
                'processing_time' => '3-7 business days',
                'fee' => 0,
            ],
            'background_check' => [
                'required_documents' => ['consent_form'],
                'processing_time' => '7-14 business days',
                'fee' => 2500, // $25 in cents
            ],
            default => [
                'required_documents' => [],
                'processing_time' => '1-3 business days',
                'fee' => 0,
            ]
        };
    }
}
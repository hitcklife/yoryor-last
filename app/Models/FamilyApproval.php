<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'match_user_id',
        'family_member_id',
        'status',
        'reason',
        'notes',
        'decided_at',
        'expires_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user requesting approval
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the match user being evaluated
     */
    public function matchUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'match_user_id');
    }

    /**
     * Get the family member
     */
    public function familyMember(): BelongsTo
    {
        return $this->belongsTo(FamilyMember::class);
    }

    /**
     * Check if approval is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approval is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if approval is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if approval is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Get time remaining for decision
     */
    public function getTimeRemainingAttribute(): ?string
    {
        if (!$this->expires_at || !$this->isPending()) {
            return null;
        }

        if ($this->expires_at->isPast()) {
            return 'Expired';
        }

        $diff = now()->diff($this->expires_at);
        
        if ($diff->days > 0) {
            return $diff->days . ' ' . Str::plural('day', $diff->days);
        } elseif ($diff->h > 0) {
            return $diff->h . ' ' . Str::plural('hour', $diff->h);
        } else {
            return $diff->i . ' ' . Str::plural('minute', $diff->i);
        }
    }

    /**
     * Scope for pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for non-expired approvals
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope for decided approvals
     */
    public function scopeDecided($query)
    {
        return $query->whereIn('status', ['approved', 'rejected']);
    }
}
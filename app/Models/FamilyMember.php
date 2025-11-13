<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'family_user_id',
        'relationship',
        'relationship_detail',
        'can_approve_matches',
        'can_view_chats',
        'can_block_users',
        'status',
        'invited_at',
        'accepted_at',
    ];

    protected $casts = [
        'can_approve_matches' => 'boolean',
        'can_view_chats' => 'boolean',
        'can_block_users' => 'boolean',
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Get the main user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the family member user
     */
    public function familyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'family_user_id');
    }

    /**
     * Get approvals made by this family member
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(FamilyApproval::class);
    }

    /**
     * Get activity logs
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(FamilyActivityLog::class);
    }

    /**
     * Check if family member is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if family member is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get formatted relationship
     */
    public function getFormattedRelationshipAttribute(): string
    {
        if ($this->relationship_detail) {
            return $this->relationship_detail;
        }

        return match ($this->relationship) {
            'parent' => 'Parent',
            'sibling' => 'Sibling',
            'guardian' => 'Guardian',
            'relative' => 'Relative',
            default => ucfirst($this->relationship),
        };
    }

    /**
     * Scope for active members
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending invitations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for members who can approve
     */
    public function scopeCanApprove($query)
    {
        return $query->where('can_approve_matches', true);
    }
}
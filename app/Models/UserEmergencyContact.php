<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'email',
        'is_primary',
        'receives_panic_alerts',
        'receives_location_updates',
        'receives_date_check_ins',
        'notification_preferences',
        'priority_order',
        'is_verified',
        'verified_at',
        'verification_code',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'receives_panic_alerts' => 'boolean',
        'receives_location_updates' => 'boolean',
        'receives_date_check_ins' => 'boolean',
        'notification_preferences' => 'array',
        'priority_order' => 'integer',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get relationship display name
     */
    public function getRelationshipDisplayNameAttribute(): string
    {
        return match ($this->relationship) {
            'parent' => 'Parent',
            'sibling' => 'Sibling',
            'partner' => 'Partner/Spouse',
            'friend' => 'Friend',
            'guardian' => 'Guardian',
            'relative' => 'Relative',
            'colleague' => 'Colleague',
            'other' => 'Other',
            default => ucfirst($this->relationship)
        };
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute(): string
    {
        // Format phone number based on country code
        $phone = preg_replace('/[^\d+]/', '', $this->phone);
        
        if (str_starts_with($phone, '+998')) {
            // Uzbekistan format
            return preg_replace('/(\+998)(\d{2})(\d{3})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $phone);
        } elseif (str_starts_with($phone, '+1')) {
            // US format
            return preg_replace('/(\+1)(\d{3})(\d{3})(\d{4})/', '$1 ($2) $3-$4', $phone);
        }
        
        return $phone;
    }

    /**
     * Get notification methods
     */
    public function getNotificationMethodsAttribute(): array
    {
        $methods = [];
        
        if ($this->phone) {
            $methods[] = 'sms';
            $methods[] = 'call';
        }
        
        if ($this->email) {
            $methods[] = 'email';
        }
        
        // Check notification preferences
        $preferences = $this->notification_preferences ?? [];
        if (isset($preferences['whatsapp']) && $preferences['whatsapp']) {
            $methods[] = 'whatsapp';
        }
        
        return $methods;
    }

    /**
     * Get contact priority
     */
    public function getContactPriorityAttribute(): string
    {
        if ($this->is_primary) {
            return 'primary';
        }
        
        return match ($this->priority_order) {
            1 => 'high',
            2 => 'medium',
            default => 'low'
        };
    }

    /**
     * Check if contact can receive specific alert type
     */
    public function canReceiveAlert(string $alertType): bool
    {
        return match ($alertType) {
            'panic' => $this->receives_panic_alerts,
            'location' => $this->receives_location_updates,
            'check_in' => $this->receives_date_check_ins,
            default => false
        };
    }

    /**
     * Generate verification code
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'verification_code' => $code,
        ]);
        
        return $code;
    }

    /**
     * Verify contact with code
     */
    public function verifyWithCode(string $code): bool
    {
        if ($this->verification_code === $code) {
            $this->update([
                'is_verified' => true,
                'verified_at' => now(),
                'verification_code' => null,
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if verification code is expired
     */
    public function isVerificationCodeExpired(): bool
    {
        if (!$this->verification_code) {
            return true;
        }
        
        // Verification codes expire after 10 minutes
        return $this->updated_at->addMinutes(10)->isPast();
    }

    /**
     * Scope for primary contacts
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for verified contacts
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for panic alert recipients
     */
    public function scopePanicAlertRecipients($query)
    {
        return $query->where('receives_panic_alerts', true)
                     ->where('is_verified', true);
    }

    /**
     * Scope by relationship
     */
    public function scopeByRelationship($query, string $relationship)
    {
        return $query->where('relationship', $relationship);
    }

    /**
     * Scope ordered by priority
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('is_primary', 'desc')
                     ->orderBy('priority_order', 'asc');
    }

    /**
     * Get preferred notification method
     */
    public function getPreferredNotificationMethod(): string
    {
        $preferences = $this->notification_preferences ?? [];
        
        // Check user preferences
        if (isset($preferences['preferred_method'])) {
            return $preferences['preferred_method'];
        }
        
        // Default priority: SMS > Call > Email > WhatsApp
        $methods = $this->notification_methods;
        
        if (in_array('sms', $methods)) {
            return 'sms';
        }
        
        if (in_array('call', $methods)) {
            return 'call';
        }
        
        if (in_array('email', $methods)) {
            return 'email';
        }
        
        if (in_array('whatsapp', $methods)) {
            return 'whatsapp';
        }
        
        return 'sms'; // Default fallback
    }

    /**
     * Test contact reachability
     */
    public function testReachability(): array
    {
        $results = [];
        
        // Test SMS capability
        if ($this->phone) {
            $results['sms'] = [
                'available' => true,
                'phone' => $this->formatted_phone,
            ];
        }
        
        // Test email capability
        if ($this->email) {
            $results['email'] = [
                'available' => filter_var($this->email, FILTER_VALIDATE_EMAIL) !== false,
                'email' => $this->email,
            ];
        }
        
        // Test WhatsApp capability (if enabled in preferences)
        $preferences = $this->notification_preferences ?? [];
        if (isset($preferences['whatsapp']) && $preferences['whatsapp'] && $this->phone) {
            $results['whatsapp'] = [
                'available' => true,
                'phone' => $this->formatted_phone,
            ];
        }
        
        return $results;
    }

    /**
     * Get emergency contact summary
     */
    public function getEmergencySummary(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'relationship' => $this->relationship_display_name,
            'phone' => $this->formatted_phone,
            'email' => $this->email,
            'is_primary' => $this->is_primary,
            'is_verified' => $this->is_verified,
            'priority' => $this->contact_priority,
            'alert_types' => [
                'panic' => $this->receives_panic_alerts,
                'location' => $this->receives_location_updates,
                'check_in' => $this->receives_date_check_ins,
            ],
            'notification_methods' => $this->notification_methods,
            'preferred_method' => $this->getPreferredNotificationMethod(),
        ];
    }
}
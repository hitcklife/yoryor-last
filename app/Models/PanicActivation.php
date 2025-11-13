<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class PanicActivation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'trigger_type',
        'location',
        'location_address',
        'location_accuracy',
        'device_info',
        'context_data',
        'user_message',
        'status',
        'triggered_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
        'authorities_contacted',
    ];

    protected $casts = [
        'device_info' => 'array',
        'context_data' => 'array',
        'triggered_at' => 'datetime',
        'resolved_at' => 'datetime',
        'authorities_contacted' => 'boolean',
    ];

    /**
     * Get the user who triggered the panic
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who resolved the panic
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Get panic notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(PanicNotification::class);
    }

    /**
     * Get trigger type display name
     */
    public function getTriggerTypeDisplayNameAttribute(): string
    {
        return match ($this->trigger_type) {
            'emergency_contact' => 'Emergency Contact Alert',
            'location_sharing' => 'Location Sharing',
            'fake_call' => 'Fake Call',
            'silent_alarm' => 'Silent Alarm',
            'safe_word' => 'Safe Word Triggered',
            'date_check_in' => 'Date Check-in Panic',
            default => ucfirst(str_replace('_', ' ', $this->trigger_type))
        };
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayNameAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Active Emergency',
            'resolved' => 'Resolved',
            'false_alarm' => 'False Alarm',
            'escalated' => 'Escalated to Authorities',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'red',
            'resolved' => 'green',
            'false_alarm' => 'yellow',
            'escalated' => 'purple',
            default => 'gray'
        };
    }

    /**
     * Get location coordinates
     */
    public function getLocationCoordinatesAttribute(): ?array
    {
        if (!$this->location) {
            return null;
        }

        // Parse PostGIS point format
        $location = DB::selectOne("SELECT ST_X(?) as longitude, ST_Y(?) as latitude", [
            $this->location, $this->location
        ]);

        return $location ? [
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ] : null;
    }

    /**
     * Get formatted location
     */
    public function getFormattedLocationAttribute(): string
    {
        if ($this->location_address) {
            return $this->location_address;
        }

        $coordinates = $this->location_coordinates;
        if ($coordinates) {
            return "Lat: {$coordinates['latitude']}, Lng: {$coordinates['longitude']}";
        }

        return 'Location unavailable';
    }

    /**
     * Get duration of activation
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->resolved_at) {
            return $this->triggered_at->diffInMinutes(now());
        }

        return $this->triggered_at->diffInMinutes($this->resolved_at);
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        $duration = $this->duration;
        
        if ($duration === null) {
            return 'Ongoing';
        }

        if ($duration < 60) {
            return $duration . ' minutes';
        }

        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        return $hours . 'h ' . $minutes . 'm';
    }

    /**
     * Check if panic is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if panic is resolved
     */
    public function isResolved(): bool
    {
        return in_array($this->status, ['resolved', 'false_alarm']);
    }

    /**
     * Get severity level
     */
    public function getSeverityLevelAttribute(): string
    {
        return match ($this->trigger_type) {
            'silent_alarm', 'safe_word' => 'critical',
            'emergency_contact', 'date_check_in' => 'high',
            'location_sharing' => 'medium',
            'fake_call' => 'low',
            default => 'medium'
        };
    }

    /**
     * Get response time requirements
     */
    public function getResponseTimeMinutesAttribute(): int
    {
        return match ($this->severity_level) {
            'critical' => 2,
            'high' => 5,
            'medium' => 10,
            'low' => 15,
            default => 10
        };
    }

    /**
     * Check if response is overdue
     */
    public function isOverdue(): bool
    {
        if ($this->isResolved()) {
            return false;
        }

        return $this->triggered_at->addMinutes($this->response_time_minutes)->isPast();
    }

    /**
     * Scope for active panics
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for overdue panics
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
                     ->where('triggered_at', '<', now()->subMinutes(10));
    }

    /**
     * Scope for high severity
     */
    public function scopeHighSeverity($query)
    {
        return $query->whereIn('trigger_type', ['silent_alarm', 'safe_word', 'emergency_contact']);
    }

    /**
     * Scope by trigger type
     */
    public function scopeByTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    /**
     * Scope for recent activations
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('triggered_at', '>=', now()->subHours($hours));
    }

    /**
     * Resolve panic activation
     */
    public function resolve(User $resolver, string $notes, bool $falseAlarm = false): bool
    {
        return $this->update([
            'status' => $falseAlarm ? 'false_alarm' : 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolver->id,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Escalate to authorities
     */
    public function escalateToAuthorities(User $escalatedBy, string $notes): bool
    {
        return $this->update([
            'status' => 'escalated',
            'authorities_contacted' => true,
            'resolved_by' => $escalatedBy->id,
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Get emergency context
     */
    public function getEmergencyContext(): array
    {
        $context = [
            'user_info' => [
                'name' => $this->user->profile->first_name ?? 'Unknown',
                'age' => $this->user->profile->age ?? 'Unknown',
                'phone' => $this->user->phone ?? 'Unknown',
            ],
            'location' => [
                'coordinates' => $this->location_coordinates,
                'address' => $this->location_address,
                'accuracy' => $this->location_accuracy,
            ],
            'trigger_info' => [
                'type' => $this->trigger_type,
                'time' => $this->triggered_at->toISOString(),
                'message' => $this->user_message,
            ],
            'device_info' => $this->device_info,
            'context_data' => $this->context_data,
        ];

        // Add emergency contacts
        $emergencyContacts = $this->user->emergencyContacts()
            ->where('receives_panic_alerts', true)
            ->get(['name', 'phone', 'relationship', 'is_primary'])
            ->toArray();

        $context['emergency_contacts'] = $emergencyContacts;

        return $context;
    }

    /**
     * Get notification summary
     */
    public function getNotificationSummary(): array
    {
        $notifications = $this->notifications()->get();
        
        return [
            'total_sent' => $notifications->where('status', 'sent')->count(),
            'total_delivered' => $notifications->where('status', 'delivered')->count(),
            'total_failed' => $notifications->where('status', 'failed')->count(),
            'emergency_contacts_notified' => $notifications->where('recipient_type', 'emergency_contact')->count(),
            'authorities_notified' => $notifications->where('recipient_type', 'authorities')->count(),
            'response_rate' => $notifications->where('status', 'read')->count() / max($notifications->count(), 1) * 100,
        ];
    }

    /**
     * Generate panic report
     */
    public function generateReport(): array
    {
        return [
            'panic_id' => $this->id,
            'user' => $this->user->profile->first_name ?? 'Unknown',
            'trigger_type' => $this->trigger_type_display_name,
            'status' => $this->status_display_name,
            'triggered_at' => $this->triggered_at->format('Y-m-d H:i:s'),
            'resolved_at' => $this->resolved_at?->format('Y-m-d H:i:s'),
            'duration' => $this->formatted_duration,
            'location' => $this->formatted_location,
            'severity' => $this->severity_level,
            'response_time_requirement' => $this->response_time_minutes . ' minutes',
            'was_overdue' => $this->isOverdue(),
            'authorities_contacted' => $this->authorities_contacted,
            'emergency_context' => $this->getEmergencyContext(),
            'notification_summary' => $this->getNotificationSummary(),
            'resolution_notes' => $this->resolution_notes,
        ];
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'email', 'phone', 'google_id', 'facebook_id', 'password',
        'registration_completed', 'last_active_at', 'last_login_at',
        'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'disabled_at' => 'datetime',
            'last_active_at' => 'datetime',
            'last_login_at' => 'datetime',
            'registration_completed' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'is_admin' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    // SCOPES
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('registration_completed', true)
                    ->whereNull('disabled_at')
                    ->where('is_private', false);
    }

    public function scopeRecentlyActive(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_active_at', '>=', now()->subDays($days));
    }

    public function scopeWithCompleteProfile(Builder $query): Builder
    {
        return $query->whereHas('profile', function($q) {
            $q->whereNotNull(['first_name', 'date_of_birth', 'city']);
        });
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('last_active_at', '>=', now()->subMinutes(5));
    }

    // RELATIONSHIPS
    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class)->orderBy('order');
    }

    public function allPhotos(): HasMany
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function profilePhoto(): HasOne
    {
        return $this->hasOne(UserPhoto::class)->where('is_profile_photo', true);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(UserPreference::class);
    }

    /**
     * Get the cultural profile for the user.
     */
    public function culturalProfile(): HasOne
    {
        return $this->hasOne(UserCulturalProfile::class);
    }

    /**
     * Get the family preferences for the user.
     */
    public function familyPreference(): HasOne
    {
        return $this->hasOne(UserFamilyPreference::class);
    }

    /**
     * Get the location preferences for the user.
     */
    public function locationPreference(): HasOne
    {
        return $this->hasOne(UserLocationPreference::class);
    }

    /**
     * Get the career profile for the user.
     */
    public function careerProfile(): HasOne
    {
        return $this->hasOne(UserCareerProfile::class);
    }

    /**
     * Get the physical profile for the user.
     */
    public function physicalProfile(): HasOne
    {
        return $this->hasOne(UserPhysicalProfile::class);
    }

    /**
     * Get the settings for the user.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class);
    }

    /**
     * Get the emergency contacts for the user.
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get the data export requests for the user.
     */
    public function dataExportRequests(): HasMany
    {
        return $this->hasMany(DataExportRequest::class);
    }

    /**
     * Get the feedback submitted by the user.
     */
    public function feedback(): HasMany
    {
        return $this->hasMany(UserFeedback::class);
    }

    // CHAT RELATIONSHIPS - Updated based on your structure
    public function chatUsers(): HasMany
    {
        return $this->hasMany(ChatUser::class);
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_users')
                    ->withPivot(['is_muted', 'last_read_at', 'joined_at', 'left_at', 'role'])
                    ->withTimestamps()
                    ->wherePivotNull('left_at');
    }

    public function activeChats(): BelongsToMany
    {
        return $this->chats()->where('chats.is_active', true);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // MATCHING RELATIONSHIPS
    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'user_id');
    }

    public function matchedUsers(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'matched_user_id');
    }

    public function mutualMatches(): Builder
    {
        return MatchModel::where(function($query) {
            $query->where('user_id', $this->id)
                  ->whereExists(function($subQuery) {
                      $subQuery->select('id')
                               ->from('matches')
                               ->whereColumn('user_id', 'matches.matched_user_id')
                               ->whereColumn('matched_user_id', 'matches.user_id');
                  });
        });
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function receivedLikes(): HasMany
    {
        return $this->hasMany(Like::class, 'liked_user_id');
    }

    public function dislikes(): HasMany
    {
        return $this->hasMany(Dislike::class, 'user_id');
    }

    public function receivedDislikes(): HasMany
    {
        return $this->hasMany(Dislike::class, 'disliked_user_id');
    }

    /**
     * Get the stories for the user.
     */
    public function stories(): HasMany
    {
        return $this->hasMany(UserStory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get only active stories for the user.
     */
    public function activeStories(): HasMany
    {
        return $this->hasMany(UserStory::class)
                    ->where('status', 'active')
                    ->where('expires_at', '>', now())
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get the device tokens for the user.
     */
    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    /**
     * Route notifications for the expo channel.
     * This method is used by the laravel-expo-notifier package.
     */
    public function routeNotificationForExpo(): array
    {
        return $this->deviceTokens->pluck('token')->toArray();
    }

    // HELPER METHODS
    public function updateLastActive(): void
    {
        $this->timestamps = false;
        $this->update(['last_active_at' => now()]);
        $this->timestamps = true;
    }

    public function isOnline(): bool
    {
        return $this->last_active_at && $this->last_active_at->greaterThan(now()->subMinutes(5));
    }

    // PRESENCE METHODS

    /**
     * Check if user is online using presence service
     */
    public function isOnlineViaPresence(): bool
    {
        return app(\App\Services\PresenceService::class)->isUserOnline($this->id);
    }

    /**
     * Mark user as online in presence system
     */
    public function goOnline(): void
    {
        app(\App\Services\PresenceService::class)->markUserOnline($this);
    }

    /**
     * Mark user as offline in presence system
     */
    public function goOffline(): void
    {
        app(\App\Services\PresenceService::class)->markUserOffline($this);
    }

    /**
     * Get user's presence data
     */
    public function getPresenceData(): ?array
    {
        return app(\App\Services\PresenceService::class)->getPresenceData($this->id);
    }

    /**
     * Get user's online matches
     */
    public function getOnlineMatches(): \Illuminate\Support\Collection
    {
        return app(\App\Services\PresenceService::class)->getOnlineMatches($this);
    }

    /**
     * Update typing status in a chat
     */
    public function updateTypingStatus(int $chatId, bool $isTyping): void
    {
        app(\App\Services\PresenceService::class)->updateTypingStatus($this, $chatId, $isTyping);
    }

    /**
     * Check if user is currently active (based on last activity)
     */
    public function isActiveInLast(int $minutes = 15): bool
    {
        return $this->last_active_at && $this->last_active_at->greaterThan(now()->subMinutes($minutes));
    }

    /**
     * Get online status with details
     */
    public function getOnlineStatus(): array
    {
        $isOnline = $this->isOnline();
        $isOnlineViaPresence = $this->isOnlineViaPresence();

        return [
            'is_online' => $isOnline || $isOnlineViaPresence,
            'is_online_database' => $isOnline,
            'is_online_presence' => $isOnlineViaPresence,
            'last_active_at' => $this->last_active_at?->toISOString(),
            'status' => $isOnline || $isOnlineViaPresence ? 'online' : 'offline',
            'last_seen' => $this->last_active_at?->diffForHumans(),
        ];
    }

    public function getAgeAttribute(): ?int
    {
        return $this->profile?->date_of_birth?->age;
    }

    public function getFullNameAttribute(): string
    {
        // Use cached value if available to avoid repeated calculations
        if (isset($this->attributes['full_name'])) {
            return $this->attributes['full_name'];
        }

        $firstName = $this->profile?->first_name;
        $lastName = $this->profile?->last_name;

        // Handle different combinations of first_name and last_name
        if ($firstName && $lastName) {
            $fullName = $firstName . ' ' . $lastName;
        } elseif ($firstName) {
            $fullName = $firstName;
        } elseif ($lastName) {
            $fullName = $lastName;
        } else {
            $fullName = 'Anonymous';
        }

        // Cache the result
        $this->attributes['full_name'] = $fullName;

        return $fullName;
    }

    public function initials(): string
    {
        $name = $this->full_name;
        return Str::of($name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    // CHAT HELPER METHODS
    public function getChatWith(User $user): ?\Illuminate\Database\Eloquent\Model
    {
        return $this->chats()
            ->whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('type', 'private')
            ->first();
    }

    public function getUnreadMessagesCount(): int
    {
        return Message::whereHas('chat.users', function($query) {
                $query->where('user_id', $this->id);
            })
            ->where('sender_id', '!=', $this->id)
            ->whereDoesntHave('messageReads', function($query) {
                $query->where('user_id', $this->id);
            })
            ->count();
    }

    public function hasLiked(User $user): bool
    {
        return $this->likes()->where('liked_user_id', $user->id)->exists();
    }

    public function hasDisliked(User $user): bool
    {
        return $this->dislikes()->where('disliked_user_id', $user->id)->exists();
    }

    public function hasMatched(User $user): bool
    {
        return $this->matches()->where('matched_user_id', $user->id)->exists() &&
               $user->matches()->where('matched_user_id', $this->id)->exists();
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $user->blockedUsers()->where('blocked_id', $this->id)->exists();
    }

    public function hasReported(User $user): bool
    {
        return $this->reportsMade()->where('reported_id', $user->id)->exists();
    }

    public function canViewProfile(User $user): bool
    {
        // Users can't view profiles of those who blocked them or whom they blocked
        return !$this->hasBlocked($user) && !$this->isBlockedBy($user);
    }

    // PROFILE PHOTO HELPER METHODS
    public function getProfilePhotoUrl(string $size = 'medium'): ?string
    {
        $profilePhoto = $this->profilePhoto;

        if (!$profilePhoto) {
            return null;
        }

        return match($size) {
            'thumbnail' => $profilePhoto->thumbnail_url,
            'original' => $profilePhoto->original_url,
            default => $profilePhoto->medium_url,
        };
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->getProfilePhotoUrl();
    }

    // BLOCKING AND REPORTING RELATIONSHIPS
    public function blockedUsers(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function blockedBy(): HasMany
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    public function reportsMade(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reporter_id');
    }

    public function reportsReceived(): HasMany
    {
        return $this->hasMany(UserReport::class, 'reported_id');
    }

    // ROLE AND PERMISSION METHODS
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function hasRole(string|Role $role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }
        return $this->roles()->where('id', $role->id)->exists();
    }

    public function hasPermission(string|Permission $permission): bool
    {
        if (is_string($permission)) {
            if ($this->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        } else {
            if ($this->permissions()->where('id', $permission->id)->exists()) {
                return true;
            }
        }

        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}

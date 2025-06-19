<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'phone',
        'google_id',
        'facebook_id',
        'password',
        'profile_photo_path',
        'registration_completed',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'disabled_at' => 'datetime',
            'registration_completed' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'is_admin' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }


    public function photos(): HasMany
    {
        return $this->hasMany(UserPhoto::class)->orderBy('order', 'asc');
    }

    public function profilePhoto(): HasOne
    {
        return $this->hasOne(UserPhoto::class)->ofMany('order', 'min');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function preference(): HasOne
    {
        return $this->hasOne(Preference::class);
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_users')
            ->withTimestamps();
    }


    public function matches(): HasMany
    {
        return $this->hasMany(MatchModel::class);
    }

    public function matchedUsers(): HasMany
    {
        return $this->hasMany(MatchModel::class, 'matched_user_id');
    }


    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function dislikes(): HasMany
    {
        return $this->hasMany(Dislike::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The permissions that belong to the user.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Check if the user has the given role.
     *
     * @param string|Role $role
     * @return bool
     */
    public function hasRole(string|Role $role): bool
    {
        if (is_string($role)) {
            return $this->roles()->where('name', $role)->exists();
        }

        return $this->roles()->where('id', $role->id)->exists();
    }

    /**
     * Check if the user has the given permission.
     *
     * @param string|Permission $permission
     * @return bool
     */
    public function hasPermission(string|Permission $permission): bool
    {
        // Check direct permissions
        if (is_string($permission)) {
            if ($this->permissions()->where('name', $permission)->exists()) {
                return true;
            }
        } else {
            if ($this->permissions()->where('id', $permission->id)->exists()) {
                return true;
            }
        }

        // Check permissions via roles
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}

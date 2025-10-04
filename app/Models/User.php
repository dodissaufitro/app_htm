<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'allowed_status',
        'urutan',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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
            'password' => 'hashed',
            'allowed_status' => 'array',
            'urutan' => 'integer',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Get the user's roles for display
     */
    public function getRoleNamesAttribute()
    {
        return $this->roles->pluck('name')->toArray();
    }

    /**
     * Check if user can access specific status
     */
    public function canAccessStatus(string $statusCode): bool
    {
        // If no allowed status is set, user can access all status
        if (empty($this->allowed_status)) {
            return true;
        }

        // Check if status code is in allowed list
        return in_array($statusCode, $this->allowed_status);
    }

    /**
     * Get allowed status codes for this user
     */
    public function getAllowedStatusCodes(): array
    {
        return $this->allowed_status ?? [];
    }

    /**
     * Set allowed status codes for this user
     */
    public function setAllowedStatus(array $statusCodes): void
    {
        $this->allowed_status = $statusCodes;
        $this->save();
    }

    /**
     * Scope to order users by urutan field
     */
    public function scopeOrderByUrutan($query, $direction = 'asc')
    {
        return $query->orderBy('urutan', $direction);
    }

    /**
     * Scope to get users by specific urutan
     */
    public function scopeByUrutan($query, int $urutan)
    {
        return $query->where('urutan', $urutan);
    }

    /**
     * Get next user in urutan sequence
     */
    public function getNextUser()
    {
        return static::where('urutan', '>', $this->urutan)
            ->orderBy('urutan', 'asc')
            ->first();
    }

    /**
     * Get previous user in urutan sequence
     */
    public function getPreviousUser()
    {
        return static::where('urutan', '<', $this->urutan)
            ->orderBy('urutan', 'desc')
            ->first();
    }

    /**
     * Check if this is the first user in sequence
     */
    public function isFirstInSequence(): bool
    {
        return static::where('urutan', '<', $this->urutan)->count() === 0;
    }

    /**
     * Check if this is the last user in sequence
     */
    public function isLastInSequence(): bool
    {
        return static::where('urutan', '>', $this->urutan)->count() === 0;
    }

    /**
     * Get all users ordered by urutan for developer workflow
     */
    public static function getDeveloperWorkflowUsers()
    {
        return static::where('urutan', '>', 0)
            ->orderBy('urutan', 'asc')
            ->get();
    }
}

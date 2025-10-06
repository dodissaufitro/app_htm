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
        'lokasi_hunian',
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
            'lokasi_hunian' => 'array',
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
     * Get DataHunian records that this developer handles
     */
    public function lokasiHunian()
    {
        if (empty($this->lokasi_hunian)) {
            return collect();
        }

        return \App\Models\DataHunian::whereIn('id', $this->lokasi_hunian)->get();
    }

    /**
     * Check if this user is a developer with specific location assignments
     */
    public function isDeveloperWithLocations(): bool
    {
        return $this->hasRole('Developer') && !empty($this->lokasi_hunian);
    }

    /**
     * Get location names handled by this developer
     */
    public function getLokasiHunianNamesAttribute(): array
    {
        if (empty($this->lokasi_hunian)) {
            return [];
        }

        return \App\Models\DataHunian::whereIn('id', $this->lokasi_hunian)
            ->pluck('nama_pemukiman')
            ->toArray();
    }

    /**
     * Check if developer can handle specific location
     */
    public function canHandleLocation(int $dataHunianId): bool
    {
        if (!$this->hasRole('Developer')) {
            return false;
        }

        // If no locations assigned, can handle all
        if (empty($this->lokasi_hunian)) {
            return true;
        }

        return in_array($dataHunianId, $this->lokasi_hunian);
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

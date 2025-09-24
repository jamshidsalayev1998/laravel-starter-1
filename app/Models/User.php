<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The guard name for the model.
     */
    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'phone_verified_at',
        'is_active',
        'is_banned',
        'banned_at',
        'banned_by',
        'ban_reason',
        'ban_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'ban_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the user's phone number for authentication.
     */
    public function getPhoneForPasswordReset()
    {
        return $this->phone;
    }

    /**
     * Check if user is verified (phone verified).
     */
    public function isVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Mark phone as verified.
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function userSessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    public function activeSessions(): HasMany
    {
        return $this->hasMany(UserSession::class)->where('is_active', true);
    }

    public function validRefreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class)->where('is_revoked', false);
    }

    /**
     * Check if user is banned (permanent or temporary).
     */
    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    /**
     * Check if user has permanent ban.
     */
    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned && $this->ban_expires_at === null;
    }

    /**
     * Check if user has temporary ban.
     */
    public function isTemporarilyBanned(): bool
    {
        return $this->is_banned && $this->ban_expires_at !== null && $this->ban_expires_at > now();
    }

    /**
     * Check if temporary ban has expired.
     */
    public function isTemporaryBanExpired(): bool
    {
        return $this->is_banned && $this->ban_expires_at !== null && $this->ban_expires_at <= now();
    }

    /**
     * Ban user (permanent or temporary).
     */
    public function ban(User $bannedBy, string $reason, ?Carbon $expiresAt = null): bool
    {
        return $this->update([
            'is_banned' => true,
            'banned_at' => now(),
            'banned_by' => $bannedBy->id,
            'ban_reason' => $reason,
            'ban_expires_at' => $expiresAt,
        ]);
    }

    /**
     * Unban user.
     */
    public function unban(): bool
    {
        return $this->update([
            'is_banned' => false,
            'banned_at' => null,
            'banned_by' => null,
            'ban_reason' => null,
            'ban_expires_at' => null,
        ]);
    }

    /**
     * Get the user who banned this user.
     */
    public function bannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    /**
     * Get ban information.
     */
    public function getBanInfo(): ?array
    {
        if (!$this->is_banned) {
            return null;
        }

        return [
            'is_banned' => $this->is_banned,
            'banned_at' => $this->banned_at,
            'banned_by' => $this->bannedBy?->name,
            'ban_reason' => $this->ban_reason,
            'ban_expires_at' => $this->ban_expires_at,
            'is_permanent' => $this->isPermanentlyBanned(),
            'is_temporary' => $this->isTemporarilyBanned(),
            'is_expired' => $this->isTemporaryBanExpired(),
        ];
    }
}

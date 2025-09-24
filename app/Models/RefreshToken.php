<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RefreshToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'device_id',
        'device_name',
        'device_type',
        'ip_address',
        'user_agent',
        'expires_at',
        'last_used_at',
        'is_revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_revoked && !$this->isExpired();
    }

    public function revoke(): void
    {
        $this->update(['is_revoked' => true]);
    }

    public function updateLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(64));
    }

    public static function createForUser(User $user, array $deviceInfo = []): self
    {
        // Mavjud refresh tokenni topish
        $existingToken = self::where('user_id', $user->id)
            ->where('device_id', $deviceInfo['device_id'] ?? null)
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingToken) {
            // Mavjud tokenni yangilash
            $existingToken->update([
                'device_name' => $deviceInfo['device_name'] ?? $existingToken->device_name,
                'device_type' => $deviceInfo['device_type'] ?? $existingToken->device_type,
                'ip_address' => $deviceInfo['ip_address'] ?? $existingToken->ip_address,
                'user_agent' => $deviceInfo['user_agent'] ?? $existingToken->user_agent,
                'last_used_at' => now(),
                'expires_at' => now()->addDays(30), // 30 kun
            ]);
            return $existingToken;
        }

        // Yangi refresh token yaratish
        return self::create([
            'user_id' => $user->id,
            'token' => self::generateToken(),
            'device_id' => $deviceInfo['device_id'] ?? null,
            'device_name' => $deviceInfo['device_name'] ?? null,
            'device_type' => $deviceInfo['device_type'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? null,
            'user_agent' => $deviceInfo['user_agent'] ?? null,
            'expires_at' => now()->addDays(30), // 30 kun
        ]);
    }
}

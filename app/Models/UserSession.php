<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'device_id',
        'device_name',
        'device_type',
        'ip_address',
        'user_agent',
        'platform',
        'browser',
        'location',
        'is_active',
        'last_activity_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
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
        return $this->is_active && !$this->isExpired();
    }

    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    public static function generateSessionId(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function createForUser(User $user, array $deviceInfo = []): self
    {
        // Mavjud sessionni topish
        $existingSession = self::where('user_id', $user->id)
            ->where('device_id', $deviceInfo['device_id'] ?? null)
            ->where('is_active', true)
            ->first();

        if ($existingSession) {
            // Mavjud sessionni yangilash
            $existingSession->update([
                'device_name' => $deviceInfo['device_name'] ?? $existingSession->device_name,
                'device_type' => $deviceInfo['device_type'] ?? $existingSession->device_type,
                'ip_address' => $deviceInfo['ip_address'] ?? $existingSession->ip_address,
                'user_agent' => $deviceInfo['user_agent'] ?? $existingSession->user_agent,
                'platform' => $deviceInfo['platform'] ?? $existingSession->platform,
                'browser' => $deviceInfo['browser'] ?? $existingSession->browser,
                'location' => $deviceInfo['location'] ?? $existingSession->location,
                'last_activity_at' => now(),
                'expires_at' => now()->addDays(30), // 30 kun
            ]);
            return $existingSession;
        }

        // Yangi session yaratish
        return self::create([
            'user_id' => $user->id,
            'session_id' => self::generateSessionId(),
            'device_id' => $deviceInfo['device_id'] ?? null,
            'device_name' => $deviceInfo['device_name'] ?? null,
            'device_type' => $deviceInfo['device_type'] ?? null,
            'ip_address' => $deviceInfo['ip_address'] ?? null,
            'user_agent' => $deviceInfo['user_agent'] ?? null,
            'platform' => $deviceInfo['platform'] ?? null,
            'browser' => $deviceInfo['browser'] ?? null,
            'location' => $deviceInfo['location'] ?? null,
            'is_active' => true,
            'last_activity_at' => now(),
            'expires_at' => now()->addDays(30), // 30 kun
        ]);
    }
}

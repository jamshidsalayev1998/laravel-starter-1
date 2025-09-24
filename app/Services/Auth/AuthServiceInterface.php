<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $userData, array $deviceInfo): array;

    /**
     * Login user
     */
    public function login(string $phone, string $password, array $deviceInfo): array;

    /**
     * Get authenticated user with permissions
     */
    public function getAuthenticatedUser(User $user): array;

    /**
     * Logout user
     */
    public function logout(User $user): bool;

    /**
     * Verify phone number
     */
    public function verifyPhone(string $phone, string $verificationCode): array;

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array;

    /**
     * Get user devices/sessions
     */
    public function getUserDevices(User $user, string $currentDeviceId): array;

    /**
     * Revoke specific device
     */
    public function revokeDevice(User $user, int $sessionId): bool;

    /**
     * Revoke all devices except current
     */
    public function revokeAllDevices(User $user, string $currentDeviceId): bool;

    /**
     * Get device information from request
     */
    public function getDeviceInfo(Request $request): array;
}

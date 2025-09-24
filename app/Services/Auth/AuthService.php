<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\RefreshToken;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $userData, array $deviceInfo): array
    {
        $user = User::create([
            'name' => $userData['name'],
            'phone' => $userData['phone'],
            'email' => $userData['email'] ?? null,
            'password' => Hash::make($userData['password']),
        ]);

        // Default role - guest
        $user->assignRole('guest');

        // Create or update refresh token
        $refreshToken = RefreshToken::createForUser($user, $deviceInfo);

        // Create or update user session
        $userSession = UserSession::createForUser($user, $deviceInfo);

        // Create access token (15 minutes)
        $accessToken = $user->createToken('auth-token', ['*'], now()->addMinutes(15))->plainTextToken;

        return [
            'user' => $user->load('roles'),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'expires_in' => 900, // 15 minutes in seconds
            'device' => [
                'id' => $userSession->device_id,
                'name' => $userSession->device_name,
                'type' => $userSession->device_type,
            ],
        ];
    }

    /**
     * Login user
     */
    public function login(string $phone, string $password, array $deviceInfo): array
    {
        \Log::info('AuthService login started', [
            'phone' => $phone,
            'device_info' => $deviceInfo
        ]);

        $user = User::where('phone', $phone)->first();
        \Log::info('User lookup result', [
            'user_found' => $user ? true : false,
            'user_id' => $user ? $user->id : null,
            'user_active' => $user ? $user->is_active : null
        ]);

        if (!$user || !Hash::check($password, $user->password)) {
            \Log::warning('Login failed - invalid credentials', [
                'phone' => $phone,
                'user_exists' => $user ? true : false,
                'password_match' => $user ? Hash::check($password, $user->password) : false
            ]);
            throw new \Exception('Invalid credentials', 401);
        }

        if (!$user->is_active) {
            \Log::warning('Login failed - account deactivated', [
                'user_id' => $user->id,
                'is_active' => $user->is_active
            ]);
            throw new \Exception('Account is deactivated', 401);
        }

        // Check if user is banned
        $this->checkUserBanStatus($user);

        \Log::info('User validation passed, creating tokens');

        // Create or update refresh token
        $refreshToken = RefreshToken::createForUser($user, $deviceInfo);
        \Log::info('Refresh token created', ['token_id' => $refreshToken->id]);

        // Create or update user session
        $userSession = UserSession::createForUser($user, $deviceInfo);
        \Log::info('User session created', ['session_id' => $userSession->id]);

        // Create access token (15 minutes)
        $accessToken = $user->createToken('auth-token', ['*'], now()->addMinutes(15))->plainTextToken;
        \Log::info('Access token created successfully');

        $result = [
            'user' => $user->load('roles'),
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken->token,
            'expires_in' => 900, // 15 minutes in seconds
            'device' => [
                'id' => $userSession->device_id,
                'name' => $userSession->device_name,
                'type' => $userSession->device_type,
            ],
        ];

        \Log::info('Login successful', [
            'user_id' => $user->id,
            'has_access_token' => !empty($result['access_token']),
            'has_refresh_token' => !empty($result['refresh_token'])
        ]);

        return $result;
    }

    /**
     * Get authenticated user with permissions
     */
    public function getAuthenticatedUser(User $user): array
    {
        $user->load('roles');

        // Barcha ruxsatlarni olish (rol orqali va to'g'ridan-to'g'ri)
        $rolePermissions = $user->roles->flatMap->permissions;
        $directPermissions = $user->permissions;
        $allPermissions = $rolePermissions->merge($directPermissions)->unique('id');

        // Permissions maydonini to'g'ri o'rnatamiz
        $user->setRelation('permissions', $allPermissions);

        return [
            'user' => $user,
        ];
    }

    /**
     * Logout user
     */
    public function logout(User $user): bool
    {
        \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();
        return true;
    }

    /**
     * Verify phone number
     */
    public function verifyPhone(string $phone, string $verificationCode): array
    {
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        // Bu yerda SMS verification logic bo'lishi kerak
        // Hozircha oddiy verification qilamiz
        if ($verificationCode === '123456') {
            $user->markPhoneAsVerified();

            return [
                'user' => $user->load('roles'),
            ];
        }

        throw new \Exception('Invalid verification code', 400);
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): array
    {
        $refreshTokenModel = RefreshToken::where('token', $refreshToken)
            ->where('is_revoked', false)
            ->first();

        if (!$refreshTokenModel || !$refreshTokenModel->isValid()) {
            throw new \Exception('Invalid or expired refresh token', 401);
        }

        $user = $refreshTokenModel->user;

        if (!$user->is_active) {
            throw new \Exception('Account is deactivated', 401);
        }

        // Check if user is banned
        $this->checkUserBanStatus($user);

        // Update refresh token last used
        $refreshTokenModel->updateLastUsed();

        // Revoke old access tokens
        \Laravel\Sanctum\PersonalAccessToken::where('tokenable_id', $user->id)->delete();

        // Create new access token
        $accessToken = $user->createToken('auth-token', ['*'], now()->addMinutes(15))->plainTextToken;

        return [
            'access_token' => $accessToken,
            'expires_in' => 900, // 15 minutes in seconds
        ];
    }

    /**
     * Get user devices/sessions
     */
    public function getUserDevices(User $user, string $currentDeviceId): array
    {
        // Faqat faol va muddati o'tmagan sessionlarni olish
        $sessions = $user->userSessions()
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->orderBy('last_activity_at', 'desc')
            ->get();

        return [
            'devices' => $sessions->map(function ($session) use ($currentDeviceId) {
                return [
                    'id' => $session->id,
                    'device_id' => $session->device_id,
                    'device_name' => $session->device_name,
                    'device_type' => $session->device_type,
                    'platform' => $session->platform,
                    'browser' => $session->browser,
                    'location' => $session->location,
                    'ip_address' => $session->ip_address,
                    'last_activity' => $session->last_activity_at,
                    'is_current' => $session->device_id === $currentDeviceId,
                ];
            }),
        ];
    }

    /**
     * Revoke specific device
     */
    public function revokeDevice(User $user, int $sessionId): bool
    {
        $session = $user->userSessions()->find($sessionId);

        if (!$session) {
            throw new \Exception('Device not found', 404);
        }

        // Deactivate session
        $session->deactivate();

        // Revoke related refresh tokens
        $user->refreshTokens()
            ->where('device_id', $session->device_id)
            ->update(['is_revoked' => true]);

        return true;
    }

    /**
     * Revoke all devices except current
     */
    public function revokeAllDevices(User $user, string $currentDeviceId): bool
    {
        // Deactivate all sessions except current
        $user->userSessions()
            ->where('device_id', '!=', $currentDeviceId)
            ->update(['is_active' => false]);

        // Revoke all refresh tokens except current
        $user->refreshTokens()
            ->where('device_id', '!=', $currentDeviceId)
            ->update(['is_revoked' => true]);

        return true;
    }

    /**
     * Get device information from request
     */
    public function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->userAgent();
        $deviceId = $this->getDeviceId($request);

        return [
            'device_id' => $deviceId,
            'device_name' => $request->header('X-Device-Name', 'Unknown Device'),
            'device_type' => $this->getDeviceType($userAgent),
            'ip_address' => $request->ip(),
            'user_agent' => $userAgent,
            'platform' => $this->getPlatform($userAgent),
            'browser' => $this->getBrowser($userAgent),
            'location' => $this->getLocation($request->ip()),
        ];
    }

    /**
     * Get device ID from request
     */
    private function getDeviceId(Request $request): string
    {
        return $request->header('X-Device-ID', md5($request->ip() . $request->userAgent()));
    }

    /**
     * Get device type from user agent
     */
    private function getDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/Tablet|iPad/', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get platform from user agent
     */
    private function getPlatform(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        // API clients uchun alohida detection
        if (strpos($userAgent, 'postman') !== false) return 'Postman';
        if (strpos($userAgent, 'insomnia') !== false) return 'Insomnia';
        if (strpos($userAgent, 'curl') !== false) return 'cURL';
        if (strpos($userAgent, 'wget') !== false) return 'Wget';
        if (strpos($userAgent, 'httpie') !== false) return 'HTTPie';

        // Oddiy browser va OS detection
        if (strpos($userAgent, 'windows') !== false) return 'Windows';
        if (strpos($userAgent, 'macintosh') !== false || strpos($userAgent, 'mac os') !== false) return 'macOS';
        if (strpos($userAgent, 'linux') !== false) return 'Linux';
        if (strpos($userAgent, 'android') !== false) return 'Android';
        if (strpos($userAgent, 'iphone') !== false || strpos($userAgent, 'ipad') !== false) return 'iOS';

        return 'Unknown';
    }

    /**
     * Get browser from user agent
     */
    private function getBrowser(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        // API clients uchun alohida detection
        if (strpos($userAgent, 'postman') !== false) return 'Postman';
        if (strpos($userAgent, 'insomnia') !== false) return 'Insomnia';
        if (strpos($userAgent, 'curl') !== false) return 'cURL';
        if (strpos($userAgent, 'wget') !== false) return 'Wget';
        if (strpos($userAgent, 'httpie') !== false) return 'HTTPie';

        // Oddiy browser detection
        if (strpos($userAgent, 'edg/') !== false) return 'Edge';
        if (strpos($userAgent, 'chrome/') !== false) return 'Chrome';
        if (strpos($userAgent, 'firefox/') !== false) return 'Firefox';
        if (strpos($userAgent, 'safari/') !== false) return 'Safari';
        if (strpos($userAgent, 'opera/') !== false || strpos($userAgent, 'opr/') !== false) return 'Opera';

        return 'Unknown';
    }

    /**
     * Get location from IP (simplified)
     */
    private function getLocation(string $ip): string
    {
        // Bu yerda IP geolocation API ishlatish mumkin
        // Hozircha oddiy qo'yamiz
        return 'Toshkent, O\'zbekiston';
    }

    /**
     * Check if user is banned and throw exception if banned
     */
    private function checkUserBanStatus(User $user): void
    {
        if ($user->isBanned()) {
            $banInfo = $user->getBanInfo();
            $message = 'Account is banned';

            if ($banInfo['is_temporary']) {
                $message .= ' until ' . $banInfo['ban_expires_at']->format('Y-m-d H:i:s');
            }

            if ($banInfo['ban_reason']) {
                $message .= '. Reason: ' . $banInfo['ban_reason'];
            }

            throw new \Exception($message, 403);
        }
    }
}

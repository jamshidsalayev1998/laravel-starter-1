<?php
namespace App\Http\Controllers\Api\Auth;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthServiceInterface;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\VerifyPhoneRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // ApiResponse trait removed - now inherited from Base Controller

    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request)
    {
        try {
            $deviceInfo = $this->authService->getDeviceInfo($request);
            $result = $this->authService->register($request->validated(), $deviceInfo);

            return $this->successResponse(
                'User registered successfully',
                $result,
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Login with phone and password
     */
    public function login(LoginRequest $request)
    {
        try {
            $deviceInfo = $this->authService->getDeviceInfo($request);
            $result = $this->authService->login($request->phone, $request->password, $deviceInfo);

            return $this->successResponse(
                'Login successful',
                $result
            );
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        try {
            $result = $this->authService->getAuthenticatedUser($request->user());

            return $this->successResponse(
                'User data retrieved successfully',
                $result
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $this->authService->logout($request->user());

            return $this->successResponse('Logged out successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Verify phone number
     */
    public function verifyPhone(VerifyPhoneRequest $request)
    {
        try {
            $result = $this->authService->verifyPhone($request->phone, $request->verification_code);

            return $this->successResponse(
                'Phone verified successfully',
                $result
            );
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(RefreshTokenRequest $request)
    {
        try {
            $result = $this->authService->refreshToken($request->refresh_token);

            return $this->successResponse(
                'Token refreshed successfully',
                $result
            );
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Get user devices/sessions
     */
    public function getDevices(Request $request)
    {
        try {
            $deviceInfo = $this->authService->getDeviceInfo($request);
            $result = $this->authService->getUserDevices($request->user(), $deviceInfo['device_id']);

            return $this->successResponse(
                'Devices retrieved successfully',
                $result
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Revoke device/session
     */
    public function revokeDevice(Request $request, $sessionId)
    {
        try {
            $this->authService->revokeDevice($request->user(), $sessionId);

            return $this->successResponse('Device revoked successfully');
        } catch (\Exception $e) {
            if ($e->getCode() === 404) {
                return $this->errorResponse($e->getMessage(), null, 404);
            }
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Revoke all devices except current
     */
    public function revokeAllDevices(Request $request)
    {
        try {
            $deviceInfo = $this->authService->getDeviceInfo($request);
            $this->authService->revokeAllDevices($request->user(), $deviceInfo['device_id']);

            return $this->successResponse('All other devices revoked successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

}

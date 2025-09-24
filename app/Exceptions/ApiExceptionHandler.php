<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class ApiExceptionHandler
{
    /**
     * API exception'larini boshqarish
     */
    public static function handle(Throwable $e, Request $request): ?JsonResponse
    {
        // API request emas bo'lsa, null qaytar
        if (!$request->expectsJson() && !$request->is('api/*')) {
            return null;
        }

        // Xatolikni log qilish
        self::logException($e, $request);

        // Exception turiga qarab response qaytarish
        return match (true) {
            $e instanceof ValidationException => self::handleValidationException($e),
            $e instanceof AuthenticationException => self::handleAuthenticationException($e),
            $e instanceof AuthorizationException => self::handleAuthorizationException($e),
            $e instanceof ModelNotFoundException => self::handleModelNotFoundException($e),
            $e instanceof NotFoundHttpException => self::handleNotFoundHttpException($e),
            $e instanceof MethodNotAllowedHttpException => self::handleMethodNotAllowedException($e),
            $e instanceof ThrottleRequestsException => self::handleThrottleRequestsException($e),
            $e instanceof TooManyRequestsHttpException => self::handleTooManyRequestsException($e),
            default => self::handleGenericException($e, $request)
        };
    }

    /**
     * Validation exception'ni boshqarish
     */
    private static function handleValidationException(ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Ma\'lumotlar noto\'g\'ri',
            'errors' => $e->errors(),
            'timestamp' => now()->toISOString(),
            'status_code' => 422,
            'meta' => ['validation_errors' => true]
        ], 422);
    }

    /**
     * Authentication exception'ni boshqarish
     */
    private static function handleAuthenticationException(AuthenticationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Autentifikatsiya kerak',
            'timestamp' => now()->toISOString(),
            'status_code' => 401,
            'meta' => ['auth_required' => true]
        ], 401);
    }

    /**
     * Authorization exception'ni boshqarish
     */
    private static function handleAuthorizationException(AuthorizationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Ruxsat yo\'q',
            'timestamp' => now()->toISOString(),
            'status_code' => 403,
            'meta' => ['permission_required' => true]
        ], 403);
    }

    /**
     * Model not found exception'ni boshqarish
     */
    private static function handleModelNotFoundException(ModelNotFoundException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Ma\'lumot topilmadi',
            'timestamp' => now()->toISOString(),
            'status_code' => 404,
            'meta' => ['not_found' => true]
        ], 404);
    }

    /**
     * Not found HTTP exception'ni boshqarish
     */
    private static function handleNotFoundHttpException(NotFoundHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Sahifa topilmadi',
            'timestamp' => now()->toISOString(),
            'status_code' => 404,
            'meta' => ['not_found' => true]
        ], 404);
    }

    /**
     * Method not allowed exception'ni boshqarish
     */
    private static function handleMethodNotAllowedException(MethodNotAllowedHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'So\'rov usuli noto\'g\'ri',
            'timestamp' => now()->toISOString(),
            'status_code' => 405,
            'meta' => ['method_not_allowed' => true]
        ], 405);
    }

    /**
     * Throttle requests exception'ni boshqarish
     */
    private static function handleThrottleRequestsException(ThrottleRequestsException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Juda ko\'p so\'rov yuborildi',
            'timestamp' => now()->toISOString(),
            'status_code' => 429,
            'meta' => [
                'rate_limit_exceeded' => true,
                'retry_after' => 60
            ]
        ], 429);
    }

    /**
     * Too many requests exception'ni boshqarish
     */
    private static function handleTooManyRequestsException(TooManyRequestsHttpException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Juda ko\'p so\'rov yuborildi',
            'timestamp' => now()->toISOString(),
            'status_code' => 429,
            'meta' => [
                'rate_limit_exceeded' => true,
                'retry_after' => $e->getHeaders()['Retry-After'] ?? 60
            ]
        ], 429);
    }

    /**
     * Umumiy exception'ni boshqarish
     */
    private static function handleGenericException(Throwable $e, Request $request): JsonResponse
    {
        // Development muhitida batafsil xatolik
        if (app()->environment('local', 'development')) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString(),
                'status_code' => 500,
            ], 500);
        }

        // Production muhitida umumiy xatolik
        return response()->json([
            'success' => false,
            'message' => 'Server xatoligi',
            'timestamp' => now()->toISOString(),
            'status_code' => 500,
            'meta' => ['server_error' => true]
        ], 500);
    }

    /**
     * Exception'ni log qilish
     */
    private static function logException(Throwable $e, Request $request): void
    {
        Log::error('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['password', 'password_confirmation']),
        ]);
    }
} 
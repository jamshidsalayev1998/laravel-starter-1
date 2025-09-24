<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

abstract class Controller
{
    use ApiResponse;

    /**
     * Umumiy exception handling funksiyasi
     * 
     * @param Throwable $exception
     * @param string $defaultMessage
     * @param string $logContext
     * @return JsonResponse
     */
    protected function handleException(Throwable $exception, string $defaultMessage = 'Xatolik yuz berdi', string $logContext = 'general'): JsonResponse
    {
        // Xatolikni log qilish
        Log::error($exception->getMessage(), [
            'context' => $logContext,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ]);

        // Validation exception uchun maxsus handling
        if ($exception instanceof ValidationException) {
            return $this->validationErrorResponse($exception->errors(), $exception->getMessage());
        }

        // Development muhitida batafsil xatolik
        $message = app()->environment('production') ? $defaultMessage : $exception->getMessage();
        
        return $this->errorResponse($message, null, 500);
    }

    /**
     * Success response
     */
    protected function successResponse(string $message = 'Muvaffaqiyatli', $data = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Error response
     */
    protected function errorResponse(string $message = 'Xatolik', $errors = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors, string $message = 'Ma\'lumotlar noto\'g\'ri'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Not found response
     */
    protected function notFoundResponse(string $message = 'Ma\'lumot topilmadi'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse(string $message = 'Ruxsat berilmagan'): JsonResponse
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Forbidden response
     */
    protected function forbiddenResponse(string $message = 'Taqiqlangan'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Paginated response
     */
    protected function paginatedResponse($data, string $message = 'Ma\'lumotlar olindi'): JsonResponse
    {
        return $this->successResponse($message, [
            'items' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ]
        ]);
    }

    /**
     * Log user activity
     */
    protected function logActivity(string $action, $model = null, array $properties = []): void
    {
        $logData = [
            'user_id' => auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'properties' => $properties,
        ];

        if ($model) {
            $logData['model_type'] = get_class($model);
            $logData['model_id'] = $model->id ?? null;
        }

        Log::channel('activity')->info('User Activity', $logData);
    }

    /**
     * Validate request data with custom rules
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        try {
            return $request->validate($rules, $messages);
        } catch (ValidationException $e) {
            throw $e;
        }
    }

    /**
     * Check user permission
     */
    protected function checkPermission(string $permission): bool
    {
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Require permission or throw exception
     */
    protected function requirePermission(string $permission, string $message = 'Sizda bu amalni bajarish uchun ruxsat yo\'q'): void
    {
        if (!$this->checkPermission($permission)) {
            throw new \Illuminate\Auth\Access\AuthorizationException($message);
        }
    }

    /**
     * Get authenticated user with error handling
     */
    protected function getAuthenticatedUser()
    {
        $user = auth()->user();
        
        if (!$user) {
            throw new \Illuminate\Auth\AuthenticationException('Foydalanuvchi autentifikatsiya qilinmagan');
        }

        return $user;
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput(array $data): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim(strip_tags($value));
            }
            return $value;
        }, $data);
    }
}

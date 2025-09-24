<?php
namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

trait ApiResponse
{
    /**
     * API Success response with consistent format
     */
    protected function apiSuccess(string $message = 'Muvaffaqiyatli', $data = null, int $statusCode = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'status_code' => $statusCode,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * API Error response with consistent format
     */
    protected function apiError(string $message = 'Xatolik', $errors = null, int $statusCode = 400, array $meta = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'status_code' => $statusCode,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Paginated API response
     */
    protected function apiPaginated($paginator, string $message = 'Ma\'lumotlar olindi', array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more_pages' => $paginator->hasMorePages(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ]
            ]
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, 200);
    }

    /**
     * Collection response with pagination info
     */
    protected function apiCollection(Collection $collection, string $message = 'Ma\'lumotlar olindi', array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'data' => $collection,
            'count' => $collection->count(),
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, 200);
    }

    /**
     * Created resource response
     */
    protected function apiCreated(string $message = 'Ma\'lumot yaratildi', $data = null, array $meta = []): JsonResponse
    {
        return $this->apiSuccess($message, $data, 201, $meta);
    }

    /**
     * Updated resource response
     */
    protected function apiUpdated(string $message = 'Ma\'lumot yangilandi', $data = null, array $meta = []): JsonResponse
    {
        return $this->apiSuccess($message, $data, 200, $meta);
    }

    /**
     * Deleted resource response
     */
    protected function apiDeleted(string $message = 'Ma\'lumot o\'chirildi', array $meta = []): JsonResponse
    {
        return $this->apiSuccess($message, null, 200, $meta);
    }

    /**
     * No content response
     */
    protected function apiNoContent(string $message = 'Ma\'lumot topilmadi'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
            'status_code' => 204,
        ], 204);
    }

    /**
     * Validation error response for API
     */
    protected function apiValidationError($errors, string $message = 'Ma\'lumotlar noto\'g\'ri'): JsonResponse
    {
        return $this->apiError($message, $errors, 422, [
            'validation_errors' => true
        ]);
    }

    /**
     * Unauthorized response for API
     */
    protected function apiUnauthorized(string $message = 'Autentifikatsiya kerak'): JsonResponse
    {
        return $this->apiError($message, null, 401, [
            'auth_required' => true
        ]);
    }

    /**
     * Forbidden response for API
     */
    protected function apiForbidden(string $message = 'Ruxsat yo\'q'): JsonResponse
    {
        return $this->apiError($message, null, 403, [
            'permission_required' => true
        ]);
    }

    /**
     * Not found response for API
     */
    protected function apiNotFound(string $message = 'Ma\'lumot topilmadi'): JsonResponse
    {
        return $this->apiError($message, null, 404, [
            'not_found' => true
        ]);
    }

    /**
     * Rate limit exceeded response
     */
    protected function apiRateLimitExceeded(string $message = 'Juda ko\'p so\'rov yuborildi'): JsonResponse
    {
        return $this->apiError($message, null, 429, [
            'rate_limit_exceeded' => true,
            'retry_after' => 60 // seconds
        ]);
    }

    /**
     * Server error response for API
     */
    protected function apiServerError(string $message = 'Server xatoligi'): JsonResponse
    {
        return $this->apiError($message, null, 500, [
            'server_error' => true
        ]);
    }

    /**
     * Maintenance mode response
     */
    protected function apiMaintenanceMode(string $message = 'Texnik xizmat ko\'rsatilmoqda'): JsonResponse
    {
        return $this->apiError($message, null, 503, [
            'maintenance_mode' => true
        ]);
    }

    /**
     * Add metadata to response
     */
    protected function addMetaData(array $response, array $meta): array
    {
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }
        return $response;
    }

    /**
     * Transform data using resource
     */
    protected function transformData($data, $resourceClass = null): array
    {
        if ($resourceClass && class_exists($resourceClass)) {
            if (is_iterable($data)) {
                return $resourceClass::collection($data)->resolve();
            }
            return (new $resourceClass($data))->resolve();
        }

        return $data;
    }
} 
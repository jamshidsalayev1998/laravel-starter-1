<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // API middleware
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Spatie Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.ban' => \App\Http\Middleware\CheckUserBan::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API request'lar uchun JSON response
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return \App\Providers\AppServiceProvider::handleApiException($e, $request);
            }
        });

        // Validation exception'lar uchun
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ma\'lumotlar noto\'g\'ri',
                    'errors' => $e->errors(),
                    'timestamp' => now()->toISOString(),
                    'status_code' => 422,
                ], 422);
            }
        });

        // Authentication exception'lar uchun
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentifikatsiya kerak',
                    'timestamp' => now()->toISOString(),
                    'status_code' => 401,
                    'meta' => ['auth_required' => true]
                ], 401);
            }
        });

        // Authorization exception'lar uchun
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ruxsat yo\'q',
                    'timestamp' => now()->toISOString(),
                    'status_code' => 403,
                    'meta' => ['permission_required' => true]
                ], 403);
            }
        });

        // Model not found exception'lar uchun
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ma\'lumot topilmadi',
                    'timestamp' => now()->toISOString(),
                    'status_code' => 404,
                    'meta' => ['not_found' => true]
                ], 404);
            }
        });

        // Not found HTTP exception'lar uchun
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sahifa topilmadi',
                    'timestamp' => now()->toISOString(),
                    'status_code' => 404,
                    'meta' => ['not_found' => true]
                ], 404);
            }
        });

        // Method not allowed exception'lar uchun
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'So\'rov usuli noto\'g\'ri',
                    'timestamp' => now()->toISOString(),
                    'status_code' => 405,
                    'meta' => ['method_not_allowed' => true]
                ], 405);
            }
        });

        // Rate limit exception'lar uchun
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
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
        });

        // Too many requests exception'lar uchun
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
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
        });
    })->create();


<?php

namespace App\Providers;

use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL uchun max key length sozlash (eski MySQL versiyalari uchun)
        Schema::defaultStringLength(191);

        Vite::prefetch(concurrency: 3);
    }

    /**
     * API exception'larini boshqarish funksiyasi
     */
    public static function handleApiException(Throwable $e, Request $request)
    {
        // Log xatolikni
        \Log::error('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

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
}

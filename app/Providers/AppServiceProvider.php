<?php

namespace App\Providers;

use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
}

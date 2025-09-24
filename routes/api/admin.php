<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Bu fayl admin va super-admin foydalanuvchilar uchun route'larni o'z ichiga oladi
|
*/

Route::middleware(['auth:sanctum', 'role:admin|super-admin'])->group(function () {
    // Admin specific routes will be added here
    // Masalan:
    // Route::apiResource('/users', UserController::class);
    // Route::get('/properties', [PropertyController::class, 'adminIndex']);
    // Route::put('/properties/{id}/approve', [PropertyController::class, 'approve']);
    // Route::get('/bookings', [BookingController::class, 'adminIndex']);
    // Route::get('/dashboard', [DashboardController::class, 'adminDashboard']);
});

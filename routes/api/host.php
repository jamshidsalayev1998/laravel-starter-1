<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Host API Routes
|--------------------------------------------------------------------------
|
| Bu fayl host (uy egasi) foydalanuvchilar uchun route'larni o'z ichiga oladi
|
*/

Route::middleware(['auth:sanctum', 'role:host'])->group(function () {
    // Host specific routes will be added here
    // Masalan:
    // Route::apiResource('/properties', PropertyController::class);
    // Route::get('/bookings', [BookingController::class, 'hostBookings']);
    // Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    // Route::get('/dashboard', [DashboardController::class, 'hostDashboard']);
});

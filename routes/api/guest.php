<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest API Routes
|--------------------------------------------------------------------------
|
| Bu fayl guest (mehmon) foydalanuvchilar uchun route'larni o'z ichiga oladi
|
*/

Route::middleware(['auth:sanctum', 'role:guest'])->group(function () {
    // Guest specific routes will be added here
    // Masalan:
    // Route::get('/properties', [PropertyController::class, 'index']);
    // Route::get('/properties/{id}', [PropertyController::class, 'show']);
    // Route::post('/bookings', [BookingController::class, 'store']);
    // Route::get('/bookings', [BookingController::class, 'index']);
});

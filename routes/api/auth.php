<?php

use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
|
| Bu fayl authentication bilan bog'liq barcha route'larni o'z ichiga oladi
|
*/

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

// Protected auth routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Device management routes
    Route::get('/devices', [AuthController::class, 'getDevices']);
    Route::delete('/devices/{sessionId}', [AuthController::class, 'revokeDevice']);
    Route::delete('/devices', [AuthController::class, 'revokeAllDevices']);
});

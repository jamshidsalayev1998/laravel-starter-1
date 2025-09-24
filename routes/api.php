<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth routes
Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// Guest routes
Route::prefix('guest')->group(base_path('routes/api/guest.php'));

// Host routes
Route::prefix('host')->group(base_path('routes/api/host.php'));

// Admin routes
Route::prefix('admin')->group(base_path('routes/api/admin.php'));

// Super Admin routes
Route::prefix('super-admin')->group(base_path('routes/api/super-admin.php'));

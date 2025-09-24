<?php

use App\Http\Controllers\Api\SuperAdmin\UserManagementController;
use App\Http\Controllers\Api\SuperAdmin\RoleController;
use App\Http\Controllers\Api\SuperAdmin\PermissionController;
use App\Http\Controllers\Api\SuperAdmin\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin API Routes
|--------------------------------------------------------------------------
|
| Bu fayl super-admin foydalanuvchilar uchun route'larni o'z ichiga oladi
|
*/

Route::middleware(['auth:sanctum', 'role:super-admin', 'check.ban'])->group(function () {
    // User Management routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserManagementController::class, 'index']);
        Route::get('/banned', [UserManagementController::class, 'getBannedUsers']);
        Route::get('/{user}', [UserManagementController::class, 'show']);
        Route::post('/{user}/ban', [UserManagementController::class, 'banUser']);
        Route::post('/{user}/unban', [UserManagementController::class, 'unbanUser']);
        Route::post('/{user}/activate', [UserManagementController::class, 'activateUser']);
        Route::post('/{user}/deactivate', [UserManagementController::class, 'deactivateUser']);
    });

    // Role Management routes
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::get('/available', [RoleController::class, 'getAvailablePermissions']);
        Route::get('/{role}', [RoleController::class, 'show']);
        Route::post('/', [RoleController::class, 'store']);
        Route::put('/{role}', [RoleController::class, 'update']);
        Route::delete('/{role}', [RoleController::class, 'destroy']);
        Route::get('/{role}/permissions', [RoleController::class, 'getPermissions']);
        Route::post('/{role}/permissions', [RoleController::class, 'assignPermissions']);
    });

    // Permission Management routes
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']);
        Route::get('/groups', [PermissionController::class, 'getGroups']);
        Route::get('/{permission}', [PermissionController::class, 'show']);
        Route::post('/', [PermissionController::class, 'store']);
        Route::post('/bulk', [PermissionController::class, 'bulkCreate']);
        Route::put('/{permission}', [PermissionController::class, 'update']);
        Route::delete('/{permission}', [PermissionController::class, 'destroy']);
    });

    // User Role & Permission Management routes
    Route::prefix('user-roles')->group(function () {
        Route::get('/available', [UserRoleController::class, 'getAvailableRoles']);
        Route::get('/permissions/available', [UserRoleController::class, 'getAvailablePermissions']);
        Route::get('/{user}/roles-permissions', [UserRoleController::class, 'getUserRolesAndPermissions']);
        Route::post('/{user}/assign-role', [UserRoleController::class, 'assignRole']);
        Route::delete('/{user}/roles/{role}', [UserRoleController::class, 'removeRole']);
        Route::post('/{user}/sync-roles', [UserRoleController::class, 'syncRoles']);
        Route::post('/{user}/assign-permission', [UserRoleController::class, 'assignPermission']);
        Route::delete('/{user}/permissions/{permission}', [UserRoleController::class, 'removePermission']);
        Route::post('/{user}/sync-permissions', [UserRoleController::class, 'syncPermissions']);
    });

    // Other Super Admin routes will be added here
    // Masalan:
    // Route::get('/system-settings', [SystemController::class, 'index']);
    // Route::put('/system-settings', [SystemController::class, 'update']);
    // Route::get('/audit-logs', [AuditController::class, 'index']);
});

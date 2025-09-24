<?php

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdmin\RoleManagementController;
use App\Http\Controllers\SuperAdmin\UserAccessController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Devices route
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
});

// Admin routes
Route::middleware(['auth', 'role:admin|super-admin'])->prefix('admin')->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');

    // User management actions
    Route::post('/users/{user}/ban', [UserManagementController::class, 'banUser'])->name('admin.users.ban');
    Route::post('/users/{user}/unban', [UserManagementController::class, 'unbanUser'])->name('admin.users.unban');
    Route::post('/users/{user}/activate', [UserManagementController::class, 'activateUser'])->name('admin.users.activate');
    Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivateUser'])->name('admin.users.deactivate');
});

// Super Admin routes
Route::middleware(['auth', 'role:super-admin'])->prefix('super-admin')->group(function () {
    Route::get('/roles', [RoleManagementController::class, 'index'])->name('super-admin.roles.index');
    Route::get('/roles/create', [RoleManagementController::class, 'create'])->name('super-admin.roles.create');
    Route::post('/roles', [RoleManagementController::class, 'store'])->name('super-admin.roles.store');
    Route::get('/roles/{role}/edit', [RoleManagementController::class, 'edit'])->name('super-admin.roles.edit');
    Route::patch('/roles/{role}', [RoleManagementController::class, 'update'])->name('super-admin.roles.update');
    Route::get('/roles/{role}', [RoleManagementController::class, 'show'])->name('super-admin.roles.show');
    Route::post('/roles/{role}/permissions', [RoleManagementController::class, 'updateRolePermissions'])->name('super-admin.roles.permissions.update');
    Route::get('/permissions', [RoleManagementController::class, 'permissions'])->name('super-admin.permissions.index');

    // User access management
    Route::get('/users/{user}/access', [UserAccessController::class, 'show'])->name('super-admin.users.access');
    Route::post('/users/{user}/roles/attach', [UserAccessController::class, 'attachRole'])->name('super-admin.users.roles.attach');
    Route::post('/users/{user}/roles/detach', [UserAccessController::class, 'detachRole'])->name('super-admin.users.roles.detach');
    Route::post('/users/{user}/permissions/grant', [UserAccessController::class, 'grantPermission'])->name('super-admin.users.permissions.grant');
    Route::post('/users/{user}/permissions/revoke', [UserAccessController::class, 'revokePermission'])->name('super-admin.users.permissions.revoke');
});

require __DIR__.'/auth.php';

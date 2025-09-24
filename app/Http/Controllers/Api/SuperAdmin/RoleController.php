<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Database\OptimizedQueryService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Api\SuperAdmin\CreateRoleRequest;
use App\Http\Requests\Api\SuperAdmin\UpdateRoleRequest;
use App\Http\Requests\Api\SuperAdmin\AssignPermissionToRoleRequest;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected OptimizedQueryService $queryService;

    public function __construct(OptimizedQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Get all roles with pagination (optimized)
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search', 'guard']);
            $perPage = $request->get('per_page', 15);
            
            $roles = $this->queryService->getRolesWithOptimizedRelations($filters, $perPage);

            return $this->apiPaginated($roles, 'Rollar muvaffaqiyatli olindi');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rollarni olishda xatolik', 'role_management');
        }
    }

    /**
     * Get specific role details (optimized)
     */
    public function show(Role $role)
    {
        try {
            $role->load(['permissions:id,name,guard_name']);

            return $this->apiSuccess('Rol ma\'lumotlari olindi', [
                'role' => $role,
                'permissions' => $role->permissions,
                'permissions_count' => $role->permissions->count(),
                'users_count' => $role->users()->count(),
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rol ma\'lumotlarini olishda xatolik', 'role_management');
        }
    }

    /**
     * Create new role
     */
    public function store(CreateRoleRequest $request)
    {
        try {
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            // Assign permissions if provided
            if ($request->has('permissions')) {
                $role->givePermissionTo($request->permissions);
            }

            $role->load(['permissions:id,name,guard_name']);

            // Clear cache
            $this->queryService->clearCache('roles');

            return $this->apiCreated('Rol muvaffaqiyatli yaratildi', [
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rol yaratishda xatolik', 'role_management');
        }
    }

    /**
     * Update role
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            // Prevent updating super-admin role
            if ($role->name === 'super-admin') {
                return $this->apiForbidden('Super-admin rolini yangilash mumkin emas');
            }

            $role->update([
                'name' => $request->name,
            ]);

            $role->load(['permissions:id,name,guard_name']);

            // Clear cache
            $this->queryService->clearCache('roles');

            return $this->apiUpdated('Rol muvaffaqiyatli yangilandi', [
                'role' => $role,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rolni yangilashda xatolik', 'role_management');
        }
    }

    /**
     * Delete role
     */
    public function destroy(Role $role)
    {
        try {
            // Prevent deleting super-admin role
            if ($role->name === 'super-admin') {
                return $this->apiForbidden('Super-admin rolini o\'chirish mumkin emas');
            }

            // Check if role has users
            if ($role->users()->count() > 0) {
                return $this->apiError('Foydalanuvchilari bo\'lgan rolni o\'chirish mumkin emas', null, 400);
            }

            $role->delete();

            // Clear cache
            $this->queryService->clearCache('roles');

            return $this->apiDeleted('Rol muvaffaqiyatli o\'chirildi');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rolni o\'chirishda xatolik', 'role_management');
        }
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissions(AssignPermissionToRoleRequest $request, Role $role)
    {
        try {
            // Prevent modifying super-admin role permissions
            if ($role->name === 'super-admin') {
                return $this->apiForbidden('Super-admin rol ruxsatlarini o\'zgartirish mumkin emas');
            }

            $role->syncPermissions($request->permissions);
            $role->load(['permissions:id,name,guard_name']);

            // Clear cache
            $this->queryService->clearCache('roles');

            return $this->apiSuccess('Rol ruxsatlari muvaffaqiyatli tayinlandi', [
                'role' => $role,
                'permissions' => $role->permissions,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rol ruxsatlarini tayinlashda xatolik', 'role_management');
        }
    }

    /**
     * Get role permissions
     */
    public function getPermissions(Role $role)
    {
        try {
            $permissions = $role->permissions()->select(['id', 'name', 'guard_name'])->get();

            return $this->apiSuccess('Rol ruxsatlari olindi', [
                'role' => $role,
                'permissions' => $permissions,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Rol ruxsatlarini olishda xatolik', 'role_management');
        }
    }

    /**
     * Get all available permissions (optimized)
     */
    public function getAvailablePermissions()
    {
        try {
            $permissions = Permission::select(['id', 'name', 'guard_name'])
                ->orderBy('name')
                ->get();

            return $this->apiSuccess('Mavjud ruxsatlar olindi', [
                'permissions' => $permissions,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Ruxsatlarni olishda xatolik', 'role_management');
        }
    }
}

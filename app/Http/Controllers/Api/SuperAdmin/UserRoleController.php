<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Api\SuperAdmin\AssignRoleToUserRequest;
use App\Http\Requests\Api\SuperAdmin\AssignPermissionToUserRequest;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    use ApiResponse;

    /**
     * Assign role to user
     */
    public function assignRole(AssignRoleToUserRequest $request, User $user)
    {
        try {
            // Prevent assigning roles to super-admin
            if ($user->hasRole('super-admin')) {
                return $this->errorResponse('Cannot modify super-admin user roles', null, 403);
            }

            $user->assignRole($request->role);

            $user->load('roles');

            return $this->successResponse(
                'Role assigned to user successfully',
                [
                    'user' => $user,
                    'roles' => $user->roles,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user, Role $role)
    {
        try {
            // Prevent removing roles from super-admin
            if ($user->hasRole('super-admin')) {
                return $this->errorResponse('Cannot modify super-admin user roles', null, 403);
            }

            // Prevent removing the last role
            if ($user->roles()->count() <= 1) {
                return $this->errorResponse('User must have at least one role', null, 400);
            }

            $user->removeRole($role);

            $user->load('roles');

            return $this->successResponse(
                'Role removed from user successfully',
                [
                    'user' => $user,
                    'roles' => $user->roles,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Sync user roles (replace all roles)
     */
    public function syncRoles(Request $request, User $user)
    {
        try {
            $request->validate([
                'roles' => 'required|array|min:1',
                'roles.*' => 'string|exists:roles,name',
            ]);

            // Prevent modifying super-admin
            if ($user->hasRole('super-admin')) {
                return $this->errorResponse('Cannot modify super-admin user roles', null, 403);
            }

            $user->syncRoles($request->roles);

            $user->load('roles');

            return $this->successResponse(
                'User roles synchronized successfully',
                [
                    'user' => $user,
                    'roles' => $user->roles,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Assign permission to user
     */
    public function assignPermission(AssignPermissionToUserRequest $request, User $user)
    {
        try {
            $user->givePermissionTo($request->permission);

            $user->load(['roles', 'permissions']);

            return $this->successResponse(
                'Permission assigned to user successfully',
                [
                    'user' => $user,
                    'permissions' => $user->permissions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Remove permission from user
     */
    public function removePermission(Request $request, User $user, Permission $permission)
    {
        try {
            $user->revokePermissionTo($permission);

            $user->load(['roles', 'permissions']);

            return $this->successResponse(
                'Permission removed from user successfully',
                [
                    'user' => $user,
                    'permissions' => $user->permissions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Sync user permissions (replace all direct permissions)
     */
    public function syncPermissions(Request $request, User $user)
    {
        try {
            $request->validate([
                'permissions' => 'array',
                'permissions.*' => 'string|exists:permissions,name',
            ]);

            $user->syncPermissions($request->permissions ?? []);

            $user->load(['roles', 'permissions']);

            return $this->successResponse(
                'User permissions synchronized successfully',
                [
                    'user' => $user,
                    'permissions' => $user->permissions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Get user roles and permissions
     */
    public function getUserRolesAndPermissions(User $user)
    {
        try {
            $user->load(['roles.permissions', 'permissions']);

            // Get all permissions (from roles + direct)
            $rolePermissions = $user->roles->flatMap->permissions;
            $directPermissions = $user->permissions;
            $allPermissions = $rolePermissions->merge($directPermissions)->unique('id');

            return $this->successResponse(
                'User roles and permissions retrieved successfully',
                [
                    'user' => $user,
                    'roles' => $user->roles,
                    'direct_permissions' => $user->permissions,
                    'all_permissions' => $allPermissions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Get all available roles
     */
    public function getAvailableRoles()
    {
        try {
            $roles = Role::with('permissions')->get();

            return $this->successResponse(
                'Available roles retrieved successfully',
                [
                    'roles' => $roles,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Get all available permissions
     */
    public function getAvailablePermissions()
    {
        try {
            $permissions = Permission::orderBy('name')->get();

            return $this->successResponse(
                'Available permissions retrieved successfully',
                [
                    'permissions' => $permissions,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}

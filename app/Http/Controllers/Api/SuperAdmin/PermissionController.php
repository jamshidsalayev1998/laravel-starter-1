<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Api\SuperAdmin\CreatePermissionRequest;
use App\Http\Requests\Api\SuperAdmin\UpdatePermissionRequest;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;

    /**
     * Get all permissions with pagination
     */
    public function index(Request $request)
    {
        try {
            $query = Permission::query();

            // Search by name
            if ($request->has('search')) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            // Filter by group (e.g., users.*, properties.*)
            if ($request->has('group')) {
                $group = $request->group;
                $query->where('name', 'like', "{$group}%");
            }

            $permissions = $query->orderBy('name')
                               ->paginate($request->get('per_page', 15));

            return $this->successResponse(
                'Permissions retrieved successfully',
                [
                    'permissions' => $permissions->items(),
                    'pagination' => [
                        'current_page' => $permissions->currentPage(),
                        'last_page' => $permissions->lastPage(),
                        'per_page' => $permissions->perPage(),
                        'total' => $permissions->total(),
                    ]
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Get specific permission details
     */
    public function show(Permission $permission)
    {
        try {
            $permission->load('roles');

            return $this->successResponse(
                'Permission details retrieved successfully',
                [
                    'permission' => $permission,
                    'roles' => $permission->roles,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Create new permission
     */
    public function store(CreatePermissionRequest $request)
    {
        try {
            $permission = Permission::create([
                'name' => $request->name,
                'guard_name' => 'api',
            ]);

            return $this->successResponse(
                'Permission created successfully',
                [
                    'permission' => $permission,
                ],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Update permission
     */
    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        try {
            $permission->update([
                'name' => $request->name,
            ]);

            return $this->successResponse(
                'Permission updated successfully',
                [
                    'permission' => $permission,
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Delete permission
     */
    public function destroy(Permission $permission)
    {
        try {
            // Check if permission is assigned to any roles
            if ($permission->roles()->count() > 0) {
                return $this->errorResponse('Cannot delete permission that is assigned to roles', null, 400);
            }

            $permission->delete();

            return $this->successResponse('Permission deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Get permission groups
     */
    public function getGroups()
    {
        try {
            $permissions = Permission::all();
            $groups = [];

            foreach ($permissions as $permission) {
                $parts = explode('.', $permission->name);
                if (count($parts) >= 2) {
                    $group = $parts[0];
                    if (!isset($groups[$group])) {
                        $groups[$group] = [
                            'name' => $group,
                            'display_name' => ucfirst($group),
                            'count' => 0,
                        ];
                    }
                    $groups[$group]['count']++;
                }
            }

            return $this->successResponse(
                'Permission groups retrieved successfully',
                [
                    'groups' => array_values($groups),
                ]
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    /**
     * Bulk create permissions
     */
    public function bulkCreate(Request $request)
    {
        try {
            $request->validate([
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'required|string|unique:permissions,name',
            ]);

            $createdPermissions = [];

            foreach ($request->permissions as $permissionName) {
                $permission = Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'api',
                ]);
                $createdPermissions[] = $permission;
            }

            return $this->successResponse(
                'Permissions created successfully',
                [
                    'permissions' => $createdPermissions,
                ],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
}

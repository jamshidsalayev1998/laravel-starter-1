<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthServiceInterface;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleManagementController extends Controller
{
    protected AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions');

        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->paginate(15);

        return Inertia::render('SuperAdmin/RoleManagement', [
            'roles' => $roles,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users']);

        // Get all permissions grouped by module
        $allPermissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });

        return Inertia::render('SuperAdmin/RoleDetails', [
            'role' => $role,
            'allPermissions' => $allPermissions
        ]);
    }

    /**
     * Display permissions management.
     */
    public function permissions(Request $request)
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[0] ?? 'other';
        });

        return Inertia::render('SuperAdmin/PermissionManagement', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Show create role form
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/RoleCreate');
    }

    /**
     * Store new role
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $data['name']]);

        return redirect()->route('super-admin.roles.show', $role->id)
            ->with('success', 'Rol muvaffaqiyatli yaratildi');
    }

    /**
     * Show edit role form
     */
    public function edit(Role $role)
    {
        return Inertia::render('SuperAdmin/RoleEdit', [
            'role' => $role
        ]);
    }

    /**
     * Update role
     */
    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
        ]);

        $role->update(['name' => $data['name']]);

        return redirect()->route('super-admin.roles.show', $role->id)
            ->with('success', 'Rol muvaffaqiyatli yangilandi');
    }
    /**
     * Update role permissions.
     */
    public function updateRolePermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['integer']
        ]);

        $permissionIds = $validated['permissions'] ?? [];
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        // Sync permissions
        $role->syncPermissions($permissions);

        return redirect()
            ->route('super-admin.roles.show', $role->id)
            ->with('success', 'Ruxsatlar muvaffaqiyatli saqlandi');
    }
}

<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserAccessController extends Controller
{
    public function show(User $user)
    {
        $user->load(['roles', 'permissions']);

        $allRoles = Role::all(['id', 'name']);
        $allPermissions = Permission::all(['id', 'name'])->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'other';
        });

        $grantedViaRoles = $user->getPermissionsViaRoles()->pluck('id');
        $directPermissionIds = $user->permissions->pluck('id');

        return Inertia::render('SuperAdmin/UserAccess', [
            'user' => $user,
            'roles' => $allRoles,
            'permissions' => $allPermissions,
            'grantedViaRoles' => $grantedViaRoles,
            'directPermissionIds' => $directPermissionIds,
        ]);
    }

    public function attachRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        if (!$user->hasRole($role->name)) {
            $user->assignRole($role->name);
        }

        return back()->with('success', 'Rol biriktirildi');
    }

    public function detachRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        if ($user->hasRole($role->name)) {
            $user->removeRole($role->name);
        }

        return back()->with('success', 'Rol olib tashlandi');
    }

    public function grantPermission(Request $request, User $user)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $permission = Permission::findOrFail($data['permission_id']);

        // Agar bu ruxsat userning rollari orqali allaqachon berilgan bo'lsa, bevosita bermaymiz
        $viaRoles = $user->getPermissionsViaRoles()->pluck('id');
        if ($viaRoles->contains($permission->id)) {
            return back()->with('info', 'Bu ruxsat rol orqali allaqachon berilgan');
        }

        if (!$user->permissions()->where('id', $permission->id)->exists()) {
            $user->givePermissionTo($permission->name);
        }

        return back()->with('success', 'Ruxsat berildi');
    }

    public function revokePermission(Request $request, User $user)
    {
        $data = $request->validate([
            'permission_id' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $permission = Permission::findOrFail($data['permission_id']);

        // Faqat bevosita berilgan ruxsatni qaytarib olamiz
        if ($user->permissions()->where('id', $permission->id)->exists()) {
            $user->revokePermissionTo($permission->name);
        }

        return back()->with('success', 'Ruxsat qaytarib olindi');
    }
}



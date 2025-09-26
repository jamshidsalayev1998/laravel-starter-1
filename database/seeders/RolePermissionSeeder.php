<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions - asosiy permissionlar
        $permissions = [
            // User management permissions
            'users.view',
            'users.edit',
            'users.ban',
            'users.unban',
            'users.activate',
            'users.deactivate',

            //Role management permissions
            'roles.view',
            'roles.edit',
            'roles.delete',
            'roles.create',
            'permissions.view',
            'permissions.edit',
            'permissions.delete',
            'permissions.create',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // Create roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
        $superAdmin->givePermissionTo(Permission::all());


    }
}

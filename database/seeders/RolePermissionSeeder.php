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
            // Asosiy permissionlar
            'properties.view',
            'properties.create',
            'properties.edit',
            'bookings.view',
            'bookings.create',
            'admin.dashboard',

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

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $admin->givePermissionTo([
            'properties.view',
            'bookings.view',
            'admin.dashboard',
            'users.view',
            'users.edit',
        ]);

        $host = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'api']);
        $host->givePermissionTo([
            'properties.view',
            'properties.create',
            'properties.edit',
            'bookings.view',
        ]);

        $guest = Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'api']);
        $guest->givePermissionTo([
            'properties.view',
            'bookings.create',
            'bookings.view',
        ]);
    }
}

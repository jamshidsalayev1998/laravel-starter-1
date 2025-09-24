<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create test users with different roles
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@staynow.uz'],
            [
                'name' => 'Super Admin',
                'phone' => '+998901234567',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole('super-admin');

        $admin = User::firstOrCreate(
            ['email' => 'admin@staynow.uz'],
            [
                'name' => 'Admin User',
                'phone' => '+998901234568',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        $host = User::firstOrCreate(
            ['email' => 'host@staynow.uz'],
            [
                'name' => 'Host User',
                'phone' => '+998901234569',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]
        );
        $host->assignRole('host');

        $guest = User::firstOrCreate(
            ['email' => 'guest@staynow.uz'],
            [
                'name' => 'Guest User',
                'phone' => '+998901234570',
                'password' => bcrypt('password'),
                'phone_verified_at' => now(),
            ]
        );
        $guest->assignRole('guest');
    }
}

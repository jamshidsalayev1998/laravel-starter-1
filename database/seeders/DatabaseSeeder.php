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


    }
}

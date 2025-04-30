<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin role
        Role::create([
            'name' => 'Admin',
            'guard_name' => 'api',
        ]);

        Role::create([
            'name' => 'Account',
            'guard_name' => 'api',
        ]);
    }
}

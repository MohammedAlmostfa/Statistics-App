<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        $permissions = [
            'user.list',
            'user.create',
            'user.update',
            'user.delete',
            'customer.delete',
            'product.delete',
            'productCategory.delete',
            'whatsappMessage.list',
            'activiteLog.list',
            'financialReport.list',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'api']);
        }



        $adminRole = Role::firstOrCreate([
            'name' => 'Admin',
            'guard_name' => 'api',
        ]);

        $accountantRole = Role::firstOrCreate([
            'name' => 'Accountant',
            'guard_name' => 'api',
        ]);


        $adminRole->givePermissionTo($permissions);
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\ReceiptSeeder;
use Database\Factories\ProductFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            // CustomerSeeder::class,
            ProductOriginSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
            //ReceiptSeeder::class,
            //PaymentSeeder::class,
        ]);
    }
}

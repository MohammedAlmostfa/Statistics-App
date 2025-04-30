<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductOrigin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'buying_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 20, 200),
            'dolar_buying_price' => $this->faker->randomFloat(2, 20, 200),
            'installment_price' => $this->faker->numberBetween(30, 300),
            'quantity' => $this->faker->numberBetween(1, 100),
            'user_id' => User::inRandomOrder()->first()?->id ?? 1,
            'origin_id' => ProductOrigin::inRandomOrder()->first()?->id ?? 1,
            'category_id' => ProductCategory::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}

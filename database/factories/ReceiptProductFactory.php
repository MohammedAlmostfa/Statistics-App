<?php
namespace Database\Factories;

use App\Models\ReceiptProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptProductFactory extends Factory
{
    protected $model = ReceiptProduct::class;

    public function definition(): array
    {
        return [
            'product_id' => rand(1, 10),
            'quantity' => rand(1, 3),
            'selling_price' => $this->faker->numberBetween(100, 500),
            'buying_price' => $this->faker->numberBetween(50, 100),
            'description' => $this->faker->sentence(),
        ];
    }
}

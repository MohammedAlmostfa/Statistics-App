<?php

namespace Database\Factories;

use App\Models\ReceiptProduct;
use App\Models\Product; // استيراد نموذج المنتج
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ReceiptProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

        $product = Product::inRandomOrder()->first();



        return [
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(1, 5),
            'selling_price' => 0,
            'buying_price' => 0,
            'description' => $this->faker->optional()->sentence(5),

        ];
    }
}

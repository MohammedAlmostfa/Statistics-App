<?php

namespace Database\Factories;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        return [
            'customer_id' => rand(1, 10),
            'receipt_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'type' => $this->faker->randomElement(['اقساط', 'نقدي']), // نصوص بدل الأرقام
            'total_price' => $this->faker->numberBetween(100, 5000),
            'receipt_date' =>"2025-4-12",
            'user_id' => 1,
            'notes' => $this->faker->sentence(),
        ];
    }
}

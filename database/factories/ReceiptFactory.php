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
            'type' => rand(0, 1), // 0: اقساط, 1: نقدي
            'total_price' => 0, // سيتحدد لاحقاً
            'receipt_date' => $this->faker->date(),
            'user_id' =>1,
            'notes' => $this->faker->sentence(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            "amount" => $this->faker->randomFloat(2, 20, 200),
            'payment_date' => $this->faker->dateTimeThisYear('+1 day')->format('Y-m-d'),
            'details' => $this->faker->sentence(),
            'user_id' => 1,
        ];
    }
}

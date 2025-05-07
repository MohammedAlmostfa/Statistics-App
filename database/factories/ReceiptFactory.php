<?php

namespace Database\Factories;

use App\Models\Receipt;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class ReceiptFactory extends Factory
{
    protected $model = Receipt::class;

    public function definition(): array
    {
        $customer = Customer::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        if (!$customer || !$user) {
            throw new \Exception('Please seed Users and Customers tables before seeding Receipts.');
        }

        $types = array_keys(Receipt::TYPE_MAP);
        $randomType = $this->faker->randomElement($types);

        return [
            'customer_id' => $customer->id,
            'receipt_number' => $this->faker->unique()->numberBetween(1000, 999999),
            'type' => $randomType,

            'total_price' => $this->faker->numberBetween(1000, 500000), // قيمة عشوائية كبيرة نسبياً
            'receipt_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'user_id' => $user->id,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => array_search('نقدي', Receipt::TYPE_MAP),
        ]);
    }

    public function installment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => array_search('اقساط', Receipt::TYPE_MAP),
        ]);
    }
}

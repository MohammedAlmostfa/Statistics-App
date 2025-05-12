<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'phone' => "+963991851269",
            'notes' => $this->faker->sentence(),
            'sponsor_name' => $this->faker->name(),
            'sponsor_phone' => $this->faker->numerify('05########'),
            'Record_id' => $this->faker->numberBetween(1000, 9999),
            'Page_id' => $this->faker->numberBetween(1, 500),
        ];
    }

}

<?php

namespace Database\Factories;

use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB; // لاستخدام array_search

class InstallmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Installment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {

        $types = array_keys(Installment::TYPE_MAP);
        $randomType = $this->faker->randomElement($types);

        return [

            'pay_cont' => $this->faker->numberBetween(2, 12),
            'first_pay' => 0,
            'installment' => 0,
            'installment_type' => $randomType,
        ];
    }
}

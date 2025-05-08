<?php
namespace Database\Factories;

use App\Models\Installment;
use Illuminate\Database\Eloquent\Factories\Factory;

class InstallmentFactory extends Factory
{
    protected $model = Installment::class;

    public function definition(): array
    {
        $typeText = $this->faker->randomElement(array_values(Installment::TYPE_MAP));

        return [
            'pay_cont' => rand(2, 5),
            'installment' => rand(100, 300),
            'first_pay' => rand(100, 300),
            'installment_type' => $typeText,
        ];
    }
}

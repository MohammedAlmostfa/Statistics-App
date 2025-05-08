<?php
namespace Database\Factories;

use App\Models\InstallmentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InstallmentPaymentFactory extends Factory
{
    protected $model = InstallmentPayment::class;

    public function definition(): array
    {
        return [
            'payment_date' => now(),
            'amount' => 100,
            'status' => rand(0, 1),

        ];
    }
}

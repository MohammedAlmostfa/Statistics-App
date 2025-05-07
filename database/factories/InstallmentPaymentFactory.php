<?php

namespace Database\Factories;

use App\Models\InstallmentPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB; // لاستخدام array_search
use Carbon\Carbon; // لاستخدام دوال التاريخ

class InstallmentPaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InstallmentPayment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        // اختيار حالة دفعة عشوائية وتخزين القيمة العددية
        $statuses = array_keys(InstallmentPayment::TYPE_MAP);
        $randomStatus = $this->faker->randomElement($statuses);

        return [
             // 'installment_id' سيتم تعيينه تلقائياً عند إنشاء العلاقة
            'payment_date' => $this->faker->dateTimeBetween('-3 months', '+3 months'), // تاريخ دفعة عشوائي قريب
            'amount' => $this->faker->numberBetween(10, 500), // مبلغ دفعة عشوائي (هذا المبلغ سيكون مؤقتاً)
            'status' => $randomStatus, // تخزين القيمة العددية
        ];
    }
}

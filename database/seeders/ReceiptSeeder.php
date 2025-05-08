<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Receipt;
use App\Models\ReceiptProduct;
use App\Models\Installment;
use App\Models\InstallmentPayment;
use Illuminate\Support\Carbon;

class ReceiptSeeder extends Seeder
{
    public function run(): void
    {

        Receipt::factory()
            ->count(50)
            ->state(['type' => 'نقدي'])
            ->create()
            ->each(function ($receipt) {
                $total = 0;
                $products = ReceiptProduct::factory()->count(rand(1, 3))->make();
                foreach ($products as $product) {
                    $product->receipt_id = $receipt->id;
                    $product->save();
                    $total += $product->quantity * $product->selling_price;
                }
                $receipt->update(['total_price' => $total]);
            });


        Receipt::factory()
            ->count(50)
            ->state(['type' => 'أقساط'])
            ->create()
            ->each(function ($receipt) {
                $total = 0;
                $products = ReceiptProduct::factory()->count(rand(1, 2))->make();
                foreach ($products as $product) {
                    $product->receipt_id = $receipt->id;
                    $product->save();
                    $total += $product->quantity * $product->selling_price;


                    $installment = Installment::factory()->make();
                    $installment->receipt_product_id = $product->id;
                    $installment->save();

                    for ($i = 0; $i < $installment->pay_cont; $i++) {
                        InstallmentPayment::factory()->create([
                            'installment_id' => $installment->id,
                            'payment_date' => Carbon::now()->addWeeks($i),
                            'amount' => $installment->installment,
                        ]);
                    }
                    break;
                }

                $receipt->update(['total_price' => $total]);
            });
    }
}

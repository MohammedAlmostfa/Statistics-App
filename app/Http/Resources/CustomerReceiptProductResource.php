<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerReceiptProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstProduct = $this->whenLoaded('receiptProducts', function () {
            return $this->receiptProducts->first();
        });

        $firstInstallment = null;
        if ($firstProduct && $firstProduct->relationLoaded('installment')) {
            $firstInstallment = $firstProduct->installment;
        }

        return [
            'id' => $this->id, // معرف الإيصال
            'receipt_number' => $this->receipt_number,
            'receipt_date' => $this->receipt_date->format('Y-m-d '),
            'product_id' => $firstProduct ? $firstProduct->product_id : null,
            'product_name' => $firstProduct ? $firstProduct->product->name : null,
               'product_price' => $firstProduct ? $firstProduct->product->installment_price : null,
            'quantity' => $firstProduct ? $firstProduct->quantity : null,
            'installment_price' => $firstProduct ? $firstProduct->product->installment_price : null,
            'pay_cont' => $firstInstallment ? $firstInstallment->pay_cont : null,
            'installment_type' => $firstInstallment ? $firstInstallment->installment_type : null,
            'installment_amount' => $firstInstallment ? $firstInstallment->installment : null,
            'first_payment' => ($firstInstallment && $firstInstallment->relationLoaded('firstInstallmentPayment')) ? $firstInstallment->firstInstallmentPayment->amount : null,
            'payments' => ($firstInstallment && $firstInstallment->relationLoaded('installmentPayments')) ? $firstInstallment->installmentPayments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                ];
            })->toArray() : [],
        ];
    }
}

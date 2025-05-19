<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerDebtResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'              => $this->id,
            'receipt_number'  => $this->receipt_number,
            'payment_amount'  => $this->payment_amount,
            'remaining_debt'  => $this->remaining_debt,
            'debt_date'       => $this->debt_date,
            'user_id'         => $this->user_id,
            'description'     => $this->description,
            'debt_payments'   => optional($this->debtPaymments)->map(function ($payment) {
                return [
                    'id'           => $payment->id,
                    'amount'       => $payment->amount,
                    'payment_date' => $payment->payment_date,
                ];
            }) ?? [],


        ];
    }
}

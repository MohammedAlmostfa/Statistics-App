<?php

namespace App\Rules;

use App\Models\Installment;
use Illuminate\Contracts\Validation\Rule;

class StoreValidInstallmentAmount implements Rule
{
    protected Installment $installment;

    public function __construct(Installment $installment)
    {
        $this->installment = $installment->load('installmentPayments', 'receiptProduct');
    }

    public function passes($attribute, $value): bool
    {

        if ($this->installment->status === 'مسدد') {
            return false;
        }

        $totalPaid = $this->installment->installmentPayments->sum('amount');

        if ($this->installment->first_pay) {
            $totalPaid += $this->installment->first_pay;
        }

        $totalInstallmentAmount = optional($this->installment->receiptProduct)->selling_price
                                * optional($this->installment->receiptProduct)->quantity ?? 0;

        $remainingAmount = max(0, $totalInstallmentAmount - $totalPaid);

        return $value <= $remainingAmount;
    }

    public function message(): string
    {
        return ' المبلغ المدفوع يتجاوز المبلغ المتبقي للقسط، يرجى إدخال مبلغ صحيح.';
    }
}

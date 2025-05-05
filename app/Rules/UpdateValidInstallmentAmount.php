<?php

namespace App\Rules;

use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Rule;

class UpdateValidInstallmentAmount implements Rule
{
    protected $installment;
    protected $installmentPayment;

    public function __construct(InstallmentPayment $installmentPayment)
    {
        $this->installmentPayment = $installmentPayment;
        $this->installment = $installmentPayment->installment;
    }

    public function passes($attribute, $value)
    {
        $totalPaid = $this->installment->installmentPayments()->sum('amount');

        // Add the first payment amount to the total paid amount
        if ($this->installment->first_pay) {
            $totalPaid += $this->installment->first_pay;
        }


        $totalInstallmentAmount = $this->installment->receiptProduct->product->installment_price *
                                   $this->installment->receiptProduct->quantity;

        $remainingAmount = $totalInstallmentAmount - $totalPaid+ $this->installmentPayment->amount;


        return $value <= $remainingAmount;
    }

    public function message(): string
    {
        return 'المبلغ المدفوع يتجاوز المبلغ المتبقي.';
    }
}

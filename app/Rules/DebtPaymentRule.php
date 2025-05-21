<?php

namespace App\Rules;

use App\Models\Debt;
use Illuminate\Contracts\Validation\Rule;

class DebtPaymentRule implements Rule
{
    protected $debt;
    protected $currentOutstandingAmount;

    public function __construct($debtId)
    {
        $this->debt = Debt::findOrFail($debtId);
    }

    public function passes($attribute, $value)
    {
        $totalPaidAmount = $this->debt->debtPayments()->sum('amount');
        $this->currentOutstandingAmount = $this->debt->remaining_debt - $totalPaidAmount;

        return $value <= $this->currentOutstandingAmount;
    }

    public function message()
    {
        return "المبلغ المسدد يجب أن يكون أقل أو يساوي القيمة المتبقية وهي {$this->currentOutstandingAmount} دينار.";
    }
}

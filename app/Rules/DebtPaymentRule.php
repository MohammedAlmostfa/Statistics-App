<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Debt;

class DebtPaymentRule implements Rule
{
    protected $debt;

    public function __construct($debtId)
    {
        $this->debt = Debt::find($debtId);
    }

    public function passes($attribute, $value)
    {
        return $this->debt && $value <= $this->debt->remaining_debt;
    }

    public function message()
    {
        return 'المبلغ المسدد يجب أن يكون أقل أو يساوي القيمة المتبقية.';
    }
}

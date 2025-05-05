<?php


namespace App\Rules;

use App\Models\Installment;
use Illuminate\Contracts\Validation\Rule;

class StoreValidInstallmentAmount implements Rule
{
    protected $installment;

    /**
     * Create a new rule instance.
     *
     * @param  Installment  $installment
     * @return void
     */
    public function __construct(Installment $installment)
    {
        $this->installment = $installment;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $totalPaid = $this->installment->installmentPayments()->sum('amount');

        // Add the first payment amount to the total paid amount
        if ($this->installment->first_pay) {
            $totalPaid += $this->installment->first_pay;
        }


        $totalInstallmentAmount = $this->installment->receiptProduct->product->installment_price *
                                   $this->installment->receiptProduct->quantity;

        $remainingAmount = $totalInstallmentAmount - $totalPaid;

        return $value <= $remainingAmount;
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'المبلغ المدفوع يتجاوز المبلغ المتبقي.';
    }
}

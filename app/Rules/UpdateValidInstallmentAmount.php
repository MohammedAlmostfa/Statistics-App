<?php

namespace App\Rules;

use App\Models\InstallmentPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule to ensure that the updated amount for an installment payment
 * does not cause the total paid amount to exceed the total installment amount.
 *
 * @documented
 */
class UpdateValidInstallmentAmount implements Rule
{
    /**
     * The installment model instance.
     *
     * @var \App\Models\Installment
     */
    protected $installment;

    /**
     * The installment payment model instance being updated.
     *
     * @var \App\Models\InstallmentPayment
     */
    protected $installmentPayment;

    /**
     * Create a new rule instance.
     *
     * @param \App\Models\InstallmentPayment $installmentPayment The installment payment model being updated.
     * @return void
     *
     * @documented
     */
    public function __construct(InstallmentPayment $installmentPayment)
    {
        $this->installmentPayment = $installmentPayment;
        $this->installment = $installmentPayment->installment;
    }

    /**
     * Determine if the validation rule passes.
     *
     * This method calculates the total amount paid for the installment,
     * considering the current value of the installment payment being updated.
     * It then checks if the new payment amount (`$value`) would cause the
     * total paid amount to exceed the total installment amount.
     *
     * @param string $attribute The name of the validation attribute.
     * @param mixed $value The new value being set for the installment payment amount.
     * @return bool True if the updated payment amount is valid, false otherwise.
     *
     * @documented
     */
    public function passes($attribute, $value)
    {
        $totalPaid = $this->installment->installmentPayments()->sum('amount');
        if ($this->installment->first_pay) {
            $totalPaid += $this->installment->first_pay;
        }

        $totalInstallmentAmount = $this->installment->receiptProduct->selling_price *
                                       $this->installment->receiptProduct->quantity;


        // To check if the *new* value is valid, we subtract the *current* value
        // of this payment and add the *new* value.
        $remainingAmount = $totalInstallmentAmount - $totalPaid + $this->installmentPayment->amount;

        return $value <= $remainingAmount;
    }

    /**
     * Get the validation error message.
     *
     * @return string The localized validation error message.
     *
     * @documented
     */
    public function message(): string
    {

        return ' المبلغ المدفوع يتجاوز المبلغ المتبقي للقسط، يرجى إدخال مبلغ صحيح.';

    }
}

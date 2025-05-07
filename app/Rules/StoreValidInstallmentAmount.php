<?php

namespace App\Rules;

use App\Models\Installment;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule to ensure that the amount being stored for an installment payment
 * does not exceed the remaining balance of the installment.
 *
 * @documented
 */
class StoreValidInstallmentAmount implements Rule
{
    /**
     * The installment model instance.
     *
     * @var Installment
     */
    protected $installment;

    /**
     * Create a new rule instance.
     *
     * @param Installment $installment The installment model to validate against.
     * @return void
     *
     * @documented
     */
    public function __construct(Installment $installment)
    {
        $this->installment = $installment;
    }

    /**
     * Determine if the validation rule passes.
     *
     * This method calculates the total amount already paid for the installment
     * (including the first payment if applicable) and the total installment amount.
     * It then checks if the current payment amount (`$value`) is less than or equal
     * to the remaining balance.
     *
     * @param string $attribute The name of the validation attribute.
     * @param mixed $value The value of the validation attribute (the payment amount).
     * @return bool True if the payment amount is valid, false otherwise.
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

        $remainingAmount = $totalInstallmentAmount - $totalPaid;

        return $value <= $remainingAmount;
    }


    /**
     * Get the validation error message.
     *
     * @return string The localized validation error message.
     *
     * @documented
     */
    public function message()
    {
        return 'المبلغ المدفوع يتجاوز المبلغ المتبقي.';
    }
}

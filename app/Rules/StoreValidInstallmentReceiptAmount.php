<?php

namespace App\Rules;

use App\Models\Customer;
use Illuminate\Contracts\Validation\Rule;

class StoreValidInstallmentReceiptAmount implements Rule
{
    protected $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer->load([
            'receipts.receiptProducts.installment.installmentPayments',
            'debts.debtPayments',
        ]);
    }

    public function passes($attribute, $value): bool
    {

        $installmentReceipts = $this->customer->receipts->where('type', 'اقساط');

        $totalRemainingFromInstallments = 0;

        foreach ($installmentReceipts as $receipt) {
            foreach ($receipt->receiptProducts as $product) {
                if ($product->installment) {
                    $price = $product->selling_price * $product->quantity;
                    $paid = $product->installment->first_pay + $product->installment->installmentPayments->sum('amount');
                    $remaining = max(0, $price - $paid);
                    $totalRemainingFromInstallments += $remaining;
                }
            }
        }

        $totalRemainingFromDebts = $this->customer->debts->sum(function ($debt) {
            $paid = $debt->debtPayments->sum('amount');
            return max(0, $debt->remaining_debt - $paid);
        });

        $totalRemaining = $totalRemainingFromInstallments + $totalRemainingFromDebts;

        return $value <= $totalRemaining;
    }

    public function message(): string
    {
        return 'المبلغ المدفوع يتجاوز مجموع المبالغ المتبقية من الأقساط والديون الخاصة بالزبون، يرجى إدخال مبلغ صحيح.';
    }
}

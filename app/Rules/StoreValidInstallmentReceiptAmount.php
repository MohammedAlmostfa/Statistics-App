<?php

namespace App\Rules;

use App\Models\Receipt;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Validation\Rule;

class StoreValidInstallmentReceiptAmount implements Rule
{
    protected $receipt;

    /**
     * إنشاء قاعدة التحقق مع تحميل العلاقات المرتبطة بالفاتورة
     */
    public function __construct(Receipt $receipt)
    {
        $this->receipt = $receipt->load('receiptProducts.installment.installmentPayments');
    }

    /**
     * تنفيذ التحقق
     */
    public function passes($attribute, $value): bool
    {

        $totalPrice = $this->receipt->total_price;


        $totalPaidAmount = $this->receipt->receiptProducts->sum(function ($product) {
            return $product->installment
                ? $product->installment->installmentPayments->sum('amount') + $product->installment->pay
                : 0;
        });
        Log::error("totalPaidAmount".$totalPaidAmount);



        $totalRemainingAmount = max(0, $totalPrice - $totalPaidAmount);
        Log::error("totalRemainingAmount".$totalRemainingAmount);

        return $value <= $totalRemainingAmount;
    }


    public function message(): string
    {
        return 'المبلغ المدفوع يتجاوز المبلغ المتبقي   لالسعر الإجمالي للفاتورة، يرجى إدخال مبلغ صحيح.';
    }
}

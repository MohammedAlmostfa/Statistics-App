<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\Receipt;
use Illuminate\Contracts\Validation\Rule;

class AvailableQuantityUpdate implements Rule
{
    protected int $productId;
    protected int $receiptId;

    public function __construct(int $productId, int $receiptId)
    {
        $this->productId = $productId;
        $this->receiptId = $receiptId;
    }

    public function passes($attribute, $value): bool
    {
        $product = Product::find($this->productId);
        $receipt = Receipt::find($this->receiptId);


        if (!$product || !$receipt) {
            return false;
        }

        $receiptProduct = optional($receipt->receiptProducts()->where('product_id', $this->productId)->first());

        $oldQuantityInReceipt = $receiptProduct->quantity ?? 0;
        $additionalQuantityNeeded = $value - $oldQuantityInReceipt;

        return $product->quantity >= $additionalQuantityNeeded;
    }

    public function message(): string
    {
        return " الكمية المطلوبة للمنتج غير متوفرة، يرجى إدخال كمية أقل.";
    }
}

<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

class FirstInstallmentAmountValid implements Rule
{
    protected $productId;
    protected $quantity;



    /**
     * Create a new rule instance.
     *
     * @param int $productId
     * @param int $quantity
     * @param string $receiptType
     * @return void
     */
    public function __construct(int $productId, int $quantity)
    {
        $this->productId = $productId;
        $this->quantity = $quantity;

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $first_pay = (int) $value;
        $product = Product::find($this->productId);
        $unitSellingPrice = $product->installment_price;
        $itemTotal = $this->quantity * $unitSellingPrice;
        return $first_pay <= $itemTotal;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {

        return ('الدفعة الاولى اكبر من  قيمة المنتجات');
    }
}

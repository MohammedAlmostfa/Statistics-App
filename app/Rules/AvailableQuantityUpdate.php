<?php

namespace App\Rules;

use App\Models\Product;
use App\Models\Receipt;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule to check if the requested quantity of a product is available in stock.
 *
 * @documented
 */
class AvailableQuantityUpdate implements Rule
{
    /**
     * The ID of the product being validated.
     *
     * @var int
     */
    protected $productId;

    /**
     * The ID of the receipt being updated.
     *
     * @var int
     */
    protected $receiptId;

    /**
     * Create a new rule instance.
     *
     * @param int $productId The ID of the product.
     * @param int $receiptId The ID of the receipt being updated.
     * @return void
     */
    public function __construct(int $productId, int $receiptId)
    {
        $this->productId = $productId;
        $this->receiptId = $receiptId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute The name of the validation attribute (e.g., 'quantity').
     * @param mixed $value The value of the validation attribute (the new requested quantity).
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail The failure callback.
     * @return void
     */
    public function passes($attribute, $value)
    {


        $product = Product::find($this->productId);
        $receipt = Receipt::find($this->receiptId);
        $receiptProduct = $receipt->receiptProducts()
                                  ->where('product_id', $this->productId)
                                  ->first();

        $oldQuantityInReceipt = $receiptProduct ? $receiptProduct->quantity : 0;
        $additionalQuantityNeeded = $value - $oldQuantityInReceipt;
        return $product->quantity >= $additionalQuantityNeeded;

    }




    /**
     * Get the validation error message.
     *
     * This method is less commonly used when using the `validate` method with Closure.
     * The failure message is provided directly to the `$fail` callback.
     * However, defining it here can serve as a default or for older Laravel versions.
     *
     * @return string
     */
    public function message(): string
    {

        return 'الكمية المطلوبة للمنتج غير متوفرة.';
    }


}

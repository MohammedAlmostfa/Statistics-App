<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validation rule to check if the requested quantity of a product is available in stock.
 *
 * @documented
 */
class AvailableQuantity implements Rule
{
    /**
     * The ID of the product to check the availability for.
     *
     * @var int
     */
    protected $productId;

    /**
     * Create a new rule instance.
     *
     * @param int $productId The ID of the product.
     * @return void
     *
     * @documented
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * This method retrieves the product based on the provided `$productId` and
     * checks if the product exists and if its current `quantity` is greater than
     * or equal to the `$value` (the requested quantity).
     *
     * @param string $attribute The name of the validation attribute (e.g., 'quantity').
     * @param mixed $value The value of the validation attribute (the requested quantity).
     * @return bool True if the requested quantity is available, false otherwise.
     *
     * @documented
     */
    public function passes($attribute, $value)
    {
        $product = Product::findOrFail($this->productId);

        if (!$product) {
            return false;
        }

        return $product->quantity >= $value;
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
        return "الكمية المطلوبة للمنتج  غير متوفرة.";
    }
}

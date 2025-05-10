<?php

namespace App\Rules;

use App\Models\Product;
use Illuminate\Contracts\Validation\Rule;

class AvailableQuantityStore implements Rule
{
    protected int $productId;

    public function __construct(int $productId)
    {
        $this->productId = $productId;
    }

    public function passes($attribute, $value): bool
    {
        $product = Product::find($this->productId);


        if (!$product) {
            return false;
        }

        return $product->quantity >= $value;
    }

    public function message(): string
    {
        return " الكمية المطلوبة للمنتج غير متوفرة، يرجى إدخال كمية أقل.";
    }
}

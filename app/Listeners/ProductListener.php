<?php

namespace App\Listeners;

use App\Models\Product;
use App\Events\ProductEvent;
use App\Models\ProductHistory;

/**
 * **ProductListener**
 *
 * Listens for `ProductEvent` to:
 * - Restore product details from history if needed.
 * - Log changes to product data before updating.
 * - Save the current product state into `ProductHistory` before modification.
 */
class ProductListener
{
    /**
     * **Handle product updates triggered by `ProductEvent`.**
     *
     * - If only `product_id` is received, it restores the last saved state.
     * - If complete product data is available, it logs history before updating.
     *
     * @param ProductEvent $event Contains product data.
     * @return void
     */
    public function handle(ProductEvent $event)
    {
        $productData = $event->product;


        // Retrieve the product from the database
        $product = Product::findOrFail($productData['product_id']);

        // If only `product_id` is provided, restore from `ProductHistory`
        if (count($productData) === 1) {
            $previousProduct = ProductHistory::where('product_id', $productData['product_id'])->latest()->first();

            if ($previousProduct) {
                $product->update([
                    'selling_price' => $previousProduct->selling_price,
                    'installment_price' => $previousProduct->installment_price,
                    'dolar_buying_price' => $previousProduct->dollar_buying_price,
                    'dollar_exchange' => $previousProduct->dollar_exchange,
                    'quantity' => $previousProduct->quantity,
                ]);
            }
        } else {
            // Save the current product state into `ProductHistory` before updating
            ProductHistory::create([
                'product_id' => $product->id,
                'selling_price' => $product->selling_price,
                'installment_price' => $product->installment_price,
                'dollar_buying_price' => $product->dolar_buying_price,
                'dollar_exchange' => $product->dollar_exchange,
                'quantity' => $product->quantity,
            ]);

            // Update product details
            $product->update([
                'installment_price' => $productData["installment_price"] ?? $product->installment_price,
                'selling_price' => $productData["selling_price"] ?? $product->selling_price,
                'dolar_buying_price' => $productData["dollar_buying_price"] ?? $product->dolar_buying_price,
                'dollar_exchange' => $productData["dollar_exchange"] ?? $product->dollar_exchange,
                'quantity' => isset($productData["quantity"]) ? $productData["quantity"] + $product->quantity : $product->quantity,
            ]);
        }
    }
}

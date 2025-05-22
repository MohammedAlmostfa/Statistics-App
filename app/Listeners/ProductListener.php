<?php

namespace App\Listeners;

use App\Events\ProductEvent;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class ProductListener
{
    public function handle(ProductEvent $event)
    {
        $productData = $event->product;
        Log::error('Product Event Triggered: ', ['product' => $productData]);

        $product = Product::findOrFail($productData['product_id']);

        $product->update([
            'selling_price' => $productData["selling_price"] ?? $product->selling_price,
            'dolar_buying_price' => $productData["dollar_buying_price"] ?? $product->dolar_buying_price,
            'dollar_exchange' => $productData["dollar_exchange"] ?? $product->dollar_exchange,
            'quantity' => $productData["quantity"]+$product->quantity ?? $product->quantity,
        ]);
    }
}

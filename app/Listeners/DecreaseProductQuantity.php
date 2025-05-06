<?php

namespace App\Listeners;

use App\Events\ReceiptCreated;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DecreaseProductQuantity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  \App\Events\ReceiptCreated  $event
     * @return void
     */
    public function handle(ReceiptCreated $event)
    {
        $productId = $event->productid;
        $quantity = $event->quantity;

        DB::transaction(function () use ($productId, $quantity) {
            $product = Product::lockForUpdate()->find($productId);

            if ($product && $product->quantity >= $quantity) {
                $product->decrement('quantity', $quantity);
                $product->save();
                Log::info("تم تخفيض كمية المنتج {$product->name} بمقدار {$quantity} بسبب فاتورة.");
            } else {
                Log::error("لا تتوفر كمية كافية من المنتج {$product->name} لتخفيضها بسبب فاتورة.");
                // يمكنك هنا اتخاذ إجراء إضافي مثل إعلام المسؤول أو عكس عملية الفاتورة إذا لزم الأمر.
                throw new \Exception("لا تتوفر كمية كافية من المنتج {$product->name} لتخفيضها.");
            }
        });
    }
}

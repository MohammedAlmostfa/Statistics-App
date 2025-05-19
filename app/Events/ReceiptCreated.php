<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReceiptCreated
{
    public $productid;
    public $quantity;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Product  $product
     * @param  int  $quantity
     * @return void
     */
    public function __construct($productid, int $quantity)
    {
        $this->productid = $productid;
        $this->quantity = $quantity;
    }

}

<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ProductEvent
{
    use Dispatchable, SerializesModels;

    public $product;

    public function __construct($product)
    {
        $this->product = $product;
    }
}

<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ProductEvent
{
    use Dispatchable, SerializesModels;
    public $product;
    public $type;
    public function __construct(array $product, string $type)
    {
        $this->product = $product;
        $this->type = $type;

    }
}

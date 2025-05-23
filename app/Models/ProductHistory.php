<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    protected $fillable = ['product_id', 'selling_price', 'installment_price','dolar_buying_price', 'dollar_exchange', 'quantity'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransactionsProduct extends Model
{
    protected $fillable = [
        'financial_id',
        'product_id',
        'selling_price',
        'dollar_buying_price',
        'dollar_exchange',
        'quantity',
        'installment_price'
    ];

    protected $casts = [
        'installment_price' => 'integer',
        'selling_price'=> 'float',
        'dollar_buying_price' => 'float',
        'dollar_exchange' => 'integer',
        'transaction_id' => 'integer',
        'product_id' => 'integer',

    ];
    public function financialTransactions()
    {
        return $this->belongsTo(FinancialTransactions::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

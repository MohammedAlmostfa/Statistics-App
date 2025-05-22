<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransactionsProduct extends Model
{
    protected $fillable = [
        'financial_transactions_id',
        'product_id',
        'selling_price',
        'dollar_buying_price',
        'dollar_exchange',
        'quantity',
    ];

    protected $casts = [

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FinancialTransactionsProduct
 *
 * Represents the products associated with a financial transaction.
 * Each product entry contains pricing details, exchange rates, quantity, and installment pricing.
 */
class FinancialTransactionsProduct extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * These fields can be updated or created using mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'financial_id',
        'product_id',
        'selling_price',
        'dollar_buying_price',
        'dollar_exchange',
        'quantity',
        'installment_price'
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * Ensures proper handling of values such as currency and identifiers.
     *
     * @var array
     */
    protected $casts = [
        'installment_price'     => 'integer',
        'selling_price'         => 'float',
        'dollar_buying_price'   => 'float',
        'dollar_exchange'       => 'integer',
        'transaction_id'        => 'integer',
        'product_id'            => 'integer',
    ];

    /**
     * Defines the relationship between financial transactions and their associated products.
     *
     * Each product entry belongs to a specific financial transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialTransactions()
    {
        return $this->belongsTo(FinancialTransaction::class);
    }

    /**
     * Defines the relationship between financial transaction products and the actual product.
     *
     * Each financial transaction product links to a specific product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

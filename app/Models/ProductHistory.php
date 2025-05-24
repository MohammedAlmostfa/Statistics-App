<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductHistory
 *
 * Represents historical records of product attributes such as pricing, quantity,
 * exchange rates, and installment options. This model tracks product changes over time.
 */
class ProductHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * These fields can be updated or created using mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'selling_price',
        'installment_price',
        'dollar_buying_price',
        'dollar_exchange',
        'quantity'
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * Ensures proper handling of numeric and float values.
     *
     * @var array
     */
    protected $casts = [
        'product_id'          => 'integer',
        'selling_price'       => 'integer',
        'installment_price'   => 'integer',
        'dollar_buying_price' => 'float',
        'dollar_exchange'     => 'integer',
        'quantity'            => 'integer',
    ];

    /**
     * Relationship: A product history entry belongs to a product.
     *
     * This allows tracking changes associated with a specific product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

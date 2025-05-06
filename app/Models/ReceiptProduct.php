<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptProduct extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'receipt_id',
        'product_id',
        'quantity',
        'description',
    ];
    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */
    protected $casts = [
        'receipt_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'description' => 'string',
    ];

    /**
     * Relationship: A ReceiptProduct belongs to a Receipt.
     *
     * This function defines the relationship between the `ReceiptProduct` model
     * and the `Receipt` model. Each receipt product is associated with a specific receipt.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    /**
     * Relationship: A ReceiptProduct belongs to a Product.
     *
     * This function defines the relationship between the `ReceiptProduct` model
     * and the `Product` model. Each receipt product is associated with a specific product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship: A ReceiptProduct can have one Installment.
     *
     * This function defines the relationship between the `ReceiptProduct` model
     * and the `Installment` model. Each receipt product may have one associated installment
     * (e.g., if the payment is in installments).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function installment()
    {
        return $this->hasOne(Installment::class);
    }
}

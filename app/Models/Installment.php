<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Installment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'receipt_product_id',
        'pay_cont',
        'installment',
        'first_pay',
        'installment_type',
    ];
    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */
    protected $casts = [
        'receipt_product_id' => 'integer',
        'pay_cont' => 'integer',
        'first_pay' => 'integer',
        'installment' => 'integer',

    ];
    /**
     * Relationship: An installment belongs to a receipt product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function receiptProduct()
    {
        return $this->belongsTo(ReceiptProduct::class, 'receipt_product_id');
    }

    /**
     * Relationship: An installment can have many installment payments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function InstallmentPayments()
    {
        return $this->hasMany(InstallmentPayment::class);
    }


    /**
     * Mapping of installment types to human-readable values.
     *
     * This map is used to translate the numeric values stored in the database
     * into human-readable strings and vice versa.
     *
     * 0 => 'اسبوع' (Weekly)
     * 1 => 'يومي' (Daily)
     * 2 => 'شهري' (Monthly)
     */
    const TYPE_MAP = [
        0 => 'اسبوعي',   // Weekly installment
        1 => 'يومي',     // Daily installment
        2 => 'شهري',     // Monthly installment
    ];

    /**
     * Accessor for the installment type.
     *
     * This method is used to retrieve the installment type as a human-readable string
     * based on the numeric value stored in the database.
     * It converts the stored integer value to a string, e.g., 1 becomes "يومي" (Daily).
     *
     * @param mixed $value The stored value in the database.
     * @return string The human-readable installment type.
     */
    public function installmentType(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => self::TYPE_MAP[$attributes['installment_type']] ?? 'Unknown',
            set: fn ($value) => array_search($value, self::TYPE_MAP) !== false ? array_search($value, self::TYPE_MAP) : null
        );
    }
}

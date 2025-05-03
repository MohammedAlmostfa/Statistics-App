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
        'installment_type',
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
     * 3 => 'شهري' (Monthly)
     */
    const TYPE_MAP = [
        0 => 'اسبوع',   // Weekly installment
        1 => 'يومي',    // Daily installment
        3 => 'شهري',    // Monthly installment
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
    public function installment_type(): Attribute
    {
        return Attribute::make(
            // Accessor: Get the human-readable value of the installment type.
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',

            // Mutator: Convert the human-readable string value back to its numeric representation.
            set: fn ($value) => array_search($value, self::TYPE_MAP)
        );
    }
}

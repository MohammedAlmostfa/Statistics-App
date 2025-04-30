<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Receipt model represents a transaction or payment made by a customer.
 *
 * @documented
 */
class Receipt extends Model
{
    /**
     * Fillable attributes for mass assignment.
     *
     * @var array
     * @documented
     */
    protected $fillable = [
        'customer_id',
        'receipt_id',
        'type',
        'total_amount',
        'received_amount',
        'remaining_amount',
        'receipt_date',
        'user_id'
    ];

    /**
     * Attribute casting.
     *
     * @var array
     * @documented
     */
    protected $casts = [
        'customer_id' => 'integer',
        'receipt_id' => 'integer',
        'total_amount' => 'integer',
        'received_amount' => 'integer',
        'remaining_amount' => 'integer',
        'receipt_date' => 'datetime:Y-m-d',
    ];

    /**
     * A receipt belongs to a customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @documented
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Constant map for type attribute (installment/cash).
     *
     * @documented
     */
    const TYPE_MAP = [
        0 => 'installment',
        1 => 'cash',
    ];

    /**
     * Accessor & mutator for the type attribute.
     *
     * @return Attribute
     * @documented
     */
    public function type(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',
            set: fn ($value) => array_search($value, self::TYPE_MAP)
        );
    }
}

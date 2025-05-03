<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class InstallmentPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'installment_id',
        'payment_date',
        'amount',
        'status',
    ];

    /**
     * Relationship: An installment payment belongs to an installment.
     *
     * This function defines the relationship between the `InstallmentPayment`
     * and the `Installment` model, where each payment is associated with an installment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Mapping of payment statuses to human-readable values.
     *
     * This map is used to translate the numeric values stored in the database
     * into human-readable strings and vice versa.
     *
     * 0 => 'مدفوعة' (Paid)
     * 1 => 'متأخر' (Late)
     */
    const TYPE_MAP = [
        0 => 'مدفوعة',   // Paid
        1 => 'متأخر',    // Late
    ];

    /**
     * Accessor and Mutator for the payment status.
     *
     * The `status` attribute is stored as a numeric value in the database.
     * This accessor converts the stored numeric value into a human-readable string,
     * and the mutator converts the string back to the numeric value when updating.
     *
     * @param mixed $value The stored value in the database.
     * @return string The human-readable status (e.g., "مدفوعة" or "متأخر").
     */
    public function status(): Attribute
    {
        return Attribute::make(
            // Accessor: Get the human-readable status value (e.g., "مدفوعة" or "متأخر").
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',

            // Mutator: Convert the human-readable status back to its numeric representation.
            set: fn ($value) => array_search($value, self::TYPE_MAP)
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DebtPayment
 *
 * This model represents debt-related payments within the system.
 * It stores details such as payment amount, associated debt, user who made the payment, and the installment date.
 */
class DebtPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * These attributes can be filled when creating or updating a record.
     *
     * @var array<string>
     */
    protected $fillable = [
        'amount',           // The payment amount for the debt
        'debt_id',          // The ID of the associated debt
        'user_id',          // The ID of the user who made the payment
        'payment_date', // The date of the installment payment
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount'           => 'integer', // Convert the amount to an integer
        'debt_id'          => 'integer', // Convert the debt ID to an integer
        'user_id'          => 'integer', // Convert the user ID to an integer
        'payment_date' => 'date',    // Convert the installment date to a date type
    ];

    /**
     * Defines the relationship between a debt payment and a user.
     *
     * Each debt payment belongs to one user who made the payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Defines the relationship between a debt payment and a debt.
     *
     * Each debt payment is associated with one debt record.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    /**
     * Defines the polymorphic relationship between DebtPayment and Activity Logs.
     *
     * This allows tracking multiple activities related to debt payments, such as modifications or status updates.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\FinancialTransactionsProduct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransactions extends Model
{
    protected $fillable = [
        'agent_id',
        'sum_amount',
        'transaction_date',
        'type',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'description',
        'user_id',
    ];

    protected $casts = [
        'agent_id'        => 'integer',
        'sum_amount'      => 'integer',
        'transaction_date'=> 'date',
        'total_amount'    => 'integer',
        'discount_amount' => 'integer',
        'paid_amount'     => 'integer',
        'description'     => 'string',
        'user_id'         => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function financialTransactionsProducts()
    {
        return $this->hasMany(FinancialTransactionsProduct::class, 'financial_id');
    }

    const TYPE_MAP = [
        0 => 'فاتورة شراء',  // Installment payment type
        1 => 'تسديد فاتورة شراء',  // Cash payment type
    ];
    public function type(): Attribute
    {
        return Attribute::make(
            // Get the string representation of the type
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',  // Convert numeric to string representation

            // Set the integer value for type
            set: fn ($value) => array_search($value, self::TYPE_MAP)  // Convert string back to its corresponding integer value
        );
    }



}

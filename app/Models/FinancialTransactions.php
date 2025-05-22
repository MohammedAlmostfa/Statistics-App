<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\FinancialTransactionsProduct;
use Illuminate\Database\Eloquent\Casts\Attribute;

class FinancialTransactions extends Model
{
    protected $fillable = [
        'agent_id',
        'transaction_date',
        'type',
        'total_amount',
        'discount_amount',
        'paid_amount',
        'description',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function financialTransactionsProducts()
    {
        return $this->hasMany(FinancialTransactionsProduct::class);
    }

    const TYPE_MAP = [
        0 => 'فاتورة شراء',  // Installment payment type
        1 => 'تسدسد شراء',   // Cash payment type
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FinancialTransactionsProduct;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Cache;

/**
 * Class FinancialTransaction
 *
 * Represents financial transactions related to agents, including purchase invoices,
 * payment records, and associated products.
 */
class FinancialTransaction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * These fields can be updated or created using mass assignment.
     *
     * @var array
     */
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
        'financial_transactions_number'
    ];

    /**
     * The attributes that should be cast to specific data types.
     *
     * This ensures proper data handling when retrieving from the database.
     *
     * @var array
     */
    protected $casts = [
        'agent_id'        => 'integer',
        'sum_amount'      => 'float',
        'transaction_date' => 'date',
        'total_amount'    => 'float',
        'discount_amount' => 'float',
        'paid_amount'     => 'float',
        'description'     => 'string',
        'user_id'         => 'integer',
        'financial_transactions_number' => 'integer',
    ];

    /**
     * Defines the relationship between financial transactions and users.
     *
     * Each transaction belongs to a specific user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Defines the relationship between financial transactions and agents.
     *
     * Each transaction is associated with a specific agent.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
    /**
     * Defines the relationship between financial transactions and transaction products.
     *
     * Each transaction can have multiple associated products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financialTransactionsProducts()
    {
        return $this->hasMany(FinancialTransactionsProduct::class, 'financial_id');
    }

    /**
     * Defines the polymorphic relationship between transactions and activity logs.
     *
     * This allows a transaction record to have multiple logged activities,
     * tracking actions performed on the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }

    /**
     * Maps transaction types to human-readable labels.
     *
     * This allows storing numeric values while displaying meaningful descriptions.
     */
    const TYPE_MAP = [
        0 => 'فاتورة شراء',
        1 => 'تسديد فاتورة شراء',
        3 => 'دين فاتورة شراء',
    ];

    /**
     * Accessor and mutator for the `type` attribute.
     *
     * Converts numeric transaction types to human-readable text and vice versa.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function type(): Attribute
    {
        return Attribute::make(
            // Get the string representation of the type
            get: fn($value) => self::TYPE_MAP[$value] ?? 'Unknown',  // Convert numeric to string representation

            // Set the integer value for type
            set: fn($value) => array_search($value, self::TYPE_MAP)  // Convert string back to its corresponding integer value
        );
    }


    /**
     * **Boot method for model event handling**
     *
     * This method listens to `created`, `updated`, and `deleted` events
     * and clears relevant caches while logging changes.
     */

    protected static function boot()
    {
        parent::boot();

        static::created(function ($transaction) {
            self::clearCache();
            Log::info("تم مسح كاش الوكلاء");
        });

        static::updated(function ($transaction) {
            self::clearCache();
            Log::info("تم مسح كاش الوكلاء");
        });


        static::deleted(function ($transaction) {
            self::clearCache();
            Log::info("تم مسح كاش الوكلاء");
        });
    }


    /**
     * **Clear relevant cache for agents**
     *
     * Ensures the cache keys related to agents are removed when model events occur.
     */
    protected static function clearCache()
    {
        $cacheKeys = Cache::get('all_agents_keys', []);
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('all_agents_keys');
    }
}

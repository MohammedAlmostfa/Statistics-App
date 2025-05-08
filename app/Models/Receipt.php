<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id',
        'receipt_number',
        'type',
        'total_price',
        'receipt_date',
        'user_id',
        'notes',
    ];
    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */

    protected $casts = [
        'customer_id'      => 'integer',
        'receipt_number'   => 'integer',
        'total_price'      => 'integer',
        'notes'            => 'string',
        'receipt_date'     => 'datetime:Y-m-d',
    ];

    /**
     * Relationship: A Receipt belongs to a Customer.
     *
     * This function defines the relationship between the `Receipt` model and the `Customer` model.
     * A receipt is linked to a specific customer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(user::class);
    }
    /**
     * Relationship: A Receipt has many ReceiptProducts.
     *
     * This function defines the one-to-many relationship between the `Receipt` model and the `ReceiptProduct` model.
     * A receipt can contain multiple receipt products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiptProducts()
    {
        return $this->hasMany(ReceiptProduct::class);
    }

    /**
     * A map for receipt types: either installment ('اقساط') or cash ('نقدي').
     */
    const TYPE_MAP = [
        0 => 'اقساط',  // Installment payment type
        1 => 'نقدي',   // Cash payment type
    ];

    /**
     * Mutator for the 'type' attribute.
     *
     * This method casts the 'type' attribute to its respective string value
     * (either 'اقساط' or 'نقدي') when retrieved from the database, and
     * it converts the string back to its integer value when saved to the database.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function type(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',  // Get the string representation of the type
            set: fn ($value) => array_search($value, self::TYPE_MAP)  // Convert the string back to its corresponding integer value
        );
    }
    protected static function boot()
    {
        parent::boot();

        static::created(function ($receipt) {
            Cache::forget('receipts');
            Log::info("تم إنشاء فاتورة جديدة ({$receipt->id}) وتم حذف كاش الفواتير.");
        });

        static::updated(function ($receipt) {
            Cache::forget('receipts');
            Log::info("تم تحديث الفاتورة ({$receipt->id}) وتم حذف كاش الفواتير.");
        });

        static::deleted(function ($receipt) {
            Cache::forget('receipts');
            Log::info("تم حذف الفاتورة ({$receipt->id}) وتم حذف كاش الفواتير.");
        });
    }



}

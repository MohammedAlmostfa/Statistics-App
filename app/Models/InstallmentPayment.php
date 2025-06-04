<?php

namespace App\Models;

use App\Models\Installment;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class InstallmentPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'installment_id',
        'payment_date',
        'amount',
    ];
    /**
 * Casts for attributes.
 *
 * @var array
 * @documented
 */
    protected $casts = [
         'installment_id' => 'integer',
         'payment_date' => 'date',
         'amount' => 'integer',
         'user_id'=>'integer',

    ];
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }
    /**
     *
     *  Relationship: An installment payment belongs to an installment.
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
    protected static function boot()
    {
        parent::boot();

        static::created(function ($installmentPayment) {
            self::clearCache();
            Log::info("وتم حذف كاش الزبائن.");

        });

        static::updated(function ($installmentPayment) {
            self::clearCache();
            Log::info("وتم حذف كاش الزبائن.");

        });

        static::deleted(function ($installmentPayment) {
            self::clearCache();
            Log::info("وتم حذف كاش الزبائن.");

        });
    }

    /**
     * Clears cache for installment payments.
     */
    protected static function clearCustomerCache()
    {
        $cacheKeys = Cache::get('all_customers_keys', []);
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
        Cache::forget('all_customers_keys');
    }
}

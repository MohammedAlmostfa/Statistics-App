<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Customer model represents a customer entity with relationships to receipts and filtering capabilities.
 *
 * @documented
 */
class Customer extends Model
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable attributes
     *
     * @var array
     * @documented
     */
    protected $fillable = [
        'name',
        'phone',
        'notes',
        'sponsor_name',
        'sponsor_phone',
        'Record_id',
        'Page_id',
        'status',
    ];

    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */

    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        'sponsor_name' => 'string',
        'sponsor_phone' => 'integer',
        'Record_id' => 'integer',
        'Page_id' => 'integer',
    ];

    /**
     * A customer may have many receipts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @documented
     */
    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */

    protected static function boot()
    {
        parent::boot();

        static::created(function ($customer) {
            $cacheKeys = Cache::get('all_customers_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_customers_keys');

            Log::info("تم إنشاء زبون جديد ({$customer->id}) وتم حذف كاش الزبائن.");
        });

        static::updated(function ($customer) {
            $cacheKeys = Cache::get('all_customers_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_customers_keys');

            Log::info("تم تحديث الزبون ({$customer->id}) وتم حذف كاش الزبائن.");
        });

        static::deleted(function ($customer) {
            $cacheKeys = Cache::get('all_customers_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_customers_keys');

            Log::info("تم حذف الزبون ({$customer->id}) وتم حذف كاش الزبائن.");
        });
    }
    const TYPE_MAP = [
        0 => 'قديم',  // Installment payment type
        1 => 'جديد',   // Cash payment type
    ];


    /**
     * Mutator for the 'type' attribute.
     *
     * This method defines an accessor and mutator for the 'type' attribute.
     * The accessor converts the numeric 'type' value (0 or 1) into a human-readable string ('قديم' or 'جديد').
     * The mutator converts the string back to its corresponding numeric value before saving it to the database.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::TYPE_MAP[$value] ?? 'Unknown',
            set: fn ($value) => array_search($value, self::TYPE_MAP) ?? $value
        );

    }

    /**
     * Scope to filter customers by optional criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filteringData
     * @return \Illuminate\Database\Eloquent\Builder
     *
     * @documented
     */
    public function scopeFilterBy($query, array $filteringData)
    {
        if (isset($filteringData['name'])) {
            $query->where('name', 'LIKE', "%{$filteringData['name']}%");
        }

        if (isset($filteringData['phone'])) {
            $query->where('phone', $filteringData['phone']);
        }

        if (isset($filteringData['status'])) {
            $status = array_search($filteringData['status'], self::TYPE_MAP);
            if ($status !== false) {
                $query->where('status', $status);
            }
        }

        return $query;
    }
    public function debt()
    {
        return $this->hasMany(Debt::class);
    }

}

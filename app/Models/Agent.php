<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * **Agent Model**
 *
 * Represents an agent in the system, handling:
 * - Data attributes
 * - Relationships
 * - Caching strategies
 * - Event-based logging
 */
class Agent extends Model
{
    /**
     * **Attributes that are mass assignable**
     *
     * Specifies which attributes can be filled using mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'notes',
        'status'
    ];

    /**
     * **Casts for model attributes**
     *
     * Defines how attributes should be cast when retrieved from the database.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'phone'=> 'integer',
        'details' => 'string',
    ];
    /**
         * Status constants to enhance readability.
         *
         * @var array<int, string>
         */
    const STATUS_MAP = [
        0 => 'موجود',   // User exists
        1 => 'محذوف',  // User is removed
    ];

    /**
     * Get and set the user status dynamically using attribute casting.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function status(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::STATUS_MAP[(int) $value] ?? 'Unknown',
            set: fn ($value) => array_search($value, self::STATUS_MAP)
        );
    }
    /**
     * **Defines the polymorphic relationship for activity logs**
     *
     * This allows tracking various activities related to agents.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
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

        static::created(function ($agent) {
            self::clearCache();
            Log::info("New agent created ({$agent->id}). Cache cleared.");
        });

        static::updated(function ($agent) {
            self::clearCache();
            Log::info("Agent data updated ({$agent->id}). Cache cleared.");
        });

        static::deleted(function ($agent) {
            self::clearCache();
            Log::info("Agent deleted ({$agent->id}). Cache cleared.");
        });
    }
    public function financialTransactions()
    {
        return $this->hasMany(FinancialTransaction::class);
    }
    public function lastfinancialTransaction(): HasOne
    {
        return $this->hasOne(FinancialTransaction::class)->latestOfMany('id');
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

    /**
     * **Scope to filter agents by optional criteria**
     *
     * Allows querying agents based on name or phone.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filteringData
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterBy($query, array $filteringData)
    {
        if (isset($filteringData['name'])) {
            $query->where('name', 'LIKE', "%{$filteringData['name']}%");
        }

        if (isset($filteringData['phone'])) {
            $query->where('phone', $filteringData['phone']);
        }

        return $query;
    }
}

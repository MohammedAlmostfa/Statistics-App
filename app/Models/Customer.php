<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Customer model represents a customer entity with relationships to receipts and filtering capabilities.
 *
 * @documented
 */
class Customer extends Model
{
    use HasFactory;

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
        'Page_id'
    ];

    /**
     * Type casting for model attributes
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

        if (isset($filteringData['Record_id'])) {
            $query->where('Record_id', $filteringData['Record_id']);
        }

        if (isset($filteringData['Page_id'])) {
            $query->where('Page_id', $filteringData['Page_id']);
        }

        return $query;
    }
}

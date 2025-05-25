<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Class ActivitiesLog
 *
 * This model logs activities performed by users related to various system entities.
 */
class ActivitiesLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'description',
        'type_id',
        'type_type',
    ];

    /**
     * Define a polymorphic relationship.
     *
     * Allows logging activities related to multiple models dynamically.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function type()
    {
        return $this->morphTo();
    }

    /**
     * Define attribute casting for model properties.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity_date' => 'datetime',
    ];

    /**
     * Define relationship with User model.
     *
     * Links activity logs to the user who performed the action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope function to filter logs based on user name or type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array<string, mixed> $filteringData
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterBy($query, $filteringData)
    {
        if (isset($filteringData['name'])) {
            $query->whereHas('user', function ($q) use ($filteringData) {
                $q->where('name', 'LIKE', "%{$filteringData['name']}%");
            });
        }

        if (isset($filteringData['type'])) {
            $typeClass = array_search($filteringData['type'], self::TYPE_MAP);
            if ($typeClass) {
                $query->where('type_type', $typeClass);
            }
        }

        return $query;
    }

    /**
     * Mapping for entity types to readable names.
     *
     * @var array<string, string>
     */
    const TYPE_MAP = [
        'App\\Models\\Receipt' => 'فواتير',
        'App\\Models\\Payment' => 'صرفيات',
        'App\\Models\\Customer' => 'زبائن',
        'App\\Models\\InstallmentPayment' => 'اقساط الفواتير',
        'App\\Models\\Product' => 'منتجات',
        'App\\Models\\ProductCategory' => 'اصناف',
        'App\\Models\\Debt' => 'ديون',
        'App\\Models\\DebtPayment' => 'اقساط الدين',
        'App\\Models\\Agent' => 'وكلاء',
        'App\\Models\\FinancialTransaction' => 'معاملات الوكلاء',
    ];

    /**
     * Define custom accessor and mutator for `typeType` attribute.
     *
     * This ensures the entity type is mapped correctly based on TYPE_MAP.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function typeType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::TYPE_MAP[$value] ?? class_basename($value),
            set: fn ($value) => array_search($value, self::TYPE_MAP) ?: $value
        );
    }



}

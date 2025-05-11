<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ActivitiesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'description',
        'type_id',
        'type_type',
    ];

    /**
     * Define a polymorphic relationship.
     */
    public function type()
    {
        return $this->morphTo();
    }

    /**
     * Define attribute casting.
     */
    protected $casts = [
        'activity_date' => 'datetime',
    ];

    /**
     * Define relationship with User model.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilterBy($query, $filteringData)
    {
        if (isset($filteringData['type'])) {
            $typeClass = array_search($filteringData['type'], self::TYPE_MAP);
            if ($typeClass) {
                $query->where('type_type', $typeClass);
            }
        }

        return $query;
    }


    const TYPE_MAP = [
        'App\\Models\\Receipt' => 'فاتورة',
        'App\\Models\\Payment' => 'دفعة',
        'App\\Models\\Customer' => 'زبائن',
        'App\\Models\\InstallmentPayment' => 'قسط',
        'App\\Models\\Product' => 'منتج',
        'App\\Models\\ProductCategory' => 'صنف منج',
    ];

    public function typeType(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::TYPE_MAP[$value] ?? class_basename($value),
            set: fn ($value) => array_search($value, self::TYPE_MAP) ?: $value
        );

    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($activitiesLog) {
            $cacheKeys = Cache::get('activities', []);

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('activities');

            Log::info("تم إنشاء سجل جديد للأنشطة ({$activitiesLog->id}) وتم حذف كاش السجلات.");
        });
    }
}

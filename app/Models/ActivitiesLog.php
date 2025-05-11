<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($activitiesLog) {

            Cache::forget('activities_logs');

            Log::info("تم إنشاء سجل جديد للأنشطة ({$activitiesLog->id}) وتم حذف كاش السجلات.");
        });
    }
}

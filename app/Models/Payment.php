<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Payment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'payment_date',
        'details',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'integer'

    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($payment) {
            $cacheKeys = Cache::get('all_payments_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_payments_keys');

            Log::info("تم إنشاء دفعة جديدة ({$payment->id}) وتم حذف كاش الدفعات.");
        });

        static::updated(function ($payment) {
            $cacheKeys = Cache::get('all_payments_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_payments_keys');

            Log::info("تم تحديث الدفعة ({$payment->id}) وتم حذف كاش الدفعات.");
        });

        static::deleted(function ($payment) {
            $cacheKeys = Cache::get('all_payments_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::forget('all_payments_keys');

            Log::info("تم حذف الدفعة ({$payment->id}) وتم حذف كاش الدفعات.");
        });
    }
}

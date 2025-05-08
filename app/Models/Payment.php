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
        'amount'=>'integer'

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
            Cache::forget('payments');
            Log::info("تم إنشاء دفعة جديدة ({$payment->id}) وتم حذف كاش الدفعات.");
        });

        static::updated(function ($payment) {
            Cache::forget('payments');
            Log::info("تم تحديث الدفعة ({$payment->id}) وتم حذف كاش الدفعات.");
        });

        static::deleted(function ($payment) {
            Cache::forget('payments');
            Log::info("تم حذف الدفعة ({$payment->id}) وتم حذف كاش الدفعات.");
        });
    }


}

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
        * Defines the polymorphic relationship between debts and activity logs.
        *
        * This allows a debt record to have multiple logged activities, tracking actions performed on the debt.
        *
        * @return \Illuminate\Database\Eloquent\Relations\MorphMany
        */
    public function activities()
    {
        return $this->morphMany(ActivitiesLog::class, 'type');
    }
}

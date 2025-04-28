<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'buying_price',
        'installment_price',
        'quantity',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

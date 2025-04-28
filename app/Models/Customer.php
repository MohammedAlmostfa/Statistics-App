<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'notes', 'record_id'];

    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        'record_id' => 'integer',
    ];



    public function receipts()
    {
        return $this->hasMany(Receipt::class);
    }

    public function scopeFilterBy($query, array $filteringData)
    {
        if (isset($filteringData['name'])) {
            $query->where('name', 'LIKE', "%{$filteringData['name']}%");
        }

        if (isset($filteringData['phone'])) {
            $query->where('phone', '=', $filteringData['phone']);
        }
        if (isset($filteringData['record_id'])) {
            $query->where('record_id', '=', $filteringData['record_id']);
        }
        return $query;
    }

}

<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'notes',];

    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',

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

        return $query;
    }

}

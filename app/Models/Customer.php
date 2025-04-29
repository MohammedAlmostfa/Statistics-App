<?php

namespace App\Models;

use App\Models\Debt;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'notes',"sponsor_name",'sponsor_phone','Record_id','Page_id'];



    protected $casts = [
        'name' => 'string',
        'phone' => 'integer',
        'notes' => 'string',
        "sponsor_name" => 'string',
        'sponsor_phone'=> 'integer',
        'Record_id'=> 'integer',
        'Page_id'=> 'integer',

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

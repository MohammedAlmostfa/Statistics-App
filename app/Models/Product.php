<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * Fillable attributes for mass assignment.
     */
    protected $fillable = [
        'name',
        'buying_price',
        'selling_price',
        'installment_price',
        'quantity',
        'user_id',
        'origin_id',
        'category_id',
    ];


    /**
     * Casts for type conversion.
     */
    protected $casts = [
        'name' => 'string',
      'buying_price' => 'float',
    'selling_price' => 'float',
        'installment_price' => 'integer',

        'quantity' => 'integer',
        'user_id' => 'integer',
        'origin_id'=>'integer',
        'category_id'=>'integer',
    ];

    /**
     * Relationship to User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function origin()
    {
        return $this->belongsTo(ProductOrigin::class, 'origin_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function scopeFilterBy($query, array $filteringData)
    {

        // Filter by debt_date if provided
        if (isset($filteringData['category_id'])) {
            $query->where('category_id', '=', $filteringData['category_id']);
        }

        return $query;
    }



}

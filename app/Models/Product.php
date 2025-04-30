<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Product model represents a product with related properties such as pricing, quantity, and category.
 * This model also includes relationships with users, categories, and product origins.
 *
 * @documented
 */
class Product extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes for the product.
     *
     * @var array
     * @documented
     */
    protected $fillable = [
        'name',
        'buying_price',
        'selling_price',
        "dolar_buying_price",
        'installment_price',
        'quantity',
        'user_id',
        'origin_id',
        'category_id',
    ];

    /**
     * Casts for type conversion.
     *
     * @var array
     * @documented
     */
    protected $casts = [
        'name' => 'string',
        'buying_price' => 'float',
        'selling_price' => 'float',
        'dolar_buying_price' => 'float',
        'installment_price' => 'integer',
        'quantity' => 'integer',
        'user_id' => 'integer',
        'origin_id' => 'integer',
        'category_id' => 'integer',
    ];

    /**
     * Relationship to the User that owns the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @documented
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to the ProductOrigin of the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @documented
     */
    public function origin()
    {
        return $this->belongsTo(ProductOrigin::class, 'origin_id');
    }

    /**
     * Relationship to the ProductCategory of the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @documented
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Scope to filter products by category or other filtering criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filteringData
     * @return \Illuminate\Database\Eloquent\Builder
     * @documented
     */
    public function scopeFilterBy($query, array $filteringData)
    {
        // Filter by category_id if provided
        if (isset($filteringData['category_id'])) {
            $query->where('category_id', '=', $filteringData['category_id']);
        }

        return $query;
    }
}

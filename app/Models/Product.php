<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'selling_price',
        'dolar_buying_price',
        'installment_price',
        'dollar_exchange',
        'quantity',
        'user_id',
        'origin_id',
        'category_id',
    ];

    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */

    protected $casts = [

        'selling_price'     => 'float',
        'dolar_buying_price' => 'float',
        'dollar_exchange' => 'integer',
        'installment_price' => 'integer',
        'quantity'          => 'integer',
        'user_id'           => 'integer',
        'origin_id'         => 'integer',
        'category_id'       => 'integer',
    ];

    public function getCalculatedBuyingPrice()
    {

        return $this->dolar_buying_price * $this->dollar_exchange;
    }

    public function getSellingPriceForReceiptType($type)
    {

        return ($type === 'اقساط') ? $this->installment_price : $this->selling_price;
    }
    /**
     * Relationship: A Product can have many ReceiptProducts.
     *
     * This function defines the one-to-many relationship between the `Product` model
     * and the `ReceiptProduct` model. A product can appear in many receipt products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receiptProducts()
    {
        return $this->hasMany(ReceiptProduct::class);
    }

    /**
     * Relationship: A Product belongs to a User.
     *
     * This function defines the inverse one-to-many relationship between the `Product` model
     * and the `User` model. Each product is created/owned by a specific user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: A Product belongs to a ProductOrigin.
     *
     * This function defines the inverse one-to-many relationship between the `Product` model
     * and the `ProductOrigin` model. Each product is linked to a specific origin (e.g., country or manufacturer).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function origin()
    {
        return $this->belongsTo(ProductOrigin::class, 'origin_id');
    }

    /**
     * Relationship: A Product belongs to a ProductCategory.
     *
     * This function defines the inverse one-to-many relationship between the `Product` model
     * and the `ProductCategory` model. Each product is associated with a category (e.g., electronics, food).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Scope query to filter products by category ID.
     *
     * This function allows filtering the products based on the category they belong to.
     * The filtering is done using an array of filtering criteria, allowing users to specify
     * the category they want to filter by.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filteringData An associative array containing the filtering criteria
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilterBy($query, array $filteringData)
    {
        if (isset($filteringData['category_id'])) {
            $query->where('category_id', $filteringData['category_id']);
        }

        return $query;
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($product) {
            Cache::forget('products');
            Log::info("تم إنشاء منتج جديد ({$product->id}) وتم حذف كاش المنتجات.");
        });

        static::updated(function ($product) {
            Cache::forget('products');
            Log::info("تم تحديث المنتج ({$product->id}) وتم حذف كاش المنتجات.");
        });

        static::deleted(function ($product) {
            Cache::forget('products');
            Log::info("تم حذف المنتج ({$product->id}) وتم حذف كاش المنتجات.");
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * ProductCategory model represents the category assigned to products.
 *
 * @documented
 */
class ProductCategory extends Model
{
    /**
     * Mass assignable attributes.
     *
     * @var array
     * @documented
     */
    protected $fillable = ['name',];

    /**
     * Casts for attributes.
     *
     * @var array
     * @documented
     */
    protected $casts = [
        'name' => 'string',

    ];

    /**
     * A category may have many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @documented
     */
    public function product()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */

    protected static function boot()
    {
        parent::boot();

        static::created(function ($productCategory) {
            Cache::forget('categories');
            Log::info("تم إنشاء فئة منتج جديدة ({$productCategory->id}) وتم حذف كاش الفئات.");
        });

        static::updated(function ($productCategory) {
            Cache::forget('categories');
            Log::info("تم تحديث فئة المنتج ({$productCategory->id}) وتم حذف كاش الفئات.");
        });

        static::deleted(function ($productCategory) {
            Cache::forget('categories');
            Log::info("تم حذف فئة المنتج ({$productCategory->id}) وتم حذف كاش الفئات.");
        });
    }
}

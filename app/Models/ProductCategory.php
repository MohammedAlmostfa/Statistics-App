<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    protected $fillable = ['name'];

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
}

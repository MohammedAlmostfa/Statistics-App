<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProductOrigin model represents the origin of a product (e.g., country or region).
 *
 * @documented
 */
class ProductOrigin extends Model
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
     * An origin may be linked to many products.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @documented
     */
    public function product()
    {
        return $this->hasMany(Product::class);
    }
}

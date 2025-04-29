<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductOrigin extends Model
{
    protected $fillable=[
    'name'
    ];
    protected $casts = [
       'name' => 'string',
    ];

    public function product()
    {
        return $this->hasMany(Product::class);
    }

}

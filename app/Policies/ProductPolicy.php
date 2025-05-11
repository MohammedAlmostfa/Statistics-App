<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function deleteProduct(User $user): bool
    {
        return $user->hasPermissionTo('product.delete');
    }
}

<?php

namespace App\Policies;

use App\Models\User;

class ProductCategoryPolicy
{
    public function deleteProductCategory(User $user): bool
    {
        return $user->hasPermissionTo('productCategory.delete');
    }
}

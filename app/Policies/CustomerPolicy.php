<?php

namespace App\Policies;

use App\Models\User;

class CustomerPolicy
{
    public function deleteCustomer(User $user): bool
    {
        return $user->hasPermissionTo('customer.delete');
    }
}
